<?php
session_start();
require_once '../config/conn.php';

// Check user session
if (!isset($_SESSION['user']) || $_SESSION['usertype'] != 't') {
    header("location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';

// SQL Query: Join student_grades and courses
$query = "
    SELECT 
        sg.course, 
        c.course_name, 
        sg.year_level, 
        sg.semester, 
        sg.school_year, 
        COUNT(DISTINCT sg.name) as total_students
    FROM student_grades sg
    LEFT JOIN courses c ON sg.course = c.id
    WHERE sg.teacher_id = '$teacher_id' ";

// Filter by school year if selected
if (!empty($school_year)) {
    $query .= " AND sg.school_year = '$school_year'";
}

$query .= " GROUP BY sg.course, c.course_name, sg.year_level, sg.semester, sg.school_year 
            ORDER BY c.course_name, sg.year_level, sg.semester";

$result = $conn->query($query);

// Prepare data for Chart.js
$labels = [];
$student_counts = [];

while ($row = $result->fetch_assoc()) {
    $course_fullname = !empty($row['course_name']) ? $row['course_name'] : $row['course'];
    $labels[] = "{$course_fullname} - {$row['year_level']} - {$row['semester']}";
    $student_counts[] = $row['total_students'];
}

// Fetch available school years
$school_years_query = "SELECT DISTINCT school_year FROM student_grades ORDER BY school_year DESC";
$school_years_result = $conn->query($school_years_query);
$school_years = [];
while ($row = $school_years_result->fetch_assoc()) {
    $school_years[] = $row['school_year'];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <title>Teacher Dashboard</title>
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
                <div class="clock-container mb-5">
                    <div id="date"></div>
                    <div id="time"></div>
                </div>
                <div class="mb-4">
                    <label for="schoolYearFilter" class="form-label">Filter by School Year</label>
                    <select id="schoolYearFilter" class="form-control w-25">
                        <option value="" selected disabled hidden>All</option>
                        <?php foreach ($school_years as $year): ?>
                            <option value="<?= $year ?>" <?= $school_year == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="card shadow p-4">
                    <canvas id="studentsChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    <script src="../public/js/loading.js"></script>
    <script src="../public/js/clock.js"></script>

    <script>
        document.getElementById("schoolYearFilter").addEventListener("change", function() {
            const selectedYear = this.value;
            window.location.href = `?school_year=${selectedYear}`;
        });

        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('studentsChart').getContext('2d');

            var labels = <?= json_encode($labels) ?>;
            var studentCounts = <?= json_encode($student_counts) ?>;

            var uniqueCourses = [...new Set(labels.map(label => label.split(" - ")[0]))];

            var courseCounts = {};
            labels.forEach((label, index) => {
                var course = label.split(" - ")[0];
                courseCounts[course] = (courseCounts[course] || 0) + studentCounts[index];
            });

            var studentsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: uniqueCourses,
                    datasets: [{
                        label: 'Total Students',
                        data: uniqueCourses.map(course => courseCounts[course]),
                        backgroundColor: 'rgba(45, 2, 116, 0.5)',
                        borderColor: 'rgba(45, 2, 116, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItem) {
                                    let fullLabel = labels[tooltipItem[0].dataIndex];
                                    let parts = fullLabel.split(" - ");
                                    return `Course: ${parts[0]}\nYear: ${parts[1]}\nSem: ${parts[2]}`;
                                },
                                label: function(tooltipItem) {
                                    return `${tooltipItem.raw} students`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Students'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Course'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>