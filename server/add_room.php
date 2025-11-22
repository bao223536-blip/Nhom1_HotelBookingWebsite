<?php
// add_room.php

require_once __DIR__ . '/db_connect.php';
session_start();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo "<script>alert('Phải gọi bằng POST');history.back();</script>";
    exit;
}

$room_type = trim($_POST['room_type'] ?? '');
$location = trim($_POST['location'] ?? '');
$room_price = trim($_POST['room_price'] ?? '0');

if($room_type === ''){
    echo "<script>alert('Loại phòng không được để trống');history.back();</script>";
    exit;
}

if($location === ''){
    echo "<script>alert('Vị trí phòng không được để trống');history.back();</script>";
    exit;
}

// Xử lý upload ảnh và lưu vào bảng homestay_images
$imagePath = '';
if(!empty($_FILES['image']['name'])){
    $uploadsDir = __DIR__ . '/../../client/uploads';
    if(!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
    $safe = time(). '_' . preg_replace('/[^A-Za-z0-9._-]/','_', basename($_FILES['image']['name']));
    $target = $uploadsDir . '/' . $safe;
    if(!move_uploaded_file($_FILES['image']['tmp_name'], $target)){
        echo "<script>alert('Lỗi upload ảnh');history.back();</script>";
        exit;
    }
   
    $imagePath = 'uploads/' . $safe;
}

// Thêm phòng vào bảng homestay
$sql = "INSERT INTO homestay (room_type, location, room_price, booked) VALUES (:room_type, :location, :room_price, 0)";
$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([':room_type'=>$room_type, ':location'=>$location, ':room_price'=>$room_price]);

// Nếu có ảnh, thêm vào bảng homestay_images
if($ok && $imagePath !== ''){
    $homestay_id = $pdo->lastInsertId();
    $sqlImage = "INSERT INTO homestay_images (homestay_id, image_url) VALUES (:homestay_id, :image_url)";
    $stmtImage = $pdo->prepare($sqlImage);
    $stmtImage->execute([':homestay_id'=>$homestay_id, ':image_url'=>$imagePath]);
}

if($ok){
    echo "<script>alert('Thêm phòng thành công');window.location='../../client/admin_rooms.html';</script>";
    exit;
} else {
    echo "<script>alert('Lỗi khi thêm phòng');history.back();</script>";
    exit;
}
?>
