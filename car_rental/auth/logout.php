<?php
require_once("../config/auth.php");
$_SESSION = [];
session_destroy();
header("Location: " . BASE_URL . "auth/login.php");
exit();