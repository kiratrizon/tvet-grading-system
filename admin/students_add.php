<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
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
                <h2 class="text-center" style="font-weight: 800; text-transform:uppercase">Add Student</h2>

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

    <script src="../public/js/admin_edit_grades.js"></script>

    <script>
        $(document).ready(function() {
            // DataTable Plugin

            document.getElementById("rating").addEventListener("input", function() {
                let rating = parseFloat(this.value);
                let remarksField = document.getElementById("remarks");

                if (isNaN(rating)) {
                    remarksField.value = "";
                    return;
                }

                if (rating >= 1.00 && rating <= 3.00) {
                    remarksField.value = "Passed";
                } else if (rating > 3.00 && rating <= 4.00) {
                    remarksField.value = "Conditional";
                } else if (rating > 4.00 && rating <= 5.00) {
                    remarksField.value = "Failed";
                } else {
                    remarksField.value = "Incomplete";
                }
            });




            let selectedCourse = "all";
            let selectedYear = "all";

            function filterStudents() {
                $(".print_students").hide();

                let courseSelector = selectedCourse === "all" ? ".print_students" : "[data-course='" + selectedCourse + "']";
                let yearSelector = selectedYear === "all" ? "" : "[data-year='" + selectedYear + "']";

                $(courseSelector + yearSelector).show();
            }

            $(".filter-course").click(function() {
                selectedCourse = $(this).data("course");
                filterStudents();
            });

            $(".filter-year").click(function() {
                selectedYear = $(this).data("year");
                filterStudents();
            });

            filterStudents();


            // Load Subjects & Grades via AJAX
            $(".view-subjects").click(function() {

                let studentId = $(this).data("id");

                // alert(studentId);
                // Set student ID sa hidden input sa form
                $("#print_student_id").val(studentId);

                let name = $(this).data("name");
                let course = $(this).data("course");
                let year = $(this).data("year");
                let semester = $(this).data("semester");
                let schoolYear = $(this).data("school");

                $.ajax({
                    url: "fetch_grades.php",
                    type: "POST",
                    data: {
                        name,
                        course,
                        year,
                        semester,
                        schoolYear
                    },
                    success: function(response) {
                        $("#subjectsTable").html(response);
                        $("#subjectsModal").modal("show");
                    }
                });
            });

            new DataTable('.list_students', {
                layout: {
                    topStart: {
                        buttons: ['excel', 'pdf']
                    }
                }
            });
        });
    </script>
</body>

</html>