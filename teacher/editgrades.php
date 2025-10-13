<?php

require_once '../config/conn.php';

$id = $_GET['id'];

$editgrades = $conn->query("SELECT * FROM student_grades WHERE id = $id");
$editgrades = $editgrades->fetch_array(MYSQLI_ASSOC);


echo json_encode($editgrades);
