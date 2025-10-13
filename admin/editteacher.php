<?php

require_once '../config/conn.php';

$id = $_GET['id'];

$teachers = $conn->query("SELECT * FROM teachers WHERE t_id = $id");
$teachers = $teachers->fetch_array(MYSQLI_ASSOC);


echo json_encode($teachers);
