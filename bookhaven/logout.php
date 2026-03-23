<?php
require_once 'includes/auth.php';
session_destroy();
header("Location: /bookhaven/index.php");
exit();
?>
