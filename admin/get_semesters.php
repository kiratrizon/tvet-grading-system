<?php

session_start();
require_once  '../config/conn.php';

$course = $_POST['course'];
$year_level = $_POST['year_level'];

$query = "SELECT DISTINCT s_semester FROM subjects WHERE s_course = '$course' AND s_year_level = '$year_level'";
$result = $conn->query($query);

echo '<option selected hidden disabaled>Select Semester</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['s_semester'] . '">' . $row['s_semester'] . '</option>';
}
