<?php

session_start();
require_once '../config/conn.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}






if (isset($_POST['add_course'])) {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];


    $conn->query("INSERT INTO `courses`(`course_code`, `course_name`)
    VALUES ('$course_code','$course_name')");

    header('location:courses.php');
    exit;
}

if (isset($_POST['update_course'])) {
    $id = $_POST['id'];
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];

    $conn->query("UPDATE `courses` SET `course_code`='$course_code',`course_name`='$course_name' WHERE id = $id");

    header('location:courses.php');
    exit;
}


$courses = $conn->query('SELECT * FROM courses');

$row_count = 1;
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
    <title>Courses</title>
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
            <div class="main-wrapper">

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#course">
                    <i class="fa fa-plus-circle"></i>
                    Assign Course
                </button>

                <div class="card shadow p-5 mt-4">
                    <table id="courseTable" class="display nowrap table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= $row_count ?></td>
                                    <td><?= $course['course_code'] ?></td>
                                    <td><?= $course['course_name'] ?></td>
                                    <td>
                                        <div class="action">
                                            <button
                                                type="submit" class="btn btn-primary update" data-bs-toggle="modal" data-bs-target="#editCourse" data-id="<?= $course['id'] ?>">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger delete-assign"
                                                data-id="<?= $course['id'] ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php $row_count++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>


    <!-- Add Course Modal -->
    <div
        class="modal fade"
        id="course"
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
                        Add Course
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-5">
                        <form action="courses.php" method="POST">
                            <div class="d-flex flex-column gap-4 mb-4">

                                <div class="form-group w-100">
                                    <label for="course_code">Course:</label>
                                    <input type="text" name="course_code" id="course_code" placeholder="Example: DIT" class="form-control" required>
                                </div>

                                <div class="form-group w-100">
                                    <label for="course_name">Course Name:</label>
                                    <input type="text" name="course_name" id="course_name" placeholder="Example: Diploma in Information Technology" class="form-control" required>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary" name="add_course">
                                    <i class="fa fa-plus-circle"></i>
                                    Add Course
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Course Modal -->
    <div
        class="modal fade"
        id="editCourse"
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
                    <div class="p-5">
                        <form action="courses.php" method="POST">
                            <input type="hidden" name="id" id="mid">
                            <div class="d-flex flex-column gap-4 mb-4">

                                <div class="form-group w-100">
                                    <label for="mcourse_code">Course:</label>
                                    <input type="text" name="course_code" id="mcourse_code" placeholder="Example: DIT" class="form-control" required>
                                </div>

                                <div class="form-group w-100">
                                    <label for="mcourse_name">Course Name:</label>
                                    <input type="text" name="course_name" id="mcourse_name" placeholder="Example: Diploma in Information Technology" class="form-control" required>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary" name="update_course">
                                    <i class="fa fa-plus-circle"></i>
                                    Add Course
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

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            new DataTable('#courseTable');

            // Update course modal
            $(".update").click(function() {
                var request = $.ajax({
                    url: "editcourses.php",
                    method: "GET",
                    data: {
                        id: $(this).data("id")
                    },
                    dataType: "json"
                });

                request.done(function(msg) {
                    $("#mid").val(msg.id);
                    $("#mcourse_code").val(msg.course_code);
                    $("#mcourse_name").val(msg.course_name);
                });
            });

            // Delete course with confirmation
            $(".delete-assign").click(function() {
                let courseId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "deletecourse.php",
                            method: "POST",
                            data: {
                                id: courseId
                            },
                            success: function(response) {
                                Swal.fire("Deleted!", "The course has been deleted.", "success")
                                    .then(() => {
                                        location.reload();
                                    });
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>



</body>

</html>