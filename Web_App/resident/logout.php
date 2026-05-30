<?php
session_start();
$_SESSION = array();
session_destroy();

// REDIRECTION
header("Location: ../login_reg.php");
exit();
?>