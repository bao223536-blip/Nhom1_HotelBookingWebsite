<?php
// search_rooms.php 

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../helpers.php';

// Nhận input từ GET
$q = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? '');
$min_price = is_numeric($_GET['min_price'] ?? null) ? (float)$_GET['min_price'] : null;
$max_price = is_numeric($_GET['max_price'] ?? null) ? (float)$_GET['max_price'] : null;

// Query cơ bản
$sql = "SELECT id, room_type, location, room_price, booked 
        FROM homestay 
        WHERE 1";

$params = [];

// Tìm theo keyword
if ($q !== '') {
    $sql .= " AND (room_type LIKE ? OR location LIKE ?)";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
}

// Tìm theo loại phòng
if ($type !== '') {
    $sql .= " AND room_type = ?";
    $params[] = $type;
}

// Lọc theo giá tối thiểu
if ($min_price !== null) {
    $sql .= " AND room_price >= ?";
    $params[] = $min_price;
}

// Lọc theo giá tối đa
if ($max_price !== null) {
    $sql .= " AND room_price <= ?";
    $params[] = $max_price;
}

// Thực thi query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

// Trả về JSON
json_response(['rooms' => $rooms]);
?>
