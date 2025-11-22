<?php
// delete_room.php

require_once __DIR__ . '/db_connect.php';
session_start();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<script>alert('ID không hợp lệ');history.back();</script>";
    exit;
}

// Xóa ảnh từ bảng homestay_images và xóa file ảnh
$stmt = $pdo->prepare("SELECT image_url FROM homestay_images WHERE homestay_id = ?");
$stmt->execute([$id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($images as $img){
    if(!empty($img['image_url'])){
        $f = __DIR__ . '/../../client/' . $img['image_url'];
        if(file_exists($f)) @unlink($f);
    }
}

// Xóa ảnh trong database (foreign key sẽ tự xóa khi xóa homestay)
$stmt = $pdo->prepare("DELETE FROM homestay_images WHERE homestay_id = ?");
$stmt->execute([$id]);

// Xóa phòng
$stmt = $pdo->prepare("DELETE FROM homestay WHERE id = ?");
$ok = $stmt->execute([$id]);

if ($ok) {
    echo "<script>alert('Xóa phòng thành công');window.location='../../client/admin_rooms.html';</script>";
    exit;
} else {
    echo "<script>alert('Lỗi khi xóa phòng');history.back();</script>";
    exit;
}
