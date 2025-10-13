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

$teacher_id = $_SESSION['teacher_id'];

$mysubjects = $conn->query("
    SELECT 
        s.s_course_code,
        ts.school_year, 
        ts.year_level, 
        ts.semester,
        ts.subject_id,
        s.s_course, 
        c.course_name, 
        c.id AS course_id, 
        c.course_code AS CC, 
        s.s_descriptive_title 
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.s_id
    JOIN courses c ON s.s_course = c.id
    WHERE ts.teacher_id = '$teacher_id'
");


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
    <title>My Subjects</title>
</head>

<body>

    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <div id="loading-overlay">
            <div class="spinner"></div>
            <p class="loading-text">Please wait... Processing your request</p>
        </div>
        <main class="main">
            <div class="main-wrapper" style="padding: 4%;">

                <div class="message-wrapper">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="text-center alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Warning!</strong> <br> <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <br> <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['updated'])): ?>
                        <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <br> <?= $_SESSION['updated'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['updated']); ?>
                    <?php endif; ?>
                </div>

                <div class="card-wrapper d-flex gap-4">

                    <?php foreach ($mysubjects as $subject): ?>
                        <div class="card shadow w-100 p-4">
                            <a href="importstudents.php?subject=<?= urlencode($subject['s_course_code']) ?>&title=<?= urlencode($subject['s_descriptive_title']) ?>&course=<?= urlencode($subject['CC']) ?>&year=<?= urlencode($subject['year_level']) ?>&semester=<?= urlencode($subject['semester']) ?>&school_year=<?= urlencode($subject['school_year']) ?>&subject_id=<?= urlencode($subject['subject_id']) ?>&teacher_id=<?= urlencode($teacher_id) ?>&course_id=<?= urlencode($subject['course_id']); ?>">

                                <div class="card" style="border: none;">
                                    <div class="card-content">
                                        <ul>
                                            <li>
                                                <h5><?= $subject['year_level'] ?></h5>
                                            </li>
                                            <li>
                                                <h5 style="font-weight: 900; line-height:1;color: #321337;"><?= $subject['course_name'] ?></h5>
                                            </li>
                                            <li>
                                                <h5><?= $subject['s_course_code'] ?></h5>
                                            </li>
                                            <li>
                                                <span><?= $subject['s_descriptive_title'] ?></span>
                                            </li>
                                            <li>
                                                <span><?= $subject['school_year'] ?></span>
                                            </li>
                                            <li>
                                                <span><?= $subject['semester'] ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach ?>

                </div>
            </div>
        </main>
    </div>
    <script src="../public/js/loading.js"></script>
</body>

</html>