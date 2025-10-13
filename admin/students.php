<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../index.php");
    exit;
}

// Query para kunin ang students grouped by course, year, semester, at school year
$query = $conn->query(" SELECT sg.id,
    sg.course,
    sg.year_level,
    sg.semester,
    sg.school_year,
    sg.name,
    sg.teacher_id,
    sg.descriptive_title,
    c.course_code,
    c.course_name
    FROM student_grades AS sg
    JOIN courses AS c ON sg.course = c.id
    GROUP BY course, year_level, semester, school_year, name, teacher_id
");

$students = [];
while ($row = $query->fetch_assoc()) {
    $group_key = "{$row['course_name']}|{$row['year_level']}|{$row['semester']}|{$row['school_year']}";
    if (!isset($students[$group_key])) {
        $students[$group_key] = [];
    }
    $students[$group_key][] = $row;
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

                <!-- Filter Buttons -->
                <div class="filter-buttons mt-5">

                    <div class="d-flex flex-row gap-4 align-items-center mb-3">
                        <h3 style="font-size:18px; font-weight: 600; line-height:1; margin:0">Filter by Course:</h3>
                        <div class="button">
                            <button class="btn btn-primary filter-course" data-course="all">Show All</button>
                            <?php
                            $courses = $conn->query("SELECT DISTINCT courses.course_code, courses.course_name FROM student_grades
                            JOIN courses ON student_grades.course = courses.id
                            ORDER BY course ASC");
                            while ($row = $courses->fetch_assoc()) :
                            ?>
                                <button class="btn btn-secondary filter-course" data-course="<?= strtolower(str_replace(' ', '-', $row['course_name'])) ?>">
                                    <?= $row['course_code'] ?>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="d-flex flex-row gap-4 align-items-center">
                        <h3 style="font-size:18px; font-weight: 600; line-height:1; margin:0">Filter by Year Level:</h3>
                        <div class="buttons">
                            <button class="btn btn-secondary filter-year" data-year="all">All Years</button>
                            <button class="btn btn-secondary filter-year" data-year="first-year">First Year</button>
                            <button class="btn btn-secondary filter-year" data-year="second-year">Second Year</button>
                            <button class="btn btn-secondary filter-year" data-year="third-year">Third Year</button>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="students">
                    <?php foreach ($students as $group_key => $studentList):
                        list($course, $year_level, $semester, $school_year) = explode('|', $group_key);
                        $courseSlug = strtolower(str_replace(' ', '-', $course));
                        $yearSlug = strtolower(str_replace(' ', '-', $year_level));
                        $teacher_id = !empty($studentList) ? $studentList[0]['teacher_id'] : '';
                        $descriptive_title = !empty($studentList) ? $studentList[0]['descriptive_title'] : ''; ?>

                        <div class="print_students" data-course="<?= htmlspecialchars($courseSlug) ?>" data-year="<?= htmlspecialchars($yearSlug) ?>">
                            <h3 class="mt-4" style="font-weight: 800;"><?= htmlspecialchars($course) ?></h3>
                            <h5><?= htmlspecialchars($year_level) ?> | <?= htmlspecialchars($semester) ?> | <?= htmlspecialchars($school_year) ?></h5>
                            <div class="card shadow p-5 mt-3 mb-3">
                                <table class="table table-bordered list_students">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($studentList as $student): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($student['name']) ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm view-subjects"
                                                        data-id="<?= htmlspecialchars($student['id']) ?>"
                                                        data-name="<?= htmlspecialchars($student['name']) ?>"
                                                        data-course="<?= htmlspecialchars($student['course']) ?>"
                                                        data-year="<?= htmlspecialchars($year_level) ?>"
                                                        data-semester="<?= htmlspecialchars($semester) ?>"
                                                        data-school="<?= htmlspecialchars($school_year) ?>">
                                                        View Grades
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para sa Subjects & Grades -->
    <div class="modal fade" id="subjectsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subjects & Grades</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="tor_print.php" method="post" target="_new">
                        <input type="hidden" id="print_student_id" name="student_id">

                        <div class="d-flex flex-row gap-4 align-items-center mb-3">
                            <label for="department">Click to Print</label>
                            <button type="submit" class="btn btn-success" name="print_data">
                                <i class="fas fa-print" style="font-size: 12px;"></i> Print
                            </button>
                        </div>
                    </form>
                    <table class="table table-bordered print_grades">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Descriptive Title</th>
                                <th>Final Rating</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="subjectsTable">
                            <!-- Dynamic Data Here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Grades Modal -->
    <div class="modal fade" id="edit_grades" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student Grades</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="updategrades.php" method="post">
                        <input type="hidden" name="id" id="grade_id">

                        <div class="form-group w-100">
                            <label>Student Name</label>
                            <input type="text" name="name" id="name" class="form-control" readonly>
                        </div>

                        <div class="d-flex flex-row gap-4 mb-4">

                            <div class="form-group w-100">
                                <label>Year Level</label>
                                <input type="text" name="year" id="year" class="form-control" readonly>
                            </div>

                            <div class="form-group w-100">
                                <label>Course Code</label>
                                <input type="text" name="course_code" id="course_code" class="form-control" readonly>
                            </div>
                            <div class="form-group w-100">
                                <label>Descriptive Title</label>
                                <input type="text" name="descriptive" id="descriptive" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="d-flex flex-row gap-4 mb-5">
                            <div class="form-group w-100">
                                <label>Semester</label>
                                <input type="text" name="semester" id="semester" class="form-control" readonly>
                            </div>

                            <div class="form-group w-100">
                                <label>Final Rating</label>
                                <input type="text" name="rating" id="rating" class="form-control">
                            </div>
                            <div class="form-group w-100">
                                <label>Remarks</label>
                                <input type="text" name="remarks" id="remarks" class="form-control" readonly>
                            </div>
                        </div>


                        <button type="submit" class="btn btn-primary" name="update_subject">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
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