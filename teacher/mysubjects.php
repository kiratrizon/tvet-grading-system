<?php

session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 't') {
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
        ts.section,
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


$year_levels = $conn->query("SELECT DISTINCT year_level FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$courses = $conn->query("
    SELECT DISTINCT c.id, c.course_name 
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.s_id
    JOIN courses c ON s.s_course = c.id
    WHERE ts.teacher_id = '$teacher_id'
");
$semesters = $conn->query("SELECT DISTINCT semester FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$school_years = $conn->query("SELECT DISTINCT school_year FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$sections = $conn->query("SELECT DISTINCT section FROM teacher_subjects WHERE teacher_id = '$teacher_id'");

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
                <h3 class="mb-3">My Subjects</h3>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <select id="filterYear" class="form-control">
                            <option value="">Select Year Level</option>
                            <?php while ($row = $year_levels->fetch_assoc()): ?>
                                <option value="<?= $row['year_level'] ?>"><?= $row['year_level'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterCourse" class="form-control">
                            <option value="">Select Course</option>
                            <?php while ($row = $courses->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['course_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterSemester" class="form-control">
                            <option value="">Select Semester</option>
                            <?php while ($row = $semesters->fetch_assoc()): ?>
                                <option value="<?= $row['semester'] ?>"><?= $row['semester'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterSchoolYear" class="form-control">
                            <option value="">Select School Year</option>
                            <?php while ($row = $school_years->fetch_assoc()): ?>
                                <option value="<?= $row['school_year'] ?>"><?= $row['school_year'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterSection" class="form-control">
                            <option value="">Select Section</option>
                            <?php while ($row = $sections->fetch_assoc()): ?>
                                <option value="<?= $row['section'] ?>"><?= $row['section'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="card-wrapper d-flex gap-4 flex-wrap">
                    <?php foreach ($mysubjects as $subject): ?>
                        <div class="card shadow w-100 p-4 subject-card"
                            data-year="<?= $subject['year_level'] ?>"
                            data-course="<?= $subject['course_id'] ?>"
                            data-semester="<?= $subject['semester'] ?>"
                            data-school-year="<?= $subject['school_year'] ?>"
                            data-section="<?= $subject['section'] ?>">
                            <a href="importstudents.php?subject=<?= urlencode($subject['s_course_code']) ?>&title=<?= urlencode($subject['s_descriptive_title']) ?>&course=<?= urlencode($subject['CC']) ?>&year=<?= urlencode($subject['year_level']) ?>&semester=<?= urlencode($subject['semester']) ?>&school_year=<?= urlencode($subject['school_year']) ?>&section=<?= urlencode($subject['section']) ?>&subject_id=<?= urlencode($subject['subject_id']) ?>&teacher_id=<?= urlencode($teacher_id) ?>&course_id=<?= urlencode($subject['course_id']); ?>">

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
                                        <li><span><?= $subject['s_descriptive_title'] ?></span></li>
                                        <li><span><?= $subject['school_year'] ?></span></li>
                                        <li><span><?= $subject['semester'] ?></span></li>
                                        <li><span>Section: <?= $subject['section'] ?></span></li>
                                    </ul>
                                </div>
                            </a>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            function filterSubjects() {
                let year = $('#filterYear').val();
                let course = $('#filterCourse').val();
                let semester = $('#filterSemester').val();
                let schoolYear = $('#filterSchoolYear').val();
                let section = $('#filterSection').val();

                let count = 0;
                $('.subject-card').each(function() {
                    let show = true;
                    if (year && $(this).data('year') != year) show = false;
                    if (course && $(this).data('course') != course) show = false;
                    if (semester && $(this).data('semester') != semester) show = false;
                    if (schoolYear && $(this).data('school-year') != schoolYear) show = false;
                    if (section && $(this).data('section') != section) show = false;

                    if (show) {
                        count++;
                        if (count <= 1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else {
                        $(this).hide();
                    }
                });

                if ($('.subject-card:visible').length < $('.subject-card').length) {
                    $('#showMore').show();
                } else {
                    $('#showMore').hide();
                }
            }

            function showAllSubjects() {
                $('.subject-card').show();
                $('#showMore').hide();
            }

            $('#filterYear, #filterCourse, #filterSemester, #filterSchoolYear, #filterSection').change(filterSubjects);
            $('#showMore').click(showAllSubjects);

            // Initial load: limit to 1 subject
            $('.subject-card:gt(0)').hide();
            if ($('.subject-card').length > 1) {
                $('#showMore').show();
            }
        });
    </script>

</body>

</html>