<?php
require_once '../app/config/config.php';
require_once '../app/controllers/auth_controller.php';

$auth = new AuthController();
$auth->logout();
header('Location: login.php');
exit;
?>
