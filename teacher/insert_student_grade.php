<?php
session_start();
require '../config/conn.php';

$teacher_id = $_SESSION['teacher_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'];
    $course = $_POST['course'];
    $course_id = $_POST['course_id'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $school_year = $_POST['school_year'];
    $course_code = $_POST['course_code'];
    $descriptive_title = $_POST['descriptive_title'];
    $name = $_POST['name'];
    $final_rating = $_POST['final_rating'];
    $remarks = $_POST['remarks'];
    $subject_id = $_POST['subject_id'];
    $section = $_POST['section'];


    if (!empty($name) && !empty($final_rating) && !empty($remarks)) {

        // **1. CHECK IF STUDENT USER EXISTS**
        $stmt = $conn->prepare("SELECT id FROM student_users WHERE name = ? AND course = ?");
        $stmt->bind_param("ss", $name, $course_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($student_id);
        $stmt->fetch();

        if ($stmt->num_rows === 0) {
            // **2. INSERT NEW STUDENT USER (AUTO-ADD EMAIL & PASSWORD)**
            $default_email = strtolower(str_replace(' ', '', $name)) . "@school.edu";
            $default_password = password_hash("student123", PASSWORD_DEFAULT);

            $insertUser = $conn->prepare("INSERT INTO student_users (name, course, email, password) VALUES (?, ?, ?, ?)");
            $insertUser->bind_param("ssss", $name, $course_id, $default_email, $default_password);
            $insertUser->execute();
            $student_id = $insertUser->insert_id;
            $insertUser->close();
        }

        $stmt->close();

        // **3. INSERT STUDENT GRADE WITH STUDENT ID**
        $stmt = $conn->prepare("INSERT INTO student_grades 
            (student_id, name, course, year_level, semester, school_year, course_code, descriptive_title, final_rating, remarks, teacher_id, subject_id, section) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssiis", $student_id, $name, $course_id, $year_level, $semester, $school_year, $course_code, $descriptive_title, $final_rating, $remarks, $teacher_id, $subject_id, $section);

        if ($stmt->execute()) {
            $_SESSION['updated'] = "Grade added successfully! <br> 
                <strong>$name</strong> ($course), <br> 
                Subject: <strong>$descriptive_title</strong> ($course_code), <br>
                Year: <strong>$year_level</strong>, Semester: <strong>$semester</strong>, <br>
                Final Rating: <strong>$final_rating</strong>, Remarks: <strong>$remarks</strong>.";
            header("location: mysubjects.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add the grade for <strong>$name</strong>.";
            header("location: mysubjects.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = " All fields are required!";
        header("location: mysubjects.php");
        exit();
    }
}

$conn->close();
