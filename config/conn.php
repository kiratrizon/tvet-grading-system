<?php
require_once __DIR__ . '/Env.php';

$server = env('DB_HOST', 'localhost');
$user   = env('DB_USER', 'root');
$pass   = env('DB_PASSWORD', '');
$dname  = env('DB_NAME', 'grading_system_v2');
$port   = (int) env('DB_PORT', '3306');

$conn = new mysqli($server, $user, $pass, $dname, $port);

if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}