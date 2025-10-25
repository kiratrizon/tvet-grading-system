<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

$teacherSubject = $_POST['teacher_subject'] ?? '';

$teacher_id = $_SESSION['teacher_id'] ?? '';

if (empty($teacherSubject) || empty($teacher_id)) {
    echo "<script>alert('Invalid parameters. Please go back and try again.'); window.close();</script>";
    exit;
}

$teacherSubjectRow = myTools::getTeacherSubjectByID([
    'conn' => $conn,
    'teacher_subject_id' => $teacherSubject
]);

if (!$teacherSubjectRow) {
    echo "<script>alert('No data found for the selected subject. Please check your inputs.'); window.close();</script>";
    exit;
}

$students = myTools::getStudentsByTeacherSubject(
    [
        'conn' => $conn,
        'teacher_subject_id' => $teacherSubject
    ]
);

$periods = myTools::periodList([
    'conn' => $conn
]);


$admin = $conn->query("SELECT * FROM admin");
$admin_name  = $admin->fetch_assoc()['a_name'];



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
    <title>Print Data</title>
    <style>
        p {
            margin: 0;
        }

        body {
            font-size: 14px;
        }

        @media print {
            @page {
                margin: 10mm;
                /* Removes headers and footers in most browsers */
                size: auto;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class=" p-4">

            <div class="intro text-center mb-5">
                <h4 style="font-weight: 600;">ANDRES SORIANO COLLEGES OF BISLIG</h4>
                <p style="margin-bottom: 14px;">Mangagoy, Bislig City</p>
                <span style="letter-spacing: 10px; font-weight: 700; border-bottom: 2px solid transparent;background: linear-gradient(to right, black 50%, transparent 50%) repeat-x bottom;background-size: 10px 2px;">GRADING SHEET</span>
            </div>

            <p>YEAR LEVEL: <span style="font-weight: 600;"><?= htmlspecialchars($teacherSubjectRow['year_level']) ?></span></p>
            <?php
            $courseId = $teacherSubjectRow['course'];
            $getCourseRow = $conn->query("SELECT * FROM courses WHERE id = $courseId")->fetch_assoc();

            $subjectRow = $conn->query("SELECT * FROM subjects WHERE s_id = " . $teacherSubjectRow['subject_id'])->fetch_assoc();
            $teacher = $conn->query("SELECT * FROM teachers WHERE t_id = " . $teacherSubjectRow['teacher_id'])->fetch_assoc();
            ?>
            <p>COURSE: <span style="font-weight: 600;"><?= htmlspecialchars($getCourseRow['course_name']) ?></span></p>
            <p>SUBJECT: <span style="font-weight: 600;"><?= htmlspecialchars($subjectRow['s_course_code']) ?></span></p>
            <p>DESCRIPTION: <span style="font-weight: 600;"> <?= htmlspecialchars($subjectRow['s_descriptive_title']) ?></span></p>
            <p>UNITS: <span style="font-weight: 600;"><?= htmlspecialchars($subjectRow['s_units']) ?></span></p>

            <div class="d-flex flex-row justify-content-between">
                <p>SEMESTER: <span style="font-weight: 600;"><?= htmlspecialchars($teacherSubjectRow['semester']) ?> </span></p>
            </div>



            <hr>
            <h4>Student List</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Final Rating</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student):
                            $totalByPercent = 0;
                            $totalAdded = 0;
                            foreach ($periods as $key => $period) {
                                $enrolleeReleasedGrades = myTools::getEnrolleesReleasedGrades([
                                    'conn' => $conn,
                                    'enrollee_id' => $student['id'],
                                    'teacher_subject_id' => $teacherSubject,
                                    'period' => $key
                                ]);
                                if (isset($enrolleeReleasedGrades) && is_numeric($enrolleeReleasedGrades)) {
                                    $totalByPercent += ($enrolleeReleasedGrades * ($period['weight'] / 100));
                                    $totalAdded++;
                                }
                            }
                            $finalRating = myTools::convertToCollegeGrade(round($totalByPercent, 2));
                            $remark = myTools::gradeRemark($finalRating);
                        ?>
                            <tr>
                                <td style="width: 50px;"><?= $row_count; ?></td>
                                <td style="text-transform:uppercase"><?= htmlspecialchars($student['student_name']) ?></td>
                                <td style="width: 130px;"><?= htmlspecialchars($finalRating) ?></td>
                                <td style="width: 130px;"><?= htmlspecialchars($remark) ?></td>
                            </tr>
                        <?php
                            $row_count++;
                        endforeach;
                        ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-danger">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="instructor_wrapper d-flex flex-row justify-content-around" style="margin-top: 70px; margin-bottom:40px">
                <div class="admin text-center" style="width: 180px;">
                    <h5 style="margin:0; text-transform:uppercase; border-bottom:1px solid black"><?= $admin_name ?></h5>
                    <p>TVET HEAD</p>
                </div>
                <div class="instructor text-center" style="width: 280px;">
                    <h5 style="margin:0; text-transform:uppercase; border-bottom:1px solid black"><?= htmlspecialchars($teacher['t_name']) ?></h5>
                    <p>INSTRUCTOR</p>
                </div>
            </div>

            <div class="grading_system d-flex flex-column align-items-center gap-4 justify-content-between">
                <span>GRADING SYSTEM:</span>
                <div class="d-flex flex-row w-100">
                    <div class="col-4 d-flex flex-row justify-content-center">
                        <ul>
                            <li>1.0 - 95 - 100%</li>
                            <li>1.1 - 94</li>
                            <li>1.2 - 93</li>
                            <li>1.3 - 92</li>
                            <li>1.4 - 91</li>
                            <li>1.5 - 90</li>
                            <li>1.6 - 89</li>
                            <li>1.7 - 88</li>
                        </ul>
                    </div>
                    <div class="col-4 d-flex flex-row justify-content-center">
                        <ul>
                            <li>1.8 - 87</li>
                            <li>1.9 - 86</li>
                            <li>2.0 - 85</li>
                            <li>2.1 - 84</li>
                            <li>2.2 - 83</li>
                            <li>2.3 - 82</li>
                            <li>2.4 - 81</li>
                            <li>2.5 - 80</li>
                        </ul>
                    </div>
                    <div class="col-4 d-flex flex-row justify-content-center">
                        <ul>
                            <li>2.6 - 79</li>
                            <li>2.7 - 78</li>
                            <li>2.8 - 77</li>
                            <li>2.9 - 76</li>
                            <li>3.0 - 75</li>
                            <li>5.0 - (Failed)</li>
                            <li>Dr. - (Dropped)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>