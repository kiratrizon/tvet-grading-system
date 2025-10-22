<?php

session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 't') {
    header("location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$conditions = [
    "ts.teacher_id = '$teacher_id'",
];

$course_filter = null;
$semester_filter = null;
$school_year_filter = null;

if (isset($_GET['course']) && !empty($_GET['course'])) {
    $course_filter = $conn->real_escape_string($_GET['course']);
    $conditions[] = "c.id = '$course_filter'";
}

if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $semester_filter = $conn->real_escape_string($_GET['semester']);
    $conditions[] = "ts.semester = '$semester_filter'";
}

if (isset($_GET['school_year']) && !empty($_GET['school_year'])) {
    $school_year_filter = $conn->real_escape_string($_GET['school_year']);
    $conditions[] = "ts.school_year = '$school_year_filter'";
}

$where_clause = implode(" AND ", $conditions);

$mysubjects = $conn->query("
    SELECT 
        ts.id,
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
        s.s_descriptive_title,
        count(gc.id) AS criteria_count
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.s_id
    JOIN courses c ON s.s_course = c.id
    LEFT JOIN grading_criteria gc ON ts.id = gc.teacher_subject_id
    WHERE $where_clause
    GROUP BY ts.id
");


// $year_levels = $conn->query("SELECT DISTINCT year_level FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$courses = $conn->query("
    SELECT DISTINCT c.id, c.course_name 
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.s_id
    JOIN courses c ON s.s_course = c.id
    WHERE ts.teacher_id = '$teacher_id'
");
$semesters = $conn->query("SELECT DISTINCT semester FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$school_years = $conn->query("SELECT DISTINCT school_year FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
// $sections = $conn->query("SELECT DISTINCT section FROM teacher_subjects WHERE teacher_id = '$teacher_id'");

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
    <title>Program & Course</title>
    <style>
        [class*="add-criteria-"] i, [class*="edit-criteria-"] i {
            pointer-events: none;
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
                <h3 class="mb-3">Program & Course</h3>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <select id="filterCourse" class="form-control">
                            <option value="">Select Program</option>
                            <?php while ($row = $courses->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $course_filter == $row['id'] ? 'selected' : '' ?>><?= $row['course_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterSemester" class="form-control">
                            <option value="">Select Semester</option>
                            <?php while ($row = $semesters->fetch_assoc()): ?>
                                <option value="<?= $row['semester'] ?>" <?= $semester_filter == $row['semester'] ? 'selected' : '' ?>><?= $row['semester'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterSchoolYear" class="form-control">
                            <option value="">Select School Year</option>
                            <?php while ($row = $school_years->fetch_assoc()): ?>
                                <option value="<?= $row['school_year'] ?>" <?= $school_year_filter == $row['school_year'] ? 'selected' : '' ?>><?= $row['school_year'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button id="applyFilters" class="btn btn-primary">Search</button>
                        <button id="clearFilters" class="btn btn-secondary">Clear</button>
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
                            <a href="importstudents.php?teacher_subject=<?= $subject['id'] ?>">

                                <div class="card-content">
                                    <ul>
                                        <li class="d-flex justify-content-between">
                                            <h5><?= $subject['year_level'] ?></h5>
                                            <?php if ($subject['criteria_count'] == 0) { ?>
                                                <button class="btn add-criteria-<?= $subject['id'] ?>" onclick="addCriteria(event, '<?= $subject['id'] ?>')">
                                                    <i class="fa-solid fa-square-plus"></i>
                                                </button>
                                                <?php } else { ?>
                                                <button class="btn edit-criteria-<?= $subject['id'] ?>" onclick="addCriteria(event, '<?= $subject['id'] ?>')">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <?php }
                                            ?>

                                        </li>
                                        <li>
                                            <h5 style="font-weight: 900; line-height:1;color: <?= $subject['criteria_count'] > 0 ? '#321337' : '#6c757d'; ?>"><?= $subject['course_name'] ?></h5>
                                        </li>
                                        <li>
                                            <h5><?= $subject['s_course_code'] ?></h5>
                                        </li>
                                        <li><span><?= $subject['s_descriptive_title'] ?></span></li>
                                        <li><span><?= $subject['school_year'] ?></span></li>
                                        <li><span><?= $subject['semester'] ?></span></li>
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
        function addCriteria(e, subjectId) {
            e.preventDefault();
            e.stopPropagation();
            window.location.href = `add_criteria.php?ts_id=${subjectId}`;
        }
        $(document).ready(function() {
            $('#applyFilters').on('click', function() {
                const course = $('#filterCourse').val();
                const semester = $('#filterSemester').val();
                const school_year = $('#filterSchoolYear').val();

                let queryParams = [];

                if (course) {
                    queryParams.push(`course=${encodeURIComponent(course)}`);
                }
                if (semester) {
                    queryParams.push(`semester=${encodeURIComponent(semester)}`);
                }
                if (school_year) {
                    queryParams.push(`school_year=${encodeURIComponent(school_year)}`);
                }

                const queryString = queryParams.length ? `?${queryParams.join('&')}` : '';
                window.location.href = `mysubjects.php${queryString}`;
            });

            $('#clearFilters').on('click', function() {
                window.location.href = 'mysubjects.php';
            });

            $(document).on("mouseover", "button[class*='add-criteria-']", function() {
                $(this).html(`Add Criteria <i class="fa-solid fa-square-plus"></i>`);
            });
            $(document).on("mouseout", "button[class*='add-criteria-']", function() {
                $(this).html(`<i class="fa-solid fa-square-plus"></i>`);
            });

            $(document).on("mouseover", "button[class*='edit-criteria-']", function() {
                $(this).html(`Edit Criteria <i class="fa-solid fa-pen"></i>`);
            });
            $(document).on("mouseout", "button[class*='edit-criteria-']", function() {
                $(this).html(`<i class="fa-solid fa-pen"></i>`);
            });
        });
    </script>

</body>

</html>