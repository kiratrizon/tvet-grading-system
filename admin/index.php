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
    <title>Admin Dashboard</title>
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
                <div class="clock-container mb-4">
                    <div id="date"></div>
                    <div id="time"></div>
                </div>
                <div>
                    <h3 style="font-weight: 900; line-height:1; letter-spacing:-1px; text-transform:uppercase;font-size:20px">Filter by School Year</h3>
                    <div class="d-flex flex-row gap-4 align-items-end justify-content-center shadow card mb-3 p-4" style="border:1px solid rgba(0,0,0,0.1)">
                        <!-- Year Filter -->
                        <div class="form-group">
                            <label for="startYear">Start Year:</label>
                            <input type="number" id="startYear" placeholder="e.g., 2020" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="endYear">End Year:</label>
                            <input type="number" id="endYear" placeholder="e.g., 2021" class="form-control" required>
                        </div>
                        <div>
                            <button onclick="updateGraph()" class="btn btn-primary">
                                <i class="fas fa-filter" style="font-size: 12px;"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-row gap-4 w-100">
                    <div class="card p-4 d-flex align-items-center justify-content-center shadow" style="width: 40%;">
                        <!-- Data Visualization Charts -->
                        <span style="font-weight: 600;">Grade Performance by Course</span>
                        <canvas id="gradeChart" class="mt-3"></canvas>
                    </div>
                    <div class="card  p-4 d-flex align-items-center justify-content-start shadow" style="width: 60%;">
                        <span style="font-weight: 600;">Students by Course and Year Level</span>
                        <!-- Data Visualization Charts -->
                        <canvas id="courseChart" class="mt-3"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../public/js/loading.js"></script>
    <script src="../public/js/clock.js"></script>
    <script>
        function updateGraph() {
            const startYear = document.getElementById('startYear').value;
            const endYear = document.getElementById('endYear').value;
            window.location.href = `?start_year=${startYear}&end_year=${endYear}`;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const startYear = urlParams.get('start_year') || 2023;
        const endYear = urlParams.get('end_year') || 2024;

        fetch(`charts_data.php?start_year=${startYear}&end_year=${endYear}`)
            .then(response => response.json())
            .then(data => {
                console.log("Fetched Data:", data);

                if (!data.courses || data.courses.length === 0) {
                    console.warn("No data received for courses.");
                    return;
                }

                const courseFullNames = data.courseNames || {};


                // Course Performance Chart (Stacked Bar)
                new Chart(document.getElementById("gradeChart"), {
                    type: "doughnut",
                    data: {
                        labels: data.courses.map(c => courseFullNames[c.course_code] || c.course_code),
                        datasets: [{
                                label: "Passed",
                                data: data.courses.map(c => c.passed),
                                backgroundColor: data.courses.map(c => c.color)
                            },
                            {
                                label: "Failed",
                                data: data.courses.map(c => c.failed),
                                backgroundColor: "#E74C3C" // Red
                            },
                            {
                                label: "Incomplete",
                                data: data.courses.map(c => c.incomplete),
                                backgroundColor: "#F1C40F" // Yellow
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(tooltipItem) {
                                        const course = data.courses[tooltipItem[0].dataIndex];
                                        return `${courseFullNames[course.course_code] || course.course_code}\nYear: ${course.year_level}`;
                                    },
                                    label: function(tooltipItem) {
                                        const datasetIndex = tooltipItem.datasetIndex;
                                        const course = data.courses[tooltipItem.dataIndex];

                                        if (datasetIndex === 0) {
                                            return `Passed: ${course.passed}`;
                                        } else if (datasetIndex === 1) {
                                            return `Failed: ${course.failed}`;
                                        } else if (datasetIndex === 2) {
                                            return `Incomplete: ${course.incomplete}`;
                                        }
                                        return "";
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    }
                });



                // Course Count Chart (Horizontal Bar)
                new Chart(document.getElementById("courseChart"), {
                    type: "bar",
                    data: {
                        labels: data.courses.map(c => courseFullNames[c.course_code] || c.course_code),
                        datasets: [{
                            label: "Students Enrolled",
                            data: data.courses.map(c => c.student_count),
                            backgroundColor: data.courses.map(c => c.color)
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(tooltipItem) {
                                        const course = data.courses[tooltipItem[0].dataIndex];
                                        return `${courseFullNames[course.course_code] || course.course_code}\nYear: ${course.year_level}`;
                                    },
                                    label: function(tooltipItem) {
                                        const course = data.courses[tooltipItem.dataIndex];
                                        return `Total: ${course.student_count} students`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Number of Students"
                                }
                            },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    font: {
                                        size: 11
                                    }
                                }
                            },

                        }
                    }
                });

            })
            .catch(error => console.error("Error fetching data:", error));
    </script>




</body>

</html>