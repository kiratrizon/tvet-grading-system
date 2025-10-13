<?php

session_start();
require_once  '../config/conn.php';


if (isset($_POST['updateTeacher'])) {
    $id = $_POST['id'];

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $oldemail = $_POST['oldemail'];

    $fullname = $fname . " " . $lname;

    $email_result = $conn->query("SELECT * FROM `teachers` 
INNER JOIN web_users on teachers.t_user_name = web_users.email WHERE web_users.email = '$username'");

    if ($email_result->num_rows == 1) {
        $id2 = $email_result->fetch_assoc()['t_id'];
    } else {
        $id2 = $id;
    }

    if ($id2 == $id) {

        $conn->query("UPDATE `teachers` SET `t_name`='$fullname',`t_user_name`='$username',`t_gender`='$gender' WHERE t_id = '$id'");


        $conn->query("UPDATE `web_users` SET `email`='$username',`usertype`='t' WHERE email = '$oldemail'");
        header("location: teacher.php");
        $_SESSION['updated'] = 0;
    }
}
