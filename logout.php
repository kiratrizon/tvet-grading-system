<?php


session_start();

require_once "config/conn.php";

$usermail = $_SESSION["user"] ?? '';

if (empty($usermail)) {
    header("location: index.php?action=logout");
    exit;
}

$conn->query("UPDATE teachers SET status = 0 WHERE t_user_name = '$usermail'");


$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 86400, '/');
}

session_destroy();

header("location: index.php?action=logout");
exit;
