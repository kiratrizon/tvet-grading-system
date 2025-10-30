<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a') {
    header('location: ../index.php');
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
    <link rel="stylesheet" href="../public/style/dataTables.dataTables.min.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <title>Print Grades</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    </head>

<body>
    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <main class="main">
            <div class="main-wrapper" style="padding: 2rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="m-0">Print Student Grades</h3>
                    <button class="btn btn-outline-secondary no-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                </div>

                <div class="card p-3 mb-3">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Juan Dela Cruz" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Program</label>
                            <input type="text" name="course" class="form-control" placeholder="e.g., BSIT" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Year Level</label>
                            <input type="text" name="year" class="form-control" placeholder="e.g., 1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" class="form-control" placeholder="e.g., 1st" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">School Year</label>
                            <input type="text" name="schoolYear" class="form-control" placeholder="e.g., 2024-2025" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                        </div>
                    </form>
                </div>

                <div id="results" class="card p-3">
                    <div id="studentMeta" class="mb-2"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Descriptive Title</th>
                                    <th>Final Rating</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="gradesBody">
                                <tr><td colspan="4" class="text-center text-muted">Use the filter above to load grades.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        $(function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                const name = $(this).find('[name="name"]').val();
                const course = $(this).find('[name="course"]').val();
                const year = $(this).find('[name="year"]').val();
                const semester = $(this).find('[name="semester"]').val();
                const schoolYear = $(this).find('[name="schoolYear"]').val();

                $('#studentMeta').html(`<strong>Name:</strong> ${$('<div>').text(name).html()} &nbsp; | &nbsp; <strong>Program:</strong> ${$('<div>').text(course).html()} &nbsp; | &nbsp; <strong>Year:</strong> ${$('<div>').text(year).html()} &nbsp; | &nbsp; <strong>Semester:</strong> ${$('<div>').text(semester).html()} &nbsp; | &nbsp; <strong>SY:</strong> ${$('<div>').text(schoolYear).html()}`);

                $.post('fetch_grades.php', formData, function(html) {
                    $('#gradesBody').html(html);
                }).fail(function() {
                    $('#gradesBody').html('<tr><td colspan="4" class="text-center text-danger">Failed to fetch grades.</td></tr>');
                });
            })
        })
    </script>
</body>

</html>


