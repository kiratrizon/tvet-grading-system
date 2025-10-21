<?php
session_start();
require '../config/conn.php';
require '../config/myTools.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 't') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}



$teacherSubject = $_GET['teacher_subject'] ?? '';
if (empty($teacherSubject)) {
    header("location: mysubjects.php");
    exit;
}

// $subjects = $conn->query("SELECT id, name, final_rating, remarks, student_id 
//                        FROM student_grades 
//                        WHERE course_code = '$subject_code' ORDER BY name ASC");

$subjects = [];

$row_count = 1;

$studentGrades = $conn->query("SELECT sgv2.*, su.name AS student_name FROM student_grades_v2 sgv2 join student_users su on sgv2.student_id = su.id where sgv2.teacher_subject_id = '$teacherSubject'")->fetch_all(MYSQLI_ASSOC);

// myTools::display(json_encode($studentGrades));exit;

$teacherSubjectDetails = $conn->query("SELECT ts.*, s.s_course_code, s.s_descriptive_title, c.course_name, c.id AS course_id
                                        FROM teacher_subjects ts
                                        JOIN subjects s ON ts.subject_id = s.s_id
                                        JOIN courses c ON ts.course = c.id
                                        WHERE ts.id = '$teacherSubject'")->fetch_assoc();

if (!$teacherSubjectDetails) {
    header("location: mysubjects.php");
    exit;
}

$subject_code = $teacherSubjectDetails['s_course_code'];
$descriptiveTitle = $teacherSubjectDetails['s_descriptive_title'];
$course = $teacherSubjectDetails['course_name'];
$year_level = $teacherSubjectDetails['year_level'];
$semester = $teacherSubjectDetails['semester'];
$school_year = $teacherSubjectDetails['school_year'];
$subject_id = $teacherSubjectDetails['subject_id'];
$course_id = $teacherSubjectDetails['course'];
$section = '';


// $subject_code = isset($_GET['subject']) ? urldecode($_GET['subject']) : '';
// $descriptiveTitle = isset($_GET['title']) ? urldecode($_GET['title']) : '';
// $course = isset($_GET['course']) ? urldecode($_GET['course']) : '';
// $year_level = isset($_GET['year']) ? urldecode($_GET['year']) : '';
// $semester = isset($_GET['semester']) ? urldecode($_GET['semester']) : '';
// $school_year = isset($_GET['school_year']) ? urldecode($_GET['school_year']) : '';
// $subject_id = isset($_GET['subject_id']) ? urldecode($_GET['subject_id']) : '';
// $course_id = isset($_GET['course_id']) ? urldecode($_GET['course_id']) : '';
// $section = isset($_GET['section']) ? urldecode($_GET['section']) : '';



// echo "Subject Code: " . $subject_code . "<br>";
// echo "Title: " . $descriptiveTitle . "<br>";
// echo "Course: " . $course . "<br>";
// echo "Year Level: " . $year_level . "<br>";
// echo "Semester: " . $semester . "<br>";
// echo "School Year: " . $school_year . "<br>";

// exit;
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

    <title>Students in <?= htmlspecialchars($subject_code); ?></title>
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
                <!-- Modal trigger button -->

                <div class="headings text-center mb-5">
                    <h4 style="font-size: 16px; line-height:1"><?= $year_level ?> <br> <?= $semester ?> <?= $school_year ?></h4>
                    <h1 style="font-size: 24px; font-weight:900; line-height:1;">Students Enrolled in <?= $course ?></h1>
                    <h5 style="font-size: 16px; line-height:1"><?= htmlspecialchars($subject_code); ?> - <?= htmlspecialchars($descriptiveTitle); ?></h5>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addStudent">
                    <i class="fa fa-plus-circle"></i> Add Student Grade
                </button>
                <hr>
                <div class="import d-flex flex-row gap-5 mb-4">
                    <a href="mysubjects.php" class="btn btn-primary" style="border-radius: 50px;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <form action="import_excel_grades.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_SESSION['teacher_id']) ?>">
                        <input type="hidden" name="subject_code" value="<?= htmlspecialchars($subject_code) ?>">
                        <input type="hidden" name="course" value="<?= htmlspecialchars($course ?? '') ?>">
                        <input type="hidden" name="year_level" value="<?= htmlspecialchars($year_level ?? '') ?>">
                        <input type="hidden" name="semester" value="<?= htmlspecialchars($semester ?? '') ?>">
                        <input type="hidden" name="school_year" value="<?= htmlspecialchars($school_year ?? '') ?>">
                        <input type="hidden" name="descriptive_title" value="<?= htmlspecialchars($descriptiveTitle ?? '') ?>">
                        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">
                        <input type="hidden" name="section" value="<?= $section ?>">


                        <div class="d-flex flex-row gap-4">
                            <input type="file" name="file" accept=".xls,.xlsx,.csv" required class="form-control">
                            <button type="submit" class="btn btn-primary w-50">
                                <i class="fa-solid fa-file-import"></i> Import Students
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Import Button -->
                <hr>
                <div class="mt-4">
                    <form action="print_subject.php" method="post" target="_new">
                        <input type="hidden" id="teacher_id" name="teacher_id" value="<?= $_SESSION['teacher_id'] ?>">
                        <input type="hidden" name="course" value="<?= $course ?>">
                        <input type="hidden" name="year_level" value="<?= $year_level ?>">
                        <input type="hidden" name="semester" value="<?= $semester ?>">
                        <input type="hidden" name="school_year" value="<?= $school_year ?>">
                        <input type="hidden" name="descriptive_title" value="<?= $descriptiveTitle ?>">
                        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">
                        <input type="hidden" name="section" value="<?= $section ?>">

                        <div class="d-flex flex-row gap-4 align-items-center mb-3">

                            <button type="submit" class="btn btn-success" name="print_data">
                                <i class="fas fa-print" style="font-size: 12px;"></i> Print
                            </button>
                        </div>
                    </form>

                    <table id="teacherTable" class="display nowrap table table-bordered mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th><i class="fa-solid fa-user"></i> Students Name</th>
                                <th class="text-center"><i class="fa-solid fa-star"></i> Final Rating</th>
                                <th class="text-center"><i class="fa-solid fa-comment"></i> Remarks</th>
                                <th class="text-center"><i class="fa-solid fa-cogs"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?= $row_count; ?></td>
                                    <td><?= htmlspecialchars($subject['name']); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($subject['final_rating']); ?></td>
                                    <td class="text-center <?php
                                                            if ($subject['remarks'] === 'Incomplete') {
                                                                echo 'text-warning';
                                                            } elseif ($subject['remarks'] === 'Failed') {
                                                                echo 'text-danger';
                                                            } else {
                                                                echo 'text-success';
                                                            } ?>">
                                        <?= htmlspecialchars($subject['remarks']); ?>
                                    </td>

                                    <td>
                                        <div class="action">
                                            <button type="submit" class="btn btn-primary update" data-bs-toggle="modal" data-bs-target="#editGrades" value="<?= $subject['id'] ?>">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </button>
                                            <button type="submit" class="btn btn-warning view" data-bs-toggle="modal" data-bs-target="#viewGrades" value="<?= $subject['id'] ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger flag-lr" data-bs-toggle="modal" data-bs-target="#flagLRModal" data-student-id="<?= $subject['id'] ?>" data-subject-id="<?= $subject_id ?>" data-studid="<?= $subject['student_id'] ?>" data-studremarks="<?= $subject['remarks'] ?>"
                                                <?= ($subject['remarks'] === 'Passed') ? 'disabled' : ''; ?>>
                                                <i class="fa-solid fa-flag"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php $row_count++;
                            endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>



    <!-- Add Student Modal -->
    <div
        class="modal fade"
        id="addStudent"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        role="dialog"
        aria-labelledby="modalTitleId"
        aria-hidden="true">
        <div
            class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
            role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Add Student Grade
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-4">
                        <form action="insert_student_grade.php" method="post">
                            <input type="hidden" name="subject_code" value="<?= htmlspecialchars($subject_code) ?>">
                            <input type="hidden" name="course" value="<?= htmlspecialchars($course ?? '') ?>">
                            <input type="hidden" name="year_level" value="<?= htmlspecialchars($year_level ?? '') ?>">
                            <input type="hidden" name="semester" value="<?= htmlspecialchars($semester ?? '') ?>">
                            <input type="hidden" name="school_year" value="<?= htmlspecialchars($school_year ?? '') ?>">
                            <input type="hidden" name="course_code" value="<?= htmlspecialchars($subject_code ?? '') ?>">
                            <input type="hidden" name="descriptive_title" value="<?= htmlspecialchars($descriptiveTitle ?? '') ?>">
                            <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                            <input type="hidden" name="course_id" value="<?= $course_id ?>">
                            <input type="hidden" name="section" value="<?= $section ?>">



                            <div class="mb-3">
                                <label for="adstudentName" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="adstudentName" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="adfinalRating" class="form-label">Final Rating</label>
                                <input type="number" step="0.01" class="form-control" id="adfinalRating" name="final_rating" required>
                            </div>

                            <div class="mb-3">
                                <label for="adremarks" class="form-label">Remarks</label>
                                <input type="text" class="form-control" id="adremarks" name="remarks" required readonly>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Student Grade</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Flag LR -->
    <div class="modal fade" id="flagLRModal" tabindex="-1" aria-labelledby="flagLRLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="flagLRLabel">Flag Missing Requirement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="flagLRForm" action="flag_missing_requirement.php" method="post">
                        <input type="hidden" id="student_id" name="student_id">
                        <input type="hidden" id="subject_id" name="subject_id">
                        <input type="hidden" id="studid" name="studid">
                        <input type="hidden" id="studremarks" name="studremarks">
                        <div class="mb-3">
                            <label for="missing_requirement" class="form-label">Missing Requirement:</label>
                            <textarea class="form-control" id="missing_requirement" name="missing_requirement" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <!-- View Gradessssssssssssssssssssssssssssssssssssssssssssssss Modal -->
    <div
        class="modal fade"
        id="viewGrades"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"

        role="dialog"
        aria-labelledby="modalTitleId"
        aria-hidden="true">
        <div
            class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
            role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #321337;">
                    <h5 class="modal-title text-white" id="modalTitleId">
                        View Student Info
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="p-4">
                        <form action="updategrades.php" method="post">

                            <div class="form-group mb-3">
                                <label for="name" class="for-label">Student Name</label>
                                <input type="text" name="name" id="mvname" readonly class="form-control">
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="">Course Code</label>
                                    <input type="text" name="course_code" id="mvcourse_code" readonly class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="">Descriptive Title</label>
                                    <input type="text" name="descriptive" id="mvdescriptive" readonly class="form-control">
                                </div>

                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="">Year Level</label>
                                    <input type="text" name="year" id="mvyear" readonly class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="">Semester</label>
                                    <input type="text" name="semester" id="mvsemester" readonly class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="rating" class="for-label">Final Rating</label>
                                    <input type="text" name="rating" id="mvrating" readonly class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="remarks" class="for-label">Remarks</label>
                                    <input type="text" name="remarks" id="mvremarks" readonly class="form-control">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Gradessssssssssssssssssss Modal -->
    <div
        class="modal fade"
        id="editGrades"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"

        role="dialog"
        aria-labelledby="modalTitleId"
        aria-hidden="true">
        <div
            class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md"
            role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #321337;">
                    <h5 class="modal-title text-white" id="modalTitleId">
                        Edit Grades
                    </h5>
                    <button
                        type="button"
                        class="btn-close text-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-4">
                        <form action="updategrades.php" method="post">
                            <input type="hidden" name="id" id="grade_id">
                            <input type="hidden" name="course_code" id="course_code">
                            <input type="hidden" name="descriptive" id="descriptive">
                            <input type="hidden" name="year" id="year">
                            <input type="hidden" name="semester" id="semester">


                            <div class="form-group mb-3">
                                <label for="name" class="for-label">Student Name</label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group">
                                    <label for="rating" class="for-label">Final Rating</label>
                                    <input type="text" name="rating" id="rating" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="remarks" class="for-label">Remarks</label>
                                    <input type="text" name="remarks" id="remarks" class="form-control" readonly>
                                </div>
                            </div>


                            <div>
                                <button type="submit" name="update_subject" class="btn btn-primary">
                                    <i class="fa-solid fa-sync"></i> Update Grades
                                </button>
                            </div>

                        </form>
                    </div>
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

    <script src="../public/js/teacher_edit_grades.js"></script>

    <script>
        $(document).ready(function() {
            // alert("lasjdlaksdjalkdjalskdj");
            // let table = new DataTable("#teacherTable");

            $(".flag-lr").click(function() {
                var studentId = $(this).data("student-id");
                var subjectId = $(this).data("subject-id");
                var studid = $(this).data("studid");
                var studremarks = $(this).data("studremarks");
                // alert(studentId);
                // alert(subjectId);
                $("#student_id").val(studentId);
                $("#subject_id").val(subjectId);
                $("#studid").val(studid);
                $("#studremarks").val(studremarks);
            });


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


            document.getElementById("adfinalRating").addEventListener("input", function() {
                let rating = parseFloat(this.value);
                let remarksField = document.getElementById("adremarks");

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
                    remarksField.value = "Invalid Grade";
                }
            });

            new DataTable('#teacherTable', {
                // layout: {
                //     topStart: {
                //         buttons: ['copy', 'excel', 'pdf']
                //     }
                // }
            });




        })
    </script>
</body>

</html>