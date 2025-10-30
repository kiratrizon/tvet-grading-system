<?php
session_start();
require_once  '../config/conn.php';



if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

$teachers = $conn->query("SELECT teachers.*, COALESCE(web_users.usertype,'t') as usertype FROM teachers LEFT JOIN web_users ON web_users.email = teachers.t_user_name ORDER BY t_name ASC");

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
    <title>Instructors</title>
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
                <h2 class="text-center" style="font-weight: 800; text-transform:uppercase">Add Instructor Setup</h2>
                <!-- Modal trigger button -->
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addTeacher">
                    <i class="fa fa-plus-circle"></i> Add Instructor
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
                        <strong>Success!</strong> <br> Instructor Added Successfully!.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['updated'])): ?>
                    <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <br> Instructor Updated Successfully!.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['updated']); ?>
                <?php endif; ?>

                <div class="card shadow p-5 mt-3 mb-3">
                    <table id="teacherTable" class="display nowrap table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>User Name</th>
                                <th>Gender</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?= $row_count; ?></td>
                                    <td style="text-transform:capitalize"><?= $teacher['t_name'] ?></td>
                                    <td>
                                        <?php if (($teacher['usertype'] ?? 't') === 't') : ?>
                                            <span class="badge bg-primary"><i class="fa fa-check-circle"></i> Active</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger"><i class="fa fa-times-circle"></i> Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= $teacher['t_user_name'] ?></td>
                                    <td><?= $teacher['t_gender'] ?></td>
                                    <td>
                                        <div class="action">
                                            <button type="submit" class="btn btn-primary update" data-bs-toggle="modal" data-bs-target="#editTeacher" value="<?= $teacher['t_id'] ?>">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </button>
                                            <button type="submit" class="btn btn-warning view" data-bs-toggle="modal" data-bs-target="#videTeacher" value="<?= $teacher['t_id'] ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <?php if (($teacher['usertype'] ?? 't') === 't'): ?>
                                                <button class="btn btn-warning toggle-teacher" data-id="<?= $teacher['t_id'] ?>" data-action="deactivate">
                                                    <i class="fa fa-user-slash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success toggle-teacher" data-id="<?= $teacher['t_id'] ?>" data-action="activate">
                                                    <i class="fa fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Add Teacher Modal -->
    <div
        class="modal fade"
        id="addTeacher"
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
                        Add Instructor
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
                        <form action="addteacher.php" method="post">
                            <div class="d-flex flex-row gap-4 mb-4">
                                <div class="form-group w-100">
                                    <label for="fname">First Name</label>
                                    <input type="text" name="fname" id="fname" required class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="lname">Last Name</label>
                                    <input type="text" name="lname" id="lname" required class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-5">
                                <div class="form-group w-100">
                                    <span>Gender</span> <br>
                                    <label for="male">Male</label>
                                    <input type="radio" name="gender" id="male" value="male" required>

                                    <label for="female">Female</label>
                                    <input type="radio" name="gender" id="female" value="female" required>
                                </div>
                                <div class="form-group w-100">
                                    <label for="username">User Name</label>
                                    <input type="text" name="username" id="username" required class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="pass">Password</label>
                                    <input type="password" name="pass" id="pass" required class="form-control">
                                </div>
                            </div>

                            <div>
                                <button type="submit" name="submitTeacher" class="btn btn-primary">
                                    <i class="fa fa-paper-plane"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- View Teacher Modal -->
    <div
        class="modal fade"
        id="videTeacher"
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
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        View Instructor Details
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>

                        <div class="p-4">

                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="mvfname">First Name</label>
                                    <input type="text" name="fname" id="mvfname" required class="form-control" disabled>
                                </div>
                                <div class="form-group w-100">
                                    <label for="mvlname">Last Name</label>
                                    <input type="text" name="lname" id="mvlname" required class="form-control" disabled>
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4">

                                <div class="form-group w-100">
                                    <span>Gender</span> <b></b>
                                    <label for="mvmale">Male</label>
                                    <input type="radio" name="gender" id="mvmale" value="male" required disabled>
                                    <label for="mvfemale">Female</label>
                                    <input type="radio" name="gender" id="mvfemale" value="female" required disabled>
                                </div>


                                <div class="form-group w-100">
                                    <label for="mvusername">User Name</label>
                                    <input type="text" name="username" id="mvusername" class="form-control" required disabled>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


    <!-- Edit Teacher Modal -->
    <div
        class="modal fade"
        id="editTeacher"
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
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Edit Teacher
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="updateteacher.php" method="post">
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="oldemail" id="oldemail">

                        <div class="p-4">

                            <div class="d-flex flex-row gap-4 mb-3">

                                <div class="form-group w-100">
                                    <label for="mefname">First Name</label>
                                    <input type="text" name="fname" id="mefname" required class="form-control">
                                </div>
                                <div class="form-group w-100">
                                    <label for="melname">Last Name</label>
                                    <input type="text" name="lname" id="melname" required class="form-control">
                                </div>
                            </div>

                            <div class="d-flex flex-row gap-4 mb-4">
                                <div class="form-group w-100">
                                    <span>Gender</span> <br>
                                    <label for="memale">Male</label>
                                    <input type="radio" name="gender" id="memale" value="male" required>
                                    <label for="mefemale">Female</label>
                                    <input type="radio" name="gender" id="mefemale" value="female" required>
                                </div>


                                <div class="form-group w-100">
                                    <label for="meusername">User Name</label>
                                    <input type="text" name="username" id="meusername" required class="form-control" readonly>
                                </div>
                            </div>

                            <div>
                                <button type="submit" name="updateTeacher" class="btn btn-primary">
                                    <i class="fa fa-sync-alt"></i> Update
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="../public/js/subjects.js"></script>
    <!-- Plugin sa Data Table -->
    <script src="../public/js/dataTable/dataTables.min.js"></script>
    <script src="../public/js/dataTable/dataTables.buttons.js"></script>
    <script src="../public/js/dataTable/buttons.dataTables.js"></script>
    <script src="../public/js/dataTable/jszip.min.js"></script>
    <script src="../public/js/dataTable/pdfmake.min.js"></script>
    <script src="../public/js/dataTable/vfs_fonts.js"></script>
    <script src="../public/js/dataTable/buttons.html5.min.js"></script>
    <script src="../public/js/dataTable/buttons.print.min.js"></script>

    <!-- Para sa Teacher Edit og View Jsons -->
    <script src="../public/js/teachers.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".toggle-teacher").forEach(button => {
                button.addEventListener("click", function() {
                    let teacherId = this.getAttribute("data-id");
                    let action = this.getAttribute("data-action");

                    const verb = action === 'deactivate' ? 'Deactivate' : 'Activate';
                    Swal.fire({
                        title: verb + " instructor?",
                        text: action === 'deactivate' ? "They won't be able to sign in, records remain intact." : "Allow sign in again.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: action === 'deactivate' ? "#d33" : "#198754",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, " + verb.toLowerCase() + "!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("toggle_teacher_status.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: `teacher_id=${teacherId}&action=${action}`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: verb + "d!",
                                            text: data.message || "Status updated.",
                                            icon: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire("Error!", data.message || "Failed to update.", "error");
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

        })
    </script>
</body>

</html>