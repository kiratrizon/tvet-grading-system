<?php
session_start();
require_once  '../config/conn.php';

$course = $_POST['course'];
$query = "SELECT DISTINCT s_year_level FROM subjects WHERE s_course = '$course'";
$result = $conn->query($query);

echo '<option hidden selected disabled>Select Year Level</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['s_year_level'] . '">' . $row['s_year_level'] . '</option>';
}
