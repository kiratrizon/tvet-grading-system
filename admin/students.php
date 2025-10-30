<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../index.php");
    exit;
}

$semesters = $conn->query("SELECT semester FROM `teacher_subjects` group by semester;")->fetch_all(MYSQLI_ASSOC);

$year_levels = $conn->query("SELECT year_level FROM `teacher_subjects` group by year_level;")->fetch_all(MYSQLI_ASSOC);

$school_years = $conn->query("SELECT school_year FROM `teacher_subjects` group by school_year;")->fetch_all(MYSQLI_ASSOC);

$courses = $conn->query("SELECT id, course_code FROM `courses`")->fetch_all(MYSQLI_ASSOC);

$course_map = [];
foreach ($courses as $course) {
    $course_map[$course['id']] = $course['course_code'];
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
    <link rel="stylesheet" href="../public/style/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="../public/style/buttons.dataTables.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <title>Student Grades</title>
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
                <h2 class="text-center" style="font-weight: 800; text-transform:uppercase">Student Grades</h2>

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

                <div class="row g-3 mt-4 align-items-end">
                    <!-- Quick search by student name -->
                    <div class="col-md-4">
                        <label for="searchName" class="form-label">Search Student Name</label>
                        <input type="text" id="searchName" class="form-control" placeholder="e.g., Juan Dela Cruz">
                    </div>
                    <!-- Course -->
                    <div class="col-md-2">
                        <label for="filterCourse" class="form-label">Program</label>
                        <select id="filterCourse" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= strtolower(str_replace(' ', '-', $c['course_code'])) ?>">
                                    <?= htmlspecialchars($c['course_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Year Level -->
                    <div class="col-md-2">
                        <label for="filterYear" class="form-label">Year Level</label>
                        <select id="filterYear" class="form-select">
                            <option value="">All Level</option>
                            <?php foreach ($year_levels as $y): ?>
                                <option value="<?= strtolower(str_replace(' ', '-', $y['year_level'])) ?>">
                                    <?= htmlspecialchars($y['year_level']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Semester -->
                    <div class="col-md-2">
                        <label for="filterSemester" class="form-label">Semester</label>
                        <select id="filterSemester" class="form-select">
                            <option value="">All Semesters</option>
                            <?php foreach ($semesters as $s): ?>
                                <option value="<?= strtolower(str_replace(' ', '-', $s['semester'])) ?>">
                                    <?= htmlspecialchars($s['semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- School Year -->
                    <div class="col-md-2">
                        <label for="filterSY" class="form-label">School Year</label>
                        <select id="filterSY" class="form-select">
                            <option value="">All Years</option>
                            <?php foreach ($school_years as $sy): ?>
                                <option value="<?= strtolower(str_replace(' ', '-', $sy['school_year'])) ?>">
                                    <?= htmlspecialchars($sy['school_year']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <button id="btnSearch" class="btn btn-primary">Search</button>
                        <button id="btnClear" class="btn btn-secondary">Clear</button>
                    </div>
                </div>


                <hr>


                <div id="subjectList">

                </div>
            </div>
        </main>
    </div>

    <script src="../public/js/loading.js"></script>
    <!-- Plugin sa Data Table -->
    <script src="../public/js/dataTable/dataTables.min.js"></script>
    <script src="../public/js/dataTable/dataTables.buttons.js"></script>
    <script src="../public/js/dataTable/buttons.dataTables.js"></script>
    <script src="../public/js/dataTable/jszip.min.js"></script>
    <script src="../public/js/dataTable/pdfmake.min.js"></script>
    <script src="../public/js/dataTable/vfs_fonts.js"></script>
    <script src="../public/js/dataTable/buttons.html5.min.js"></script>
    <script src="../public/js/dataTable/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#btnSearch').on('click', function() {
                // Implement search functionality here
                const course = $('#filterCourse').val();
                const year = $('#filterYear').val();
                const semester = $('#filterSemester').val();
                const sy = $('#filterSY').val();
                const target = $('#subjectList');

                // ajax call

                $.ajax({
                    url: 'get_all_subjects.php',
                    type: 'POST',
                    data: {
                        q: $('#searchName').val(),
                        course: course,
                        year: year,
                        semester: semester,
                        sy: sy
                    },
                    beforeSend: function() {
                        target.html(`<div class="text-center my-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p>Loading data, please wait...</p>
                                    </div>`);
                    },
                    success: function(response) {
                        // Handle the response to update the table
                        target.html(response);
                    },
                    error: function() {
                        target.html('<div class="alert alert-danger" role="alert">An error occurred while fetching data. Please try again.</div>');
                    }
                });
            });

            $('#btnClear').on('click', function() {
                $('#filterCourse').val('');
                $('#filterYear').val('');
                $('#filterSemester').val('');
                $('#filterSY').val('');
                $('#searchName').val('');

                $('#btnSearch').trigger('click');
            });
            $('#btnSearch').trigger('click');
        });
    </script>

    <script src="../public/admin/get_all_subjects.js"></script>
</body>

</html>