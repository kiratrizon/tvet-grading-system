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

$fromNav = $_SESSION['from_nav'] ?? 1;
unset($_SESSION['from_nav']);

$teacherSubject = $_GET['teacher_subject'] ?? '';
if (empty($teacherSubject)) {
    header("location: mysubjects.php");
    exit;
}

$coverage = myTools::periodList(compact('conn'));

$firstId = strtolower($coverage[1]['label']) ?? null;

// $subjects = $conn->query("SELECT id, name, final_rating, remarks, student_id 
//                        FROM student_grades 
//                        WHERE course_code = '$subject_code' ORDER BY name ASC");

$subjects = [];

$row_count = 1;

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


// update all students to read flg 1 in teacher_subject_enrollees
$conn->query("UPDATE teacher_subject_enrollees SET read_flg = 1 WHERE teacher_subject_id = '$teacherSubject'");

$criteria = myTools::getGradingCriteriaByTeacherSubjectID([
    'conn' => $conn,
    'teacher_subject_id' => $teacherSubject
]);

if (!empty($criteria)) {
    $criteria[] = [
        'id' => 0,
        'teacher_subject_id' => $teacherSubject,
        'criteria_name' => 'Final Grade',
        'percentage' => 100
    ];
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <div class="headings text-center mb-5">
                    <h4 style="font-size: 16px; line-height:1"><?= $year_level ?> <br> <?= $semester ?> <?= $school_year ?></h4>
                    <h1 style="font-size: 24px; font-weight:900; line-height:1;">Students Enrolled in <?= $course ?></h1>
                    <h5 style="font-size: 16px; line-height:1"><?= htmlspecialchars($subject_code); ?> - <?= htmlspecialchars($descriptiveTitle); ?></h5>
                </div>

                <!-- <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addStudent">
                    <i class="fa fa-plus-circle"></i> Add Student Grade
                </button> -->
                <hr>
                <div class="import d-flex flex-row gap-5 mb-4">
                    <a href="mysubjects.php" class="btn btn-primary" style="border-radius: 50px;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <form action="enroll_students.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="teacher_subject" value="<?= $teacherSubject ?>">
                        <div class="d-flex flex-row gap-4">
                            <input type="file" name="file" accept=".xls,.xlsx,.csv" required class="form-control">
                            <!-- Import Button -->
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-file-import"></i> Import Students for this Course
                            </button>
                        </div>
                    </form>
                    <a href="enroll_students_sample.php" class="btn btn-secondary" target="_blank">
                        <i class="fa-solid fa-download"></i> Download Template
                    </a>
                </div>

                <hr>
                <div class="mt-4">
                    <div>
                        <form action="export_excel_student_for_grading.php" method="post" target="_new">
                            <div class="d-flex flex-row gap-4 align-items-center mb-3">
                                <input type="hidden" name="teacher_subject" value="<?= $teacherSubject ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-file-excel"></i> Generate Grading Template
                                </button>
                            </div>
                        </form>
                        <form action="print_subject.php" method="post" target="_new">
                            <div class="d-flex flex-row gap-4 align-items-center mb-3">
                                <input type="hidden" name="teacher_subject" value="<?= $teacherSubject ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa-solid fa-print"></i> Print Gradesheet
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- tab -->
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a id="navStudents" class="nav-link active" href="#">Student List</a>
                        </li>
                        <?php if (!empty($criteria)): ?>
                            <li class="nav-item">
                                <a id="navAddGrades" class="nav-link" href="#">Set Criteria</a>
                            </li>
                            <li class="nav-item">
                                <a id="navGrades" class="nav-link" href="#">Compute Grades</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div id="studentsTable">

                    </div>
                    <div id="addGradesArea" class="mt-3" style="display: none;">
                        <!-- Grading Criteria -->
                        <h4>Grading Criteria</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Criteria</th>
                                    <th>Percentage (%)</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($criteria)): ?>
                                    <?php foreach ($criteria as $criterion):
                                        if ($criterion['id'] == 0) {
                                            continue; // skip final grade
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($criterion['criteria_name']) ?></td>
                                            <td><?= htmlspecialchars($criterion['percentage']) ?>%</td>
                                            <td>
                                                <button class="btn btn-primary add-grades" value="<?= htmlspecialchars($criterion['id']) ?>">Add Assessments</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-danger">No grading criteria found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="gradesTable" class="mt-3" style="display: none;">
                        <ul class="nav nav-tabs" id="gradeTabs" role="tablist">
                            <?php foreach ($coverage as $key => $coverageVal): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $key == 1 ? 'active' : '' ?>" id="<?= strtolower($coverageVal['label']) ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= strtolower($coverageVal['label']) ?>" type="button" role="tab"><?= htmlspecialchars($coverageVal['label']) ?> <?= $coverageVal['weight'] ?>%</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="tab-content mt-3" id="gradeTabsContent">
                            <?php foreach ($coverage as $key => $coverageVal):
                                $label = $coverageVal['label'];
                                $weight = $coverageVal['weight'];
                            ?>
                                <div
                                    class="tab-pane fade<?= $key == 1 ? ' show active' : '' ?>"
                                    id="<?= strtolower($label) ?>"
                                    role="tabpanel">
                                    <h5 class="mt-3"><?= htmlspecialchars($label) ?> Period</h5>

                                    <!-- Criteria Nav Tabs -->
                                    <ul class="nav nav-tabs mt-2" id="criteriaTabs-<?= strtolower($label) ?>" role="tablist">
                                        <?php foreach ($criteria as $index => $criterion): ?>
                                            <li class="nav-item" role="presentation">
                                                <button
                                                    class="nav-link criteria-tab<?= $index == 0 ? ' active' : '' ?>"
                                                    id="criteria-<?= $criterion['id'] ?>-tab-<?= strtolower($label) ?>"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#criteria-<?= $criterion['id'] ?>-<?= strtolower($label) ?>"
                                                    type="button"
                                                    role="tab"
                                                    data-id="<?= $criterion['id'] ?>"
                                                    data-period="<?= strtolower($key) ?>">
                                                    <?= htmlspecialchars($criterion['criteria_name']) ?> <?= isset($criterion['percentage']) ? '(' . htmlspecialchars($criterion['percentage']) . '%)' : '' ?>
                                                </button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <!-- Criteria Content Panes -->
                                    <div class="tab-content mt-3" id="criteriaTabsContent-<?= strtolower($label) ?>">
                                        <?php foreach ($criteria as $index => $criterion): ?>
                                            <div
                                                class="tab-pane fade<?= $index == 0 ? ' show active' : '' ?>"
                                                id="criteria-<?= $criterion['id'] ?>-<?= strtolower($label) ?>"
                                                role="tabpanel">

                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>


                    </div>


                </div>
            </div>
        </main>
    </div>

    <!-- add grades modal -->
    <div class="modal fade" id="addGradesModal" tabindex="-1" aria-labelledby="addGradesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGradesModalLabel">Set Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gradesForm" enctype="multipart/form-data" method="post" action="insert_grades.php">
                        <input type="hidden" name="criterion_id" id="criterion_id">
                        <input type="hidden" name="teacher_subject_id" value="<?= htmlspecialchars($teacherSubject) ?>">
                        <input type="hidden" name="from_nav" value="2">

                        <div class="mb-3">
                            <label for="note-criteria" class="form-label">Note</label>
                            <input type="text" class="form-control" id="note-criteria" name="note_criteria" required placeholder="e.g. Quiz #1">
                        </div>

                        <!-- covered for prelim, midterm, and finals -->
                        <div class="mb-3">
                            <label for="covered" class="form-label">Coverage/Period</label>
                            <select name="covered" id="covered" class="form-select" required>

                            </select>

                        </div>

                        <!-- out of or total items -->
                        <div class="mb-3">
                            <label for="total_items" class="form-label">Total Items</label>
                            <input type="number" class="form-control" id="total_items" name="total_items" required placeholder="e.g. 100">
                        </div>

                        <!-- excel upload -->
                        <div class="mb-3">
                            <label for="gradesFile" class="form-label">Upload Grades File (Excel or CSV)</label>
                            <input type="file" class="form-control" id="gradesFile" name="grades_file" accept=".xls,.xlsx,.csv" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="gradesForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('./theme/modals.php'); ?>

    <script src="../public/js/loading.js"></script>

    <script src="../public/js/dataTable/dataTables.min.js"></script>
    <script src="../public/js/dataTable/dataTables.buttons.js"></script>
    <script src="../public/js/dataTable/buttons.dataTables.js"></script>
    <script src="../public/js/dataTable/jszip.min.js"></script>
    <script src="../public/js/dataTable/pdfmake.min.js"></script>
    <script src="../public/js/dataTable/vfs_fonts.js"></script>
    <script src="../public/js/dataTable/buttons.html5.min.js"></script>
    <script src="../public/js/dataTable/buttons.print.min.js"></script>
    <script src="../public/js/teacher_edit_grades.js"></script>

    <script src="../public/teacher/fetch_grades_all.js"></script>
    <script src="../public/teacher/fetch_grades.js"></script>
    <script>
        $(document).ready(function() {
            // alert("lasjdlaksdjalkdjalskdj");
            // let table = new DataTable("#teacherTable");

            $("#navStudents").click(function(e) {
                e.preventDefault();
                $("#studentsTable").show();
                $("#addGradesArea").hide();
                $("#gradesTable").hide();
                $("#navStudents").addClass("active");
                $("#navGrades").removeClass("active");
                $("#navAddGrades").removeClass("active");
                $.ajax({
                    url: 'student_grades_final.php',
                    type: 'POST',
                    data: {
                        teacher_subject_id: <?= json_encode($teacherSubject); ?>,
                    },
                    success: function(response) {
                        $("#studentsTable").html(response);
                        new DataTable("#teacherTable", {
                            // layout: {
                            //     topStart: '<"d-flex justify-content-between"fB>',
                            //  bottomStart: 'p',
                            // },
                        })
                    },
                })
            });

            // trigger $('#navStudents').click();
            $("#navStudents").trigger("click");

            $("#navGrades").click(function(e) {
                e.preventDefault();
                $("#studentsTable").hide();
                $("#addGradesArea").hide();
                $("#gradesTable").show();
                $("#navGrades").addClass("active");
                $("#navStudents").removeClass("active");
                $("#navAddGrades").removeClass("active");
                // Optionally trigger the first criteria of the first period on initial load
                const firstPeriodTab = $('.nav-link[data-bs-target="#<?= $firstId ?>"]'); // adjust if needed
                if (firstPeriodTab.length) {
                    const targetPane = $(firstPeriodTab.data('bs-target'));
                    const firstCriteriaTab = targetPane.find('.criteria-tab').first();
                    if (firstCriteriaTab.length) firstCriteriaTab.trigger('click');
                }
            });
            $("#navAddGrades").click(function(e) {
                e.preventDefault();
                $("#studentsTable").hide();
                $("#addGradesArea").show();
                $("#gradesTable").hide();
                $("#navAddGrades").addClass("active");
                $("#navStudents").removeClass("active");
                $("#navGrades").removeClass("active");
            });
            const fromNav = <?= json_encode($fromNav); ?>;
            if (fromNav == 2) {
                $("#navAddGrades").trigger("click");
            } else if (fromNav == 3) {
                $("#navGrades").trigger("click");
            }

            $(".add-grades").click(function() {
                const criterionId = $(this).val();
                $("#criterion_id").val(criterionId); // put value in hidden input
                // ajax... check teacherSubjectId


                $.ajax({
                    url: 'get_available_period.php',
                    type: 'POST',
                    data: {
                        teacher_subject_id: <?= json_encode($teacherSubject); ?>,
                    },
                    success: function(response) {
                        const periods = JSON.parse(response);
                        const coveredSelect = $("#covered");
                        coveredSelect.html('<option value="" disabled selected>---</option>'); // reset options
                        Object.entries(periods).forEach(([key, label]) => {
                            coveredSelect.append(`<option value="${key}">${label}</option>`);
                        });
                        $("#addGradesModal").modal("show"); // show Bootstrap modal
                    },
                    error: function(xhr) {
                        const msg = xhr.responseText ?? "This action is temporarily unavailable.";
                        Swal.fire({
                            title: "Error!",
                            icon: "warning",
                            text: msg,
                            confirmButton: false,
                            timer: 2000
                        })
                    }
                })
            });


            $(document).on('shown.bs.tab', '[data-bs-target^="#"]', function(e) {
                const periodPane = $($(this).data('bs-target')); // e.g. #prelim
                const firstCriteriaTab = periodPane.find('.criteria-tab').first();

                // Trigger click on first criteria inside the selected period
                if (firstCriteriaTab.length) {
                    firstCriteriaTab.trigger('click');
                }
            });

            let countFetch = 0;

            $(document).on('click', '.criteria-tab', function() {
                const criterionId = $(this).data('id');
                const period = $(this).data('period');
                const target = $(this).data('bs-target'); // tab-pane selector
                const teacherSubject = <?= json_encode($teacherSubject); ?>;
                // remove previous content
                // $(`${target} #fetch_grade_response_${countFetch}`).remove();
                // countFetch++;

                // Show loading state
                $(target).html('<div class="text-center py-3">Loading...</div>');

                const url = criterionId == 0 ? 'fetch_grades_all.php' : 'fetch_grades.php';
                // AJAX request
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        criterion_id: criterionId,
                        period: period,
                        teacher_subject: teacherSubject,
                        count_fetch: countFetch
                    },
                    success: function(response) {
                        $(target).html(response);
                        new DataTable(`${target} #gradesTable`, {
                            // layout: {
                            //     topStart: {
                            //         buttons: ['copy', 'excel', 'pdf']
                            //     }
                            // }
                        });
                    },
                    error: function() {
                        $(target).html('<div class="text-danger text-center py-3">No data.</div>');
                    }
                });
            });

        })
    </script>
</body>

</html>