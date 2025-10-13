<?php

session_start();

require_once  '../config/conn.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 't') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../public/assets/icon/logo.svg">
    <link rel="stylesheet" href="../public/style/bootstrap.min.css">
    <link rel="stylesheet" href="../public/style/main.css">
    <link rel="stylesheet" href="../public/style/admin.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">

    <title>Teacher</title>
</head>

<body>
    <?php include('./theme/sidebar.php'); ?>
    <div id="loading-overlay">
        <div class="spinner"></div>
        <p class="loading-text">Please wait... Processing your request</p>
    </div>
    <main>
        <div class="clock-container">
            <div id="date"></div>
            <div id="time"></div>
        </div>
    </main>
    <script src="../public/js/loading.js"></script>
    <script src="../public/js/clock.js"></script>
</body>

</html>