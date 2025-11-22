<?php
// update_room.php

require_once __DIR__ . '/db_connect.php';
session_start();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo "<script>alert('Phải gọi bằng POST');history.back();</script>";
    exit;
}

$id = intval($_POST['id'] ?? 0);
$room_type = trim($_POST['room_type'] ?? '');
$location = trim($_POST['location'] ?? '');
$room_price = trim($_POST['room_price'] ?? '0');

if($id <= 0){
    echo "<script>alert('ID không hợp lệ');history.back();</script>";
    exit;
}

if($room_type === ''){
    echo "<script>alert('Loại phòng không được để trống');history.back();</script>";
    exit;
}

if($location === ''){
    echo "<script>alert('Vị trí phòng không được để trống');history.back();</script>";
    exit;
}

// Lấy ảnh cũ từ bảng homestay_images
$stmt = $pdo->prepare("SELECT image_url FROM homestay_images WHERE homestay_id = ? LIMIT 1");
$stmt->execute([$id]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);
$oldImagePath = $old['image_url'] ?? '';

// Xử lý upload ảnh mới
if(!empty($_FILES['image']['name'])){
    $uploadsDir = __DIR__ . '/../../client/uploads';
    if(!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    $safe = time(). '_' . preg_replace('/[^A-Za-z0-9._-]/','_', basename($_FILES['image']['name']));
    $target = $uploadsDir . '/' . $safe;
    if(!move_uploaded_file($_FILES['image']['tmp_name'], $target)){
        echo "<script>alert('Lỗi upload ảnh mới');history.back();</script>";
        exit;
    }

    // Xóa ảnh cũ nếu có
    if(!empty($oldImagePath)){
        $oldPath = __DIR__ . '/../../client/' . $oldImagePath;
        if(file_exists($oldPath)) @unlink($oldPath);
    }
    
    $newImagePath = 'uploads/' . $safe;
    
    // Cập nhật hoặc thêm ảnh mới vào homestay_images
    $stmtCheck = $pdo->prepare("SELECT homestay_id FROM homestay_images WHERE homestay_id = ? LIMIT 1");
    $stmtCheck->execute([$id]);
    if($stmtCheck->fetch()){
        $sqlImage = "UPDATE homestay_images SET image_url = :image_url WHERE homestay_id = :homestay_id";
    } else {
        $sqlImage = "INSERT INTO homestay_images (homestay_id, image_url) VALUES (:homestay_id, :image_url)";
    }
    $stmtImage = $pdo->prepare($sqlImage);
    $stmtImage->execute([':homestay_id'=>$id, ':image_url'=>$newImagePath]);
}

// Cập nhật thông tin phòng
$sql = "UPDATE homestay SET room_type=:room_type, location=:location, room_price=:room_price WHERE id=:id";
$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([':room_type'=>$room_type, ':location'=>$location, ':room_price'=>$room_price, ':id'=>$id]);

if($ok){
    echo "<script>alert('Cập nhật phòng thành công');window.location='../../client/admin_rooms.html';</script>";
    exit;
} else {
    echo "<script>alert('Lỗi khi cập nhật phòng');history.back();</script>";
    exit;
}
?>
