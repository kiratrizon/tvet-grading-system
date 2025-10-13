<?php
session_start();
require_once '../config/conn.php';

$student_id = $_SESSION['student_id'];

$query = "SELECT 
            sg.course_code, 
            sg.descriptive_title, 
            sg.semester, 
            sg.year_level, 
            sg.school_year, 
            sg.final_rating, 
            sg.remarks, 
            t.t_name AS teacher_name
          FROM student_grades sg
          JOIN teacher_subjects ts ON sg.subject_id = ts.subject_id
          JOIN teachers t ON ts.teacher_id = t.t_id
          WHERE sg.student_id = ?
          ORDER BY sg.school_year DESC, sg.year_level DESC, sg.semester DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$last_semester = "";
$last_year_level = "";
$last_school_year = "";
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
    <title>My Subjects</title>
</head>

<body>
    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <main class="main">

            <div class="main-wrapper" style="padding: 4%;">
                <h2>Welcome, <?= $_SESSION['student_name']; ?>!</h2>
                <p>Below are your enrolled subjects and corresponding grades:</p>

                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php

                        if ($last_semester !== $row['semester'] || $last_year_level !== $row['year_level'] || $last_school_year !== $row['school_year']) {
                            if ($last_semester !== "") {
                                echo "</tbody></table>";
                            }

                            // Display semester, year level, and school year above the table header
                            echo "<h4 class='text-center mt-4 text-primary'>
                            {$row['semester']} | Year {$row['year_level']} | SY: {$row['school_year']}
                          </h4>";

                            // Start new table
                            echo "<table class='table table-bordered table-striped'>
                            <thead class='table-dark'>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Descriptive Title</th>
                                    
                                    <th>Remarks</th>
                                    <th>Assigned Teacher</th>
                                </tr>
                            </thead>
                            <tbody>";

                            // Update tracking variables
                            $last_semester = $row['semester'];
                            $last_year_level = $row['year_level'];
                            $last_school_year = $row['school_year'];
                        }
                        ?>
                        <tr>
                            <td><?= $row['course_code']; ?></td>
                            <td><?= $row['descriptive_title']; ?></td>

                            <td><?= $row['remarks']; ?></td>
                            <td><?= $row['teacher_name']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-danger">No subjects found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../public/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>