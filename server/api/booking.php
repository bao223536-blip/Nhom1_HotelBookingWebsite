<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Kết nối database
$conn = new mysqli("localhost", "root", "", "homestay_db");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Kết nối database thất bại"]);
    exit;
}

// Nhận dữ liệu từ frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

// Lấy dữ liệu
$user_id       = $data["user_id"] ?? null;
$homestay_id   = $data["homestay_id"] ?? null;
$guest_name    = trim($data["guest_name"] ?? "");
$guest_phone   = trim($data["guest_phone"] ?? "");
$check_in      = $data["check_in_date"] ?? "";
$check_out     = $data["check_out_date"] ?? "";

// Validate
if (!$user_id || !$homestay_id || empty($guest_name) || empty($guest_phone) || empty($check_in) || empty($check_out)) {
    echo json_encode(["status" => "error", "message" => "Vui lòng điền đầy đủ thông tin"]);
    exit;
}

if ($check_out <= $check_in) {
    echo json_encode(["status" => "error", "message" => "Ngày trả phòng phải sau ngày nhận phòng"]);
    exit;
}

// Kiểm tra định dạng ngày
if (!DateTime::createFromFormat('Y-m-d', $check_in) || !DateTime::createFromFormat('Y-m-d', $check_out)) {
    echo json_encode(["status" => "error", "message" => "Định dạng ngày không hợp lệ"]);
    exit;
}

// === BẮT ĐẦU TRANSACTION ===
$conn->autocommit(false);
try {
    // 1. Kiểm tra phòng có tồn tại không
    $sql = "SELECT id FROM homestay WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $homestay_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Phòng không tồn tại!");
    }

    // 2. KIỂM TRA TRÙNG LỊCH (QUAN TRỌNG)
    $sqlCheck = "
        SELECT id FROM booked_room 
        WHERE homestay_id = ? 
        AND (
            (check_in_date <= ? AND check_out_date > ?) OR
            (check_in_date < ? AND check_out_date >= ?) OR
            (check_in_date >= ? AND check_out_date <= ?)
        )
        LIMIT 1
    ";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("sssssss", 
        $homestay_id,
        $check_out, $check_in,
        $check_out, $check_in,
        $check_in,  $check_out
    );
    $stmtCheck->execute();
    $conflict = $stmtCheck->get_result();

    if ($conflict->num_rows > 0) {
        throw new Exception("Phòng đã được đặt trong khoảng thời gian này!");
    }

    // 3. THÊM ĐẶT PHÒNG (chỉ dùng các cột bạn đã có)
    $sqlInsert = "
        INSERT INTO booked_room 
        (guest_name, guest_phone, check_in_date, check_out_date, homestay_id, user_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ssssii", 
        $guest_name, $guest_phone, $check_in, $check_out, $homestay_id, $user_id
    );

    if (!$stmtInsert->execute()) {
        throw new Exception("Không thể lưu đặt phòng");
    }

    $booking_id = $conn->insert_id;

    // === THÀNH CÔNG → COMMIT ===
    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Đặt phòng thành công! Mã đặt phòng: #$booking_id",
        "booking_id" => $booking_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    $conn->autocommit(true);
    $conn->close();
}
?>
