<?php
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];

    $stmtStudent = $conn->prepare("SELECT 
    sg.name,
    sg.course,
    sg.year_level,
    sg.semester,
    sg.school_year,
    c.course_code,
    c.course_name
    FROM student_grades AS sg
    JOIN courses AS c ON sg.course = c.id
    WHERE sg.id = ?");
    $stmtStudent->bind_param("i", $studentId);
    $stmtStudent->execute();
    $studentResult = $stmtStudent->get_result();
    $student = $studentResult->fetch_assoc();
    $stmtStudent->close();
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
    <link rel="stylesheet" href="../public/style/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="../public/style/buttons.dataTables.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <title>Print Evaluation Grades</title>
</head>

<body>
    <main style="padding: 2%;">
        <h2>Grade Evaluation</h2>
        <hr>

        <?php if ($student): ?>
            <div class="d-flex flex-row gap-4">
                <p class="col-2"><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
                <?php
                $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_code = ?");
                $stmt->bind_param("s", $student['course_code']);
                $stmt->execute();
                $stmt->bind_result($full_course);
                $stmt->fetch();
                $stmt->close();

                $full_course = $full_course ?: $student['course_name'];
                ?>
                <p class="col-2"><strong>Course:</strong> <?= htmlspecialchars($full_course) ?> </p>
            </div>

            <div class="d-flex flex-row gap-4">
                <p class="col-2"><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level']) ?> </p>
                <p class="col-2"><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?> </p>
                <p class="col-2"><strong>School Year:</strong> <?= htmlspecialchars($student['school_year']) ?> </p>
            </div>
            <hr>

            <?php
            $stmtGrades = $conn->prepare("
                SELECT id, course_code, descriptive_title, final_rating, remarks
                FROM student_grades
                WHERE name = ? AND course = ? AND year_level = ? AND semester = ? AND school_year = ?");
            $stmtGrades->bind_param("sssss", $student['name'], $student['course'], $student['year_level'], $student['semester'], $student['school_year']);
            $stmtGrades->execute();
            $gradesResult = $stmtGrades->get_result();

            // ✅ Initialize variables para sa computation ng average
            $totalGrades = 0;
            $numSubjects = 0;
            ?>

            <table class="display nowrap table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Course Code</th>
                        <th>Descriptive Title</th>
                        <th>Final Rating</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $gradesResult->fetch_assoc()) {
                        // ✅ Compute total grades at bilang ng subjects
                        $finalRating = floatval($row['final_rating']);
                        $totalGrades += $finalRating;
                        $numSubjects++;
                    ?>
                        <tr>
                            <td> <?= htmlspecialchars($row['course_code']) ?></td>
                            <td> <?= htmlspecialchars($row['descriptive_title']) ?> </td>
                            <td> <?= htmlspecialchars($row['final_rating']) ?> </td>
                            <td> <?= htmlspecialchars($row['remarks']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="4" class="text-center fw-bold">
                            <?php
                            // ✅ Compute average grade
                            $averageGrade = $numSubjects > 0 ? number_format($totalGrades / $numSubjects, 2) : "N/A";
                            echo "Average Grade: " . htmlspecialchars($averageGrade);
                            ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <?php $stmtGrades->close(); ?>
        <?php else: ?>
            <p style='color: red;'>⚠ Student info not found. Please check the ID.</p>
        <?php endif; ?>
    </main>
</body>

</html>