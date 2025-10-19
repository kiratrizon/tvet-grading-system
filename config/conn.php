<?php
require_once __DIR__ . '/Env.php';

$server = env('DB_HOST', 'localhost');
$user   = env('DB_USER', 'root');
$pass   = env('DB_PASSWORD', '');
$dname  = env('DB_NAME', 'grading_system');

$conn = new mysqli($server, $user, $pass, $dname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}