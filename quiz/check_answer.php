<?php
session_start();
require_once("log.php");
add_log(
    $_SESSION['lang']['logs']['logout']['title'],
    $_SESSION['lang']['logs']['logout']['user_auto_logout'],
    $_SESSION['email'],
    "./logs/"
);
header("Location: logout.php");
?>