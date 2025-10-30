<?php

session_start();
require_once '../config/conn.php';


$result = $conn->query("
    SELECT subjects.*, courses.course_code 
    FROM subjects 
    LEFT JOIN courses ON subjects.s_course = courses.id 
    ORDER BY 
    FIELD(subjects.s_year_level, 'First Year', 'Second Year', 'Third Year'), 
    FIELD(subjects.s_semester, 'First Semester', 'Second Semester', 'Summer')
")->fetch_all(MYSQLI_ASSOC);




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
    <title>Subjects</title>
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
                <h2 class="text-center" style="font-weight: 800; text-transform:uppercase">Add Subjects</h2>
                <!-- Modal trigger button -->
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addSubjects">
                    <i class="fa fa-plus-circle"></i>
                    Add Subjects
                </button>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="text-center alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Warning!</strong> <br> User Name is already used!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <br>Subject <strong><?= $_SESSION['success'] ?></strong> Added Successfully!.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['updated']) and isset($_SESSION['year'])): ?>
                    <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <br> Subject <strong><?= $_SESSION['updated'] ?></strong> Updated Successfully!. <br> <strong><?= $_SESSION['year'] ?></strong>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['year']); ?>
                    <?php unset($_SESSION['updated']); ?>
                <?php endif; ?>


                <?php
                $subjects = [];

                // Fetch data from database
                foreach ($result as $row){
                    $course = $row['course_code'];
                    $year_level = strtolower(str_replace(' ', '-', $row['s_year_level']));
                    $semester = $row['s_semester'];

                    // Group by course
                    if (!isset($subjects[$course])) {
                        $subjects[$course] = [];
                    }

                    // Group by year level
                    if (!isset($subjects[$course][$year_level])) {
                        $subjects[$course][$year_level] = [];
                    }

                    // Group by semester
                    if (!isset($subjects[$course][$year_level][$semester])) {
                        $subjects[$course][$year_level][$semester] = [];
                    }

                    // Add subject
                    $subjects[$course][$year_level][$semester][] = $row;
                }
                ?>

                <!-- Simple Search -->
                <div class="card p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Search Subjects</label>
                            <input type="text" id="subjectSearch" class="form-control" placeholder="Type course code or title...">
                        </div>
                        <div class="col-md-6 text-muted small">
                            Matches across all tables; showing only sections with results.
                        </div>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons">
                    <div class="d-flex flex-row gap-4 align-items-center mb-3">
                        <h3 style="font-size:18px; font-weight: 600; line-height:1; margin:0">Filter by Program:</h3>
                        <div class="buttons">
                            <button class="btn btn-primary filter-course" data-course="all">Show All</button>
                            <?php foreach ($subjects as $course => $years) : ?>
                                <button class="btn btn-secondary filter-course" data-course="<?= strtolower(str_replace(' ', '-', $course)) ?>">
                                    <?= $course ?>
                                </button>
                            <?php endforeach; ?>
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


                <div class="subjects-wrapper">

                    <!-- Curriculum Tables -->
                    <?php foreach ($subjects as $course => $years): ?>
                        <div class="curriculum-section course-<?= strtolower(str_replace(' ', '-', $course)) ?>">
                            <div class="heading text-center mt-5">
                                <?php if ($course == 'DBOT'): ?>
                                    <h1 style="font-size: 24px; font-weight: 700; color:#8F00FF; line-height:1;">Diploma in Business Operation Technology</h1> <!-- Display Course Name -->
                                <?php endif; ?>
                                <?php if ($course == 'DSOT'): ?>
                                    <h1 style="font-size: 24px; font-weight: 700; color:#8F00FF; line-height:1;">Diploma in Security Operation Technology</h1> <!-- Display Course Name -->
                                <?php endif; ?>
                                <?php if ($course == 'DIST'): ?>
                                    <h1 style="font-size: 24px; font-weight: 700; color:#8F00FF; line-height:1;">Diploma in Information Systems Technology</h1> <!-- Display Course Name -->
                                <?php endif; ?>
                                <?php if ($course == 'DIT'): ?>
                                    <h1 style="font-size: 24px; font-weight: 700; color:#8F00FF; line-height:1;">Diploma Information Technology</h1> <!-- Display Course Name -->
                                <?php endif; ?>
                            </div>

                            <?php foreach ($years as $year => $semesters): ?>
                                <div class="year-section year-<?= $year ?>">
                                    <h2 style="font-size: 18px; font-weight:900; color:#321337" class="text-center"><?= ucwords(str_replace('-', ' ', $year)) ?></h2>
                                    <?php foreach ($semesters as $semester => $courses): ?>
                                        <div class="curriculum-table">
                                            <div class="card shadow p-5 mt-3 mb-3">
                                                <table class="display nowrap curriculumOutline table table-bordered">
                                                    <h3 style="font-size: 18px; background:#8E01FF; padding-block:4px" class="text-white text-center"><?= $semester ?></h3>
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th style="font-size: 14px;">Course Code</th>
                                                            <th style="font-size: 14px;">Descriptive Title</th>
                                                            <th style="font-size: 14px;">NTH</th>
                                                            <th style="font-size: 14px;">Units</th>
                                                            <th style="font-size: 14px;">Lee</th>
                                                            <th style="font-size: 14px;">Lab</th>
                                                            <th style="font-size: 14px;">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($courses as $courseData): ?>
                                                            <tr>
                                                                <td style="font-size: 14px;"><?= $courseData['s_course_code'] ?></td>
                                                                <td style="line-height: 1; font-size: 14px"><?= nl2br(wordwrap($courseData['s_descriptive_title'], 30, "<br>\n")) ?></td>
                                                                <td style="font-size: 14px;"><?= $courseData['s_nth'] ?? '' ?></td>
                                                                <td style="font-size: 14px;"><?= $courseData['s_units'] ?? '' ?></td>
                                                                <td style="font-size: 14px;"><?= $courseData['s_lee'] ?? '' ?></td>
                                                                <td style="font-size: 14px;"><?= $courseData['s_lab'] ?? '' ?></td>
                                                                <td>
                                                                    <div class="action">
                                                                        <button type="submit" class="btn btn-primary update" data-bs-toggle="modal" data-bs-target="#editSubjects" value="<?= $courseData['s_id'] ?>">
                                                                            <i class="fa-solid fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button type="submit" class="btn btn-warning view" data-bs-toggle="modal" data-bs-target="#viewSubjects" value="<?= $courseData['s_id'] ?>">
                                                                            <i class="fa-solid fa-eye"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Subjects Modal -->
    <div
        class="modal fade"
        id="addSubjects"
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
                        Add Subjects
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="addsubjects.php" method="post">

                        <div class="p-4">
                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="year">Year Level</label>
                                    <select name="year" id="year" class="form-control">
                                        <option selected disabled hidden>Select Year Level</option>
                                        <option value="First Year">First Year</option>
                                        <option value="Second Year">Second Year</option>
                                        <option value="Third Year">Third Year</option>
                                    </select>
                                </div>


                                <div class="form-group w-100">
                                    <label for="semester">Semester</label>
                                    <select name="semester" id="semester" class="form-control">
                                        <option selected disabled hidden>Select Semester</option>
                                        <option value="First Semester">First Semester</option>
                                        <option value="Second Semester">Second Semester</option>
                                        <option value="Summer">Summer</option>
                                    </select>
                                </div>



                                <div class="form-group w-100">
                                    <label for="course">Program</label>
                                    <select name="course" id="course" class="form-control">
                                        <option selected disabled hidden>Select Course</option>
                                        <?php
                                        $programs = $conn->query('SELECT id, program_name FROM programs');
                                        foreach ($programs as $program): ?>
                                            <option value="<?= $program['id'] ?>" class="form-control"><?= $program['program_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="course_code">Course Code</label>
                                    <input type="text" name="course_code" id="course_code" required class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="descriptive_title">Descriptive Title</label>
                                    <input type="text" name="descriptive_title" id="descriptive_title" required class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="nth">NTH</label>
                                    <input type="text" name="nth" id="nth" class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="units">Units</label>
                                    <input type="number" name="units" id="units" required class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="lee">Lee</label>
                                    <input type="number" name="lee" id="lee" required class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="lab">Lab</label>
                                    <input type="number" name="lab" id="lab" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-4">
                                <div class="form-group w-100">
                                    <label for="covered_qualification">Covered Qualification</label>
                                    <input type="text" name="covered_qualification" id="covered_qualification" class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="pre_requisite">Pre-Requisite</label>
                                    <input type="text" name="pre_requisite" id="pre_requisite" class="form-control">
                                </div>
                            </div>


                            <div class="">
                                <button type="submit" name="submit_subject" class="btn btn-primary">
                                    <i class="fa fa-plus-circle"></i>
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


    <!-- View Subjects Modal -->
    <div
        class="modal fade"
        id="viewSubjects"
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
                        View Subject Details
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="updatesubjects.php" method="post">
                        <div class="p-4">


                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="mvsemester">Semester</label>
                                    <select name="semester" id="mvsemester" disabled class="form-control">
                                    </select>
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvyear">Year Level</label>
                                    <select name="year" id="mvyear" disabled class="form-control">
                                    </select>
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvcourse">Program</label>
                                    <select name="course" id="mvcourse" disabled class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="mvcourse_code">Course Code</label>
                                    <input type="text" name="course_code" id="mvcourse_code" disabled class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvdescriptive_title">Descriptive Title</label>
                                    <input type="text" name="descriptive_title" id="mvdescriptive_title" disabled class="form-control">
                                </div>
                            </div>
                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="mvnth">NTH</label>
                                    <input type="text" name="nth" id="mvnth" disabled class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="mvunits">Units</label>
                                    <input type="number" name="units" id="mvunits" disabled class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvlee">Lee</label>
                                    <input type="number" name="lee" id="mvlee" disabled class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvlab">Lab</label>
                                    <input type="number" name="lab" id="mvlab" disabled class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="mvcovered_qualification">Covered Qualification</label>
                                    <input type="text" name="covered_qualification" id="mvcovered_qualification" disabled class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvpre_requisite">Pre-Requisite</label>
                                    <input type="text" name="pre_requisite" id="mvpre_requisite" disabled class="form-control">
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


    <!-- Edit Subjects Modal -->
    <div class="modal fade" id="editSubjects" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="updatesubjects.php" method="post">
                        <input type="hidden" name="id" id="me_s_id">
                        <div class="p-4">
                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="meyear">Year Level</label>
                                    <select name="year" id="meyear" class="form-control">
                                        <option value="First Year">First Year</option>
                                        <option value="Second Year">Second Year</option>
                                        <option value="Third Year">Third Year</option>
                                    </select>
                                </div>

                                <div class="form-group w-100">
                                    <label for="mesemester">Semester</label>
                                    <select name="semester" id="mesemester" class="form-control">
                                        <option value="First Semester">First Semester</option>
                                        <option value="Second Semester">Second Semester</option>
                                        <option value="Summer">Summer</option>
                                    </select>
                                </div>

                                <div class="form-group w-100">
                                    <label for="mecourse">Program</label>
                                    <select name="course" id="mecourse" class="form-control">
                                        <option selected disabled hidden>Select Course</option>
                                        <?php
                                        require_once '../config/conn.php';
                                        $programs = $conn->query('SELECT id, program_name FROM programs');
                                        foreach ($programs as $program): ?>
                                            <option value="<?= $program['id'] ?>"><?= $program['program_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="mecourse_code">Course Code</label>
                                    <input type="text" name="course_code" id="mecourse_code" required class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="medescriptive_title">Descriptive Title</label>
                                    <input type="text" name="descriptive_title" id="medescriptive_title" required class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-3">
                                <div class="form-group w-100">
                                    <label for="menth">NTH</label>
                                    <input type="text" name="nth" id="menth" class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="meunits">Units</label>
                                    <input type="number" name="units" id="meunits" required class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="melee">Lee</label>
                                    <input type="number" name="lee" id="melee" required class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="melab">Lab</label>
                                    <input type="number" name="lab" id="melab" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-4">
                                <div class="form-group w-100">
                                    <label for="mecovered_qualification">Covered Qualification</label>
                                    <input type="text" name="covered_qualification" id="mecovered_qualification" class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="mepre_requisite">Pre-Requisite</label>
                                    <input type="text" name="pre_requisite" id="mepre_requisite" class="form-control">
                                </div>
                            </div>

                            <div>
                                <button type="submit" name="update_subject" class="btn btn-primary">Update Subject</button>
                            </div>
                        </div>
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

    <!-- para sa subject edit -->

    <script src="../public/js/subjects.js"></script>

    <script>
        $(document).ready(function() {
            // Default na course na ipapakita sa unang pag-load (puwede mong palitan ng course code na gusto mo)
            let selectedCourse = "dit"; // Halimbawa, DIT ang unang course na ipapakita
            let selectedYear = "all";

            function filterContent() {
                $(".curriculum-section").hide(); // Itago lahat ng sections
                $(".year-section").hide(); // Itago lahat ng year sections

                // Gamitin ang selectedCourse para magfilter kung anong course ang ipapakita
                let courseSelector = selectedCourse === "all" ? ".curriculum-section" : ".course-" + selectedCourse;
                let yearSelector = selectedYear === "all" ? ".year-section" : ".year-" + selectedYear;

                $(courseSelector).each(function() {
                    $(this).show(); // Ipakita ang mga section ng napiling course
                    $(this).find(yearSelector).show(); // Ipakita ang section ng year
                });
            }

            $(".filter-course").click(function() {
                selectedCourse = $(this).data("course");
                filterContent();
            });

            $(".filter-year").click(function() {
                selectedYear = $(this).data("year");
                filterContent();
            });

            // Initial na pag-filter, ipapakita lang ang default na course (DIT sa kasalukuyan)
            filterContent();

            // Simple client-side subject search
            $('#subjectSearch').on('input', function(){
                const q = $(this).val().toLowerCase().trim();
                // Show all if empty
                if (!q) {
                    $('.curriculum-section').show();
                    $('.year-section').show();
                    $('.curriculum-table').show();
                    $('.curriculumOutline tbody tr').show();
                    return;
                }

                // Hide everything first
                $('.curriculumOutline tbody tr').each(function(){
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.indexOf(q) !== -1);
                });

                // Hide empty tables and sections
                $('.curriculum-table').each(function(){
                    const anyVisible = $(this).find('tbody tr:visible').length > 0;
                    $(this).toggle(anyVisible);
                });
                $('.year-section').each(function(){
                    const anyVisible = $(this).find('.curriculum-table:visible').length > 0;
                    $(this).toggle(anyVisible);
                });
                $('.curriculum-section').each(function(){
                    const anyVisible = $(this).find('.year-section:visible').length > 0;
                    $(this).toggle(anyVisible);
                });
            });

            // Button para sa Print,Export/ etc... Plugins
            new DataTable('.curriculumOutline', {
                layout: {
                    topStart: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                }
            });
        })
    </script>


</body>

</html>