<?php
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $schoolYear = $_POST['schoolYear'];

    $stmt = $conn->prepare("
        SELECT id, course_code, descriptive_title, final_rating, remarks 
        FROM student_grades 
        WHERE name = ? AND course = ? AND year_level = ? AND semester = ? AND school_year = ?
    ");
    $stmt->bind_param("sssss", $name, $course, $year, $semester, $schoolYear);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalGrades = 0;
    $numSubjects = 0;
    $output = "";

    while ($row = $result->fetch_assoc()) {
        $finalRating = floatval($row['final_rating']);
        $totalGrades += $finalRating;
        $numSubjects++;

        $output .= "<tr>
                <td>" . htmlspecialchars($row['course_code']) . "</td>
                <td>" . htmlspecialchars($row['descriptive_title']) . "</td>
                <td>" . htmlspecialchars($finalRating) . "</td>
                <td>" . htmlspecialchars($row['remarks']) . "</td>
                <td>
                    <button type='button' class='btn btn-primary update' data-id='" . $row['id'] . "' data-bs-toggle='modal' data-bs-target='#edit_grades'>Edit</button>
                </td>
              </tr>";
    }

    $stmt->close();

    // Compute average
    $averageGrade = $numSubjects > 0 ? number_format($totalGrades / $numSubjects, 2) : "N/A";

    // Ensure there's content
    if ($numSubjects > 0) {
        $output .= "<tr><td colspan='5' class='text-center'><strong>Average Grade: " . htmlspecialchars($averageGrade) . "</strong></td></tr>";
    } else {
        $output .= "<tr><td colspan='5' class='text-center text-danger'><strong>No records found.</strong></td></tr>";
    }

    echo $output;
}
