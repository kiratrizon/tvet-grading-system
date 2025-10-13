<?php

session_start();
require_once  '../config/conn.php';


if (isset($_POST['submitTeacher'])) {


    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    $fullname = $fname . " " . $lname;

    $email_result = $conn->query("SELECT * FROM web_users WHERE email = '$username'");

    if ($email_result->num_rows == 1) {
        $_SESSION['error'] = 1;
        header("location: teacher.php");
        exit;
    } else {
        $conn->query("INSERT INTO `teachers`(`t_name`, `t_user_name`, `t_password`, `t_gender`) VALUES ('$fullname','$username','$pass','$gender')");
        $conn->query("INSERT INTO `web_users`(email, usertype) VALUES ('$username','t')");

        header("location: teacher.php");
        $_SESSION['success'] = 0;
    }
}
