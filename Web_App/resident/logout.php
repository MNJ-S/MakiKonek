<?php
session_start();
$_SESSION = array();
session_destroy();

// REDIRECTION
header("Location: Web_App\login_reg.php");
exit();
?>