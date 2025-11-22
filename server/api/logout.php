<?php
// logout.php 
session_start();
$_SESSION = [];
session_unset();
session_destroy();

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã đăng xuất'
    ]);
    exit;
}

$msg = urlencode('Đã đăng xuất');
header('Location: ../client/login.html?msg=' . $msg . '&type=success');
exit;
?>
