<?php
// booking_stats.php

require_once __DIR__ . '/../db_connect.php';

// Sử dụng check_in_date thay vì created_at (không tồn tại)
$sql = "SELECT DATE_FORMAT(check_in_date, '%Y-%m') as period, COUNT(*) as total 
        FROM booked_room 
        GROUP BY period 
        ORDER BY period DESC 
        LIMIT 6";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode(array_reverse($data));
?>
