<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {


    $student_id = $_SESSION['student_id'];
    $periodList = myTools::periodList(compact('conn'));
    $school_year = $_POST['school_year'] ?? null;
    $studentSchoolyears = $school_year !== 'all' || $school_year != null ?  compact('school_year') : $conn->query("SELECT ts.school_year FROM teacher_subject_enrollees tse join teacher_subjects ts on tse.teacher_subject_id = ts.id WHERE tse.student_id = '$student_id' group by ts.school_year order by ts.school_year")->fetch_all(MYSQLI_ASSOC);
    $subjects = myTools::getStudentSubjects(compact('conn', 'student_id', 'school_year'));

    // subjects by school_year
    $allSubs = [];
    foreach ($subjects as $subject) {
        $year = $subject['school_year'];
        unset($subject['school_year']);
        if (!isset($allSubs[$year])) {
            $allSubs[$year] = [];
        }
        array_push($allSubs[$year], $subject);
    }
    // myTools::display($allSubs);exit;

?>

    <?php foreach ($allSubs as $schoolyear => $subs) {
        // foreach ($subject )
        $semesters = [];
        foreach ($subs as $sub) {
            if (!isset($semesters[$sub['semester']])) {
                $semesters[$sub['semester']] = [];
            }
            array_push($semesters[$sub['semester']], $sub);
        }
    ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2">
                    <strong>School Year:</strong>
                    <span><?= $schoolyear ?></span>
                </div>

                <?php
                foreach ($semesters as $sem => $subs) { ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <caption class="text-start fw-semibold caption-top"><?= $sem ?></caption>
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Description</th>
                                    <?php foreach ($periodList as $key => $period) { ?>
                                        <th><?= $period['label'] ?></th>
                                    <?php } ?>
                                    <th>Final Rating</th>
                                    <th>Units</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $forGwa = 0;
                                $totalUnits = 0;
                                foreach ($subs as $sub) { ?>
                                    <tr>
                                        <td><?= $sub['s_course_code'] ?></td>
                                        <td><?= $sub['description'] ?></td>
                                        <?php
                                        $teacher_subject_id = $sub['teacher_subject'];
                                        $enrollee_id = $sub['enrollee_id'];
                                        $criteria = myTools::getGradingCriteriaByTeacherSubjectID(compact('teacher_subject_id', 'conn'));
                                        $finalRating = 0;
                                        foreach ($periodList as $period => $periodVal) {
                                            $periodWeight = $periodVal['weight'];
                                            $periodPercentage = 0;
                                            foreach ($criteria as $criterion) {
                                                $criteria_id = $criterion['id'];
                                                $totalGrade = myTools::getEnrolleeAllGradesByCriteriaAndPeriod(compact('conn', 'period', 'enrollee_id', 'criteria_id'));
                                                $totalItems = myTools::getTotalItemByCriteriaAndPeriod(compact('conn', 'criteria_id', 'period'));
                                                $criteriaPercentage = $criterion['percentage'];
                                                $scorePercentage = number_format(($totalGrade / $totalItems) * 100, 2) * ($criteriaPercentage / 100);
                                                $periodPercentage += $scorePercentage;
                                            }
                                            $toCollegeGrade = myTools::convertToCollegeGrade($periodPercentage);
                                            $finalRating += $periodPercentage * ($periodWeight / 100);
                                        ?>
                                            <td><?= $toCollegeGrade ?></td>
                                        <?php }
                                        $finalRatingConvert = myTools::convertToCollegeGrade($finalRating);
                                        $forGwa += $finalRatingConvert * $sub['units'];
                                        $totalUnits += $sub['units'];
                                        ?>
                                        <td><?= $finalRatingConvert ?></td>
                                        <td><?= $sub['units'] ?></td>
                                        <td><?= myTools::gradeRemark($finalRatingConvert) ?></td>
                                    </tr>
                                <?php }
                                ?>
                                <tr>
                                    <td colspan="<?= 4 + count($periodList) ?>" class="text-center bg-info">General Weight Average</td>
                                    <td class="bg-info"><?= $forGwa / $totalUnits ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>


    <?php } ?>
<?php } ?>