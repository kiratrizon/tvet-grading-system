<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

$student_id = $_SESSION['student_id'];

$studentSchoolyears = $conn->query("SELECT ts.school_year FROM teacher_subject_enrollees tse join teacher_subjects ts on tse.teacher_subject_id = ts.id WHERE tse.student_id = '$student_id' group by ts.school_year")->fetch_all(MYSQLI_ASSOC);

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
    <title>My Subjects</title>
</head>

<body>
    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <main class="main">

            <div class="main-wrapper" style="padding: 4%;">
                <h2>Welcome, <?= $_SESSION['student_name']; ?>!</h2>
                <p>Below are your enrolled course and corresponding grades:</p>

                <div class="row  mb-4">
                    <div class="col mb-2">
                        <select id="searchSchoolYear" class="form-control">
                            <option value="all">All School Years</option>
                            <?php foreach ($studentSchoolyears as $sy): ?>
                                <option value="<?= $sy['school_year'] ?>"><?= $sy['school_year'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- search button -->
                    <div class="col mb-2">
                        <button id="searchButton" class="btn btn-primary">Search</button>
                    </div>

                    <!-- table -->
                </div>
                <div id="studentSubjects">

                </div>
            </div>
        </main>
    </div>

    <script src="../public/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#searchButton").click(function() {
                var btn = $(this);
                var school_year = $("#searchSchoolYear").val();

                $.ajax({
                    method: "POST",
                    url: "get_grades.php",
                    data: {
                        school_year
                    },

                    beforeSend: function() {
                        $("#studentSubjects").html(`
                            <div class="text-center p-3">
                                <div class="spinner-border" role="status"></div>
                                <div>Loading...</div>
                            </div>
                        `);
                        btn.prop('disabled', true);
                    },

                    success: function(response) {
                        $("#studentSubjects").html(response);
                    },

                    error: function() {
                        $("#studentSubjects").html("<div class='alert alert-danger'>An error occurred while fetching data.</div>");
                    },

                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });

            // Auto-trigger search on page load
            $("#searchButton").trigger("click");
        });
    </script>
</body>

</html>