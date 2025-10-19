<?php
require_once __DIR__ . '/Env.php';

$server = ('localhost');
$user   = ('root');
$pass   = ('');
$dname  = ('grading_system');

$conn = new mysqli($server, $user, $pass, $dname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}