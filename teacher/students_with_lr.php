<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['teacher_id'])) {
    header("location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$query = "SELECT smr.id, sg.name AS student_name, s.s_course_code, s.s_descriptive_title, smr.missing_requirement 
          FROM student_missing_requirements smr
          JOIN student_grades sg ON smr.student_id = sg.id
          JOIN subjects s ON smr.subject_id = s.s_id
          WHERE smr.flagged_by_teacher = ? ORDER BY sg.name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Students with Lack of Requirements</title>
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
                <h2>Subjects with Missing Requirements or Failed Grades</h2>
                <table id="student_lr" class="table table-bordered mt-3">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Course Code</th>
                            <th>Descriptive Title</th>
                            <th>Missing Requirements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $count = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $count++; ?></td>
                                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                                    <td><?= htmlspecialchars($row['s_course_code']); ?></td>
                                    <td><?= htmlspecialchars($row['s_descriptive_title']); ?></td>
                                    <td><?= htmlspecialchars($row['missing_requirement']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    <script>
        $(document).ready(function() {
            new DataTable('#student_lr', {
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