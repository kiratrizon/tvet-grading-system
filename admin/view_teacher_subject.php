<?php
session_start();
require_once '../config/conn.php';


$teacher_id2 = intval($_GET['teacher_id']);
$subject_id2 = intval($_GET['teacher_subject_id']);

// Kunin ang details ng teacher at subject
$assignTeachers = $conn->query("SELECT 
        teachers.t_id AS teacher_id,
        teachers.t_name, 
        subjects.s_descriptive_title, 
        subjects.s_course, 
        courses.course_name,  
        subjects.s_course_code, 
        subjects.s_year_level, 
        subjects.s_semester, 
        teacher_subjects.course, 
        teacher_subjects.year_level, 
        teacher_subjects.semester,
        teacher_subjects.school_year,
        teacher_subjects.section
    FROM teacher_subjects
    JOIN teachers ON teacher_subjects.teacher_id = teachers.t_id
    JOIN subjects ON teacher_subjects.subject_id = subjects.s_id
    JOIN courses ON subjects.s_course = courses.id 
    WHERE teacher_subjects.teacher_id = $teacher_id2 
    AND teacher_subjects.subject_id = $subject_id2
    LIMIT 1");


if ($assignTeachers->num_rows == 0) {
    $_SESSION['error'] = "No record found!";
    header("location: asignteacher.php");
    exit;
}

$teacher = $assignTeachers->fetch_assoc();
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
    <link rel="stylesheet" href="../public/style/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="../public/style/buttons.dataTables.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Edit Teacher Subject</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }
    </style>
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

                <h2>View Instructor Subject</h2>

                <form action="update_teacher_subject.php" method="POST">
                    <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id']; ?>">
                    <input type="hidden" name="subject_id" value="<?= $subject_id2; ?>">
                    <div class="d-flex flex-row gap-4">
                        <div class="form-group w-100">
                            <label>Teacher Name:</label>
                            <input type="text" value="<?= $teacher['t_name']; ?>" disabled class="form-control">
                        </div>
                        <div class="form-group w-100">
                            <label>Course:</label>
                            <input type="text" value="<?= $teacher['course_name']; ?>" disabled class="form-control">
                        </div>
                    </div>

                    <div class="d-flex flex-row gap-4">
                        <div class="form-group w-100">
                            <label>Course Code:</label>
                            <input type="text" value="<?= $teacher['s_course_code']; ?>" disabled class="form-control">
                        </div>
                        <div class="form-group w-100">
                            <label>Subject:</label>
                            <input type="text" value="<?= $teacher['s_descriptive_title']; ?>" disabled class="form-control">
                        </div>
                    </div>


                    <div class="d-flex flex-row gap-4">
                        <div class="from-group w-100">
                            <label>Year Level:</label>
                            <input type="text" value="<?= $teacher['year_level']; ?>" disabled class="form-control">
                        </div>
                        <div class="form-group w-100">
                            <label>Semester:</label>
                            <input type="text" value="<?= $teacher['semester']; ?>" disabled class="form-control">
                        </div>
                        <div class="form-group w-100">
                            <label>School Year:</label>
                            <input type="text" name="school_year" value="<?= $teacher['school_year']; ?>" disabled class="form-control">
                        </div>
                    </div>
                    
                    <div class="d-flex flex-row gap-4">
                        <div class="form-group w-100">
                            <label>Room:</label>
                            <input type="text" value="<?= $teacher['section']; ?>" disabled class="form-control">
                        </div>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>

</html>