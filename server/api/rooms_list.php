<?php
// rooms_list.php

require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json');

// Lấy danh sách phòng với ảnh đầu tiên (nếu có)
$sql = "SELECT h.id, h.room_type, h.location, h.room_price, h.booked, 
        (SELECT image_url FROM homestay_images WHERE homestay_id = h.id LIMIT 1) as image
        FROM homestay h 
        ORDER BY h.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rooms);
?>
