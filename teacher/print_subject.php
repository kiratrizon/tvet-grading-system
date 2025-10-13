<?php
require_once '../config/conn.php';



$teacher_id = $_POST['teacher_id'] ?? '';
$course = $_POST['course'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$year_level = $_POST['year_level'] ?? '';
$semester = $_POST['semester'] ?? '';
$school_year = $_POST['school_year'] ?? '';
$descriptive_title = $_POST['descriptive_title'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';


$query = $conn->prepare("
    SELECT DISTINCT
        t.t_id, t.t_name, t.t_gender, t.status, t.t_image, 
        c.course_code, c.course_name, sg.name, sg.year_level, 
        sg.semester,
        sg.course_code AS COURSECODE,
        sg.school_year, sg.descriptive_title, 
        sg.final_rating, sg.remarks, sub.s_units
    FROM teachers t
    JOIN student_grades sg ON t.t_id = sg.teacher_id
    LEFT JOIN teacher_subjects ts ON sg.teacher_id = ts.teacher_id AND sg.descriptive_title = ?
    LEFT JOIN subjects sub ON ts.subject_id = sub.s_id
    JOIN courses c ON c.id = sg.course
    WHERE t.t_id = ? 
    AND c.id = ? 
    AND sg.year_level = ? 
    AND sg.semester = ? 
    AND sg.school_year = ? 
    AND sub.s_id = ?
    ORDER BY sg.name ASC
");
$query->bind_param("sssssss", $descriptive_title, $teacher_id, $course_id, $year_level, $semester, $school_year, $subject_id);

$query->execute();
$result = $query->get_result();

$teacher = null;
$students = [];

while ($row = $result->fetch_assoc()) {
    if (!$teacher) {
        $teacher = [
            't_id' => $row['t_id'],
            't_name' => $row['t_name'],
            't_gender' => $row['t_gender'],
            'status' => $row['status'],
            't_image' => $row['t_image'],
            'course_code' => $row['COURSECODE']
        ];
    }
    $students[] = $row;
}


if (!$teacher) {
    echo "<script>alert('No data found for the selected subject. Please check your inputs.'); window.close();</script>";
    exit;
}



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

            <p>YEAR LEVEL: <span style="font-weight: 600;"><?= htmlspecialchars($year_level) ?></span></p>
            <?php
            $full_course = $course;

            $query = $conn->prepare("SELECT course_name FROM courses WHERE course_code = ?");
            $query->bind_param("s", $course);
            $query->execute();
            $result = $query->get_result();
            if ($row = $result->fetch_assoc()) {
                $full_course = $row['course_name'];
            }
            ?>
            <p>COURSE: <span style="font-weight: 600;"><?= htmlspecialchars($full_course) ?></span></p>
            <p>SUBJECT: <span style="font-weight: 600;"><?= htmlspecialchars($teacher['course_code']); ?></span></p>
            <p>DESCRIPTION: <span style="font-weight: 600;"> <?= htmlspecialchars($descriptive_title) ?></span></p>

            <div class="d-flex flex-row justify-content-between">
                <p>INSTRUCTOR: <span style="font-weight: 600;"><?= htmlspecialchars($teacher['t_name']) ?> </span></p>
                <p>SEMESTER: <span style="font-weight: 600;"><?= htmlspecialchars($semester) ?> </span></p>
            </div>



            <hr>
            <h4>Student List</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Final Rating</th>
                        <th>Units</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td style="width: 50px;"><?= $row_count; ?></td>
                                <td style="text-transform:uppercase"><?= htmlspecialchars($student['name']) ?></td>
                                <td style="width: 130px;"><?= htmlspecialchars($student['final_rating']) ?></td>
                                <td style="width: 130px;"><?= htmlspecialchars($student['s_units']) ?></td>
                                <td style="width: 130px;"><?= htmlspecialchars($student['remarks']) ?></td>
                            </tr>
                        <?php
                            $row_count++;
                        endforeach; ?>
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

</body>

</html>