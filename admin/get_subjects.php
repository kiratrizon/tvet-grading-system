<?php

session_start();
require_once  '../config/conn.php';

$course = $_POST['course'];
$year_level = $_POST['year_level'];
$semester = $_POST['semester'];

$query = "SELECT s_id, s_descriptive_title FROM subjects WHERE s_course = '$course' AND s_year_level = '$year_level' AND s_semester = '$semester'";
$result = $conn->query($query);

echo '<option hidden selected disabaled>Select Descriptive Title</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['s_id'] . '">' . $row['s_descriptive_title'] . '</option>';
}
