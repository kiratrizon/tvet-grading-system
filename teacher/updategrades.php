<?php
session_start();
require_once '../config/conn.php';

if (isset($_POST['update_subject'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $course_code = $_POST['course_code'];
    $descriptive = $_POST['descriptive'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $rating = $_POST['rating'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE student_grades SET name = ?, final_rating = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $name, $rating, $remarks, $id);

    if ($stmt->execute()) {

        if ($remarks !== "Incomplete") {
            $delete_stmt = $conn->prepare("DELETE FROM student_missing_requirements WHERE student_id = ?");
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }

        $_SESSION['updated'] = "Successfully updated the final rating of <strong>$name</strong> for <strong>$descriptive</strong> 
        (<strong>$course_code</strong>), <br> Year Level <strong>$year</strong>, Semester <strong>$semester</strong>. 
        Final Rating: <strong>$rating</strong>, <br> Remarks: <strong>$remarks</strong>.";
    } else {
        $_SESSION['error'] = "Failed to update the grade for <strong>$name</strong>.";
    }

    $stmt->close();
    $conn->close();

    header("location: mysubjects.php");
    exit();
}
