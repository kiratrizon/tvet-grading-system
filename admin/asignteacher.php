<?php

session_start();
require_once  '../config/conn.php';

$courses = $conn->query("SELECT DISTINCT subjects.s_course,
    courses.course_name 
    FROM subjects 
    JOIN courses ON subjects.s_course = courses.id");

$courses2 = $conn->query("SELECT DISTINCT subjects.s_course,
    courses.course_name 
    FROM subjects 
    JOIN courses ON subjects.s_course = courses.id");


function formatSchedule($schedule_days, $schedule_times)
{
    date_default_timezone_set('Asia/Manila');
    $days = explode(", ", $schedule_days);
    $times = explode(", ", $schedule_times);

    $formattedSchedule = [];

    foreach ($days as $index => $day) {
        if (!isset($times[$index])) continue;

        $time_range = explode(" - ", $times[$index]);
        $start_time = date("h:i:sA", strtotime($time_range[0]));
        $end_time = date("h:i:sA", strtotime($time_range[1]));

        $formattedSchedule[] = "$day - $start_time - $end_time";
    }

    return implode("<br>", $formattedSchedule);
}

// QUERY: Get assigned teachers and subjects
$assignTeachers = $conn->query("
    SELECT 
        teachers.t_name, 
        subjects.s_descriptive_title, 
        subjects.s_course, 
        subjects.s_course_code, 
        teacher_subjects.course, 
        teacher_subjects.year_level, 
        teacher_subjects.id, 
        teacher_subjects.subject_id, 
        teacher_subjects.teacher_id, 
        teacher_subjects.semester,
        teacher_subjects.school_year,
        teacher_subjects.assigned_date,
        GROUP_CONCAT(DISTINCT CONCAT(teacher_subjects.schedule_day, ' ', TIME_FORMAT(teacher_subjects.schedule_time_start, '%h:%i %p'), ' - ', TIME_FORMAT(teacher_subjects.schedule_time_end, '%h:%i %p')) ORDER BY teacher_subjects.schedule_day ASC SEPARATOR '<br>') AS schedule_details
    FROM teacher_subjects
    JOIN teachers ON teacher_subjects.teacher_id = teachers.t_id
    JOIN subjects ON teacher_subjects.subject_id = subjects.s_id 
    GROUP BY teachers.t_name, subjects.s_course_code, subjects.s_descriptive_title, teacher_subjects.course, teacher_subjects.year_level, teacher_subjects.semester, teacher_subjects.school_year
    ORDER BY teachers.t_name ASC
");




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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <title>Asign Subject</title>
    <style>
        .modal .form-select {
            z-index: 1055 !important;
        }

        span.select2.select2-container.select2-container--default {
            width: 100% !important;
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
                <h2 class="text-center" style="font-weight: 800; text-transform:uppercase">Add and Assign Instructors</h2>
                <!-- Modal trigger button -->
                <div class="message">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="text-center alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Warning!</strong> <br>
                            <?= is_array($_SESSION['error']) ? implode("<br>", $_SESSION['error']) : $_SESSION['error']; ?>
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
                            <strong>Success!</strong> <br> Teacher Updated Successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['updated']); ?>
                    <?php endif; ?>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#assignTeacher">
                    <i class="fa fa-plus-circle"></i>
                    Assign Schedule to Instructor
                </button>


                <div class="card shadow p-5 mt-4">
                    <table id="teacherTable" class="display nowrap table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Course Code</th>
                                <th>Assigned Subject</th>
                                <th>Schedule</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $row_count = 1; ?>
                            <?php foreach ($assignTeachers as $assign): ?>
                                <tr>
                                    <td style="font-size: 14px;"><?= $row_count; ?></td>
                                    <td style="font-size: 14px; text-transform:capitalize"><?= $assign['t_name'] ?></td>
                                    <td style="font-size: 14px;"><?= $assign['s_course_code'] ?></td>
                                    <td style="line-height: 1; font-size: 14px"><?= nl2br(wordwrap($assign['s_descriptive_title'], 30, "<br>\n")) ?></td>
                                    <td style="font-size: 14px;">
                                        <?= nl2br($assign['schedule_details']) ?>
                                    </td>

                                    <td>
                                        <div class="action">
                                            <!-- EDIT BUTTON -->
                                            <a href="edit_teacher_subject.php?teacher_id=<?= $assign['teacher_id'] ?>&teacher_subject_id=<?= $assign['subject_id'] ?>"
                                                target="_blank"
                                                class="btn btn-primary">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </a>

                                            <!-- VIEW BUTTON  -->
                                            <a href="view_teacher_subject.php?teacher_id=<?= $assign['teacher_id'] ?>&teacher_subject_id=<?= $assign['subject_id'] ?>"
                                                target="_blank"
                                                class="btn btn-warning">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- DELETE BUTTON -->
                                            <button type="button" class="btn btn-danger delete-assign"
                                                data-id="<?= $assign['id'] ?>" data-subject-id="<?= $assign['subject_id'] ?>" data-teacher-id="<?= $assign['teacher_id'] ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php $row_count++; ?>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </main>

    </div>


    <!-- Assign Teacher Modal -->
    <div class="modal fade" id="assignTeacher" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Assign Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignTeacherForm" action="assign_subject_process.php" method="POST">
                        <div class="d-flex flex-row gap-4 mb-4">
                            <div class="form-group w-100">
                                <label for="teacher">Teacher:</label>
                                <select name="teacher_id" required class="form-control">
                                    <option hidden selected disabled>Select Teacher</option>
                                    <?php
                                    $teachers = $conn->query("SELECT t_id, t_name FROM teachers");
                                    while ($row = $teachers->fetch_assoc()) { ?>
                                        <option value="<?= $row['t_id'] ?>"><?= $row['t_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="course">Course:</label>
                                <select id="course" name="course" required class="form-control course">
                                    <option hidden selected disabled>Course</option>
                                    <?php while ($row = $courses->fetch_assoc()) { ?>
                                        <option value="<?= $row['s_course'] ?>"><?= $row['course_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="year_level">Year Level:</label>
                                <select id="year_level" name="year_level" required disabled class="form-control">
                                    <option hidden selected disabled>Select Year Level</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-row gap-4 mb-5">
                            <div class="form-group w-100">
                                <label for="semester">Semester:</label>
                                <select id="semester" name="semester" required disabled class="form-control">
                                    <option selected hidden disabled>Select Semester</option>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="sy">School Year:</label>
                                <input list="schoolYears" id="sy" name="sy" class="form-control" placeholder="Select or Type School Year">
                                <datalist id="schoolYears">
                                    <option value="2024-2025"></option>
                                    <option value="2025-2026"></option>
                                    <option value="2026-2027"></option>
                                </datalist>
                            </div>

                            <div class="form-group w-100">
                                <label for="subject">Subject:</label>
                                <select id="subject" name="subject_id" required disabled class="form-control">
                                    <option hidden selected disabled>Select Descriptive Title</option>
                                </select>
                            </div>
                        </div>

                        <!-- SCHEDULE FIELDS -->
                        <div class="form-group mb-4">
                            <label for="schedule_day">Schedule:</label>
                            <table class="table" id="scheduleTable">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Section</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dito mag-a-append ng schedule rows dynamically -->
                                </tbody>
                            </table>
                            <button type="button" id="add_schedule" class="btn btn-secondary mb-3">+ Add Schedule</button>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i> Assign Subject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Assign Edit Teacher Modal -->
    <div
        class="modal fade"
        id="assignEditTeacher"
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
                        Assign Teacher
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="update_assign_subject_process.php" method="POST">
                        <input type="hidden" name="id" id="t_id">
                        <div class="d-flex flex-row gap-4 mb-4">
                            <div class="form-group w-100">
                                <label for="teacher_name">Teacher:</label>
                                <select id="teacher_name" name="teacher_name" required class="form-control">
                                    <option hidden selected disabled>Select Teacher</option>
                                    <?php
                                    $teachers = $conn->query("SELECT t_id, t_name FROM teachers");
                                    while ($row = $teachers->fetch_assoc()) { ?>
                                        <option value="<?= $row['t_id'] ?>"><?= $row['t_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="mecourse">Course:</label>
                                <select id="mecourse" name="course" required class="form-control course">
                                    <option hidden selected disabled>Course</option>
                                    <?php while ($row = $courses2->fetch_assoc()) { ?>
                                        <option value="<?= $row['s_course'] ?>"><?= $row['course_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="meyear_level">Year Level:</label>
                                <select id="meyear_level" name="year_level" required class="form-control">
                                    <option hidden selected disabled>Select Year Level</option>
                                </select>
                            </div>
                        </div>


                        <div class="d-flex flex-row gap-4 mb-4">
                            <div class="form-group w-100">
                                <label for="mesemester">Semester:</label>
                                <select id="mesemester" name="semester" required disabled class="form-control">
                                    <option selected hidden disabaled>Select Semester</option>
                                </select>
                            </div>


                            <div class="form-group w-100">
                                <label for="mesy">School Year</label>
                                <input list="schoolYears" id="mesy" name="sy" class="form-control" placeholder="Select or Type School Year">
                                <datalist id="schoolYears">
                                    <option value="2024/2025"></option>
                                    <option value="2025/2026"></option>
                                    <option value="2026/2027"></option>
                                </datalist>
                            </div>

                            <div class="form-group w-100">
                                <label for="mesubject">Subject:</label>
                                <select id="mesubject" name="subject_id" required disabled class="form-control">
                                    <option hidden selected disabaled>Select Descriptive Title</option>
                                </select>
                            </div>
                        </div>


                        <div class="d-flex flex-row gap-4 mb-5">
                            <div class="form-group w-100">
                                <label for="meschedule_day">Schedule Day:</label>
                                <select id="meschedule_day" name="schedule_day" required class="form-control">
                                    <option hidden selected disabled>Select Day</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>

                            <div class="form-group w-100">
                                <label for="meschedule_time_start">Time Start:</label>
                                <input type="time" id="meschedule_time_start" name="schedule_time_start" required class="form-control">
                            </div>

                            <div class="form-group w-100">
                                <label for="meschedule_time_end">Time End:</label>
                                <input type="time" id="meschedule_time_end" name="schedule_time_end" required class="form-control">
                            </div>
                        </div>


                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i>
                                Assign Subject
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>




    <!-- Assign View Teacher Modal -->
    <div
        class="modal fade"
        id="assignViewTeacher"
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
                        View Assign Details
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="p-4">
                            <div class="d-flex flex-row gap-4 mb-4">
                                <div class="form-group w-100">
                                    <label for="teacher_name">Teacher:</label>
                                    <input type="text" id="mvteacher_name" disabled class="form-control">
                                </div>

                            </div>
                            <div class="form-group w-100 mb-4">
                                <label for="mecourse">Course:</label>
                                <input type="text" id="mvcourse" disabled class="form-control">

                            </div>
                            <div class="form-group w-100 mb-4">
                                <label for="mesubject">Subject:</label>
                                <input type="text" id="mvssubject" disabled class="form-control">
                            </div>


                            <div class="d-flex flex-row gap-4 mb-5">
                                <div class="form-group w-100">
                                    <label for="meyear_level">Year Level:</label>
                                    <input type="text" id="mvyearlevel" disabled class="form-control">

                                </div>
                                <div class="form-group w-100">
                                    <label for="mesemester">Semester:</label>
                                    <input type="text" id="mvsemester" disabled class="form-control">

                                </div>




                            </div>
                            <div class="d-flex flex-row gap-4 mb-5">

                                <div class="form-group w-100">
                                    <label for="mesy">School Year</label>
                                    <input type="text" id="mvschoolyear" disabled class="form-control">

                                </div>
                                <div class="form-group w-100">
                                    <label for="mesy">Assigned Date</label>
                                    <input type="text" id="mvassigned" disabled class="form-control">

                                </div>

                            </div>

                            <div class="d-flex flex-row gap-4 mb-5">
                                <div class="form-group w-100">
                                    <label for="mvschedule_day">Schedule Day:</label>
                                    <input type="text" id="mvschedule_day" name="schedule_day" class="form-control" disabled>

                                </div>

                                <div class="form-group w-100">
                                    <label for="mvschedule_time_start">Time Start:</label>
                                    <input type="time" id="mvschedule_time_start" name="schedule_time_start" disabled required class="form-control">
                                </div>

                                <div class="form-group w-100">
                                    <label for="mvschedule_time_end">Time End:</label>
                                    <input type="time" id="mvschedule_time_end" name="schedule_time_end" disabled required class="form-control">
                                </div>
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


    <!-- Assign Teacher Js -->
    <script src="../public/js/asignteacher.js"></script>
    <script src="../public/js/edit_assign_subject.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            fetch("get_courses.php")
                .then(response => response.json())
                .then(courseFullNames => {
                    document.querySelectorAll(".course").forEach(courseSelect => {
                        Array.from(courseSelect.options).forEach(option => {
                            if (courseFullNames[option.value]) {
                                option.textContent = courseFullNames[option.value];
                            }
                        });
                    });
                })
                .catch(error => console.error("Error fetching courses:", error));



            document.querySelectorAll(".delete-assign").forEach(button => {
                button.addEventListener("click", function() {
                    let assignId = this.getAttribute("data-id");
                    let subjectId = this.getAttribute("data-subject-id");
                    let teacherId = this.getAttribute("data-teacher-id");

                    Swal.fire({
                        title: "Are you sure?",
                        text: "Deleting this will also remove associated student grades (if no other teacher is assigned)!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "No, cancel!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("delete_assign_teacher.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: `assign_id=${assignId}&subject_id=${subjectId}&teacher_id=${teacherId}`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: data.message,
                                            icon: "success",
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire("Error!", data.message, "error");
                                    }
                                })
                                .catch(error => {
                                    Swal.fire("Error!", "Something went wrong.", "error");
                                });
                        }
                    });
                });
            });


        });
        $(document).ready(function() {
            // alert("lasjdlaksdjalkdjalskdj");
            // let table = new DataTable("#teacherTable");


            new DataTable('#teacherTable', {
                layout: {
                    topStart: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                }
            });

        });
    </script>
    <script>
        document.getElementById('add_schedule').addEventListener('click', function() {
            let tableBody = document.querySelector('#scheduleTable tbody');
            let row = document.createElement('tr');
            row.innerHTML = `
            <td>
                <select name="schedule_day[]" class="form-control">
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </td>
            <td>
                <input type="time" name="schedule_time_start[]" class="form-control" required>
            </td>
            <td>
                <input type="time" name="schedule_time_end[]" class="form-control" required>
            </td>
            <td>
                <input type="text" name="schedule_section[]" class="form-control" placeholder="Section">
            </td>
            <td>
                <button type="button" class="btn btn-danger remove-row">Remove</button>
            </td>
            `;
            tableBody.appendChild(row);
        });

        // Remove row event
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    </script>

</body>

</html>