<?php
// logout.php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

$msg = urlencode('Đã đăng xuất');
$typ = 'success';
header("Location: ../client/login.html?msg={$msg}&type={$typ}");
exit;
?>
