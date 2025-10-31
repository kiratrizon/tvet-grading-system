<?php 
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if ($_SERVER['REQUEST_METHOD'] === "POST"){


    $student_id = $_SESSION['student_id'];
    $periodList = myTools::periodList(compact('conn'));
    $school_year = $_POST['school_year'] ?? null;
    $studentSchoolyears = $school_year !== 'all' || $school_year != null ?  compact('school_year') : $conn->query("SELECT ts.school_year FROM teacher_subject_enrollees tse join teacher_subjects ts on tse.teacher_subject_id = ts.id WHERE tse.student_id = '$student_id' group by ts.school_year order by ts.school_year")->fetch_all(MYSQLI_ASSOC);
    $subjects = myTools::getStudentSubjects(compact('conn','student_id', 'school_year'));

    // myTools::display(($subjects));
    myTools::display(json_encode($studentSchoolyears));exit;
?>

    <?php foreach ($studentSchoolyears as $year) { ?>
        <table class="table-responsive">
            <caption>School Year: <?php echo htmlspecialchars($year['school_year']); ?></caption>
        </table>
    <?php } ?>
<?php }?>