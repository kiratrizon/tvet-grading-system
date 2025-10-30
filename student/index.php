<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 's') {
    header("location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student course with course name
$student_query = "SELECT su.course, c.course_name 
                  FROM student_users su
                  JOIN courses c ON su.course = c.id
                  WHERE su.id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_row = $student_result->fetch_assoc();

// Get course details
$student_course = $student_row['course'];
$student_course_name = $student_row['course_name'];




$missing_query = "SELECT sm.*, 
                         s.s_course, s.s_course_code, s.s_descriptive_title, 
                         ts.semester, ts.year_level, ts.school_year, 
                         sm.remarks,
                         t.t_name AS teacher_name,
                         c.course_code, 
                         c.course_name
                  FROM student_missing_requirements sm
                  JOIN subjects s ON sm.subject_id = s.s_id
                  JOIN teacher_subjects ts ON sm.subject_id = ts.subject_id
                  JOIN teachers t ON ts.teacher_id = t.t_id
                  JOIN student_users su ON sm.studid = su.id
                  JOIN courses c ON su.course = c.id 
                  WHERE sm.studid = ?";

$stmt = $conn->prepare($missing_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$missing_result = $stmt->get_result();

$missing_alerts = [];
while ($row = $missing_result->fetch_assoc()) {
    $missing_alerts[] = $row;
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
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <title>Student Dashboard</title>
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .card-title {
            font-weight: bold;
            color: #333;
        }

        .card-text {
            color: #555;
        }
    </style>
</head>

<body>
    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <main class="main">
            <div class="main-wrapper" style="padding: 4%;">
                <div class="intro mb-5">
                    <h1 class="text-center" style="font-weight: 700;">
                        <?php echo isset($student_course_name) ? $student_course_name : "Program Not Found"; ?>
                    </h1>
                    <hr>


                </div>

                <div class="mb-4">
                    <h5>Welcome, <?php echo $_SESSION['student_name']; ?>!</h5>
                </div>


                <?php if (!empty($missing_alerts)): ?>
                    <div class="alert alert-danger" role="alert">

                        <?php
                        $hasIncomplete = false;
                        $hasFailed = false;

                        foreach ($missing_alerts as $alert) {
                            if ($alert['remarks'] == 'Incomplete') {
                                $hasIncomplete = true;
                            } elseif ($alert['remarks'] == 'Failed') {
                                $hasFailed = true;
                            }
                        }
                        ?>

                        <h4 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= ($hasIncomplete && $hasFailed) ? "Subjects with Issues" : ($hasIncomplete ? "Subjects with Incomplete Requirements" : "Subjects with Failing Grades"); ?>
                        </h4>
                        <ul>
                            <?php foreach ($missing_alerts as $alert): ?>
                                <li>
                                    <strong>Year Level:</strong> <?= $alert['year_level']; ?> <br>
                                    <strong>Program:</strong>
                                    <?= isset($course_mapping[$alert['course_name']]) ? $course_mapping[$alert['course_name']] : $alert['course_name']; ?> <br>
                                    <strong>Course Code:</strong> <?= $alert['s_course_code']; ?> <br>
                                    <strong>Subject:</strong> <?= $alert['s_descriptive_title']; ?> <br>
                                    <strong>Semester:</strong> <?= $alert['semester']; ?> <br>
                                    <strong>School Year:</strong> <?= $alert['school_year']; ?> <br>
                                    <strong>Teacher:</strong> <?= $alert['teacher_name']; ?> <br>

                                    <?php if ($alert['remarks'] == 'Incomplete'): ?>
                                        <strong class="text-danger">Missing Requirements:</strong> <?= $alert['missing_requirement']; ?>
                                    <?php elseif ($alert['remarks'] == 'Failed'): ?>
                                        <strong class="text-warning">Failed Message:</strong> You have failed this subject. <?= $alert['missing_requirement']; ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>

                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>