<?php
session_start();
require '../config/conn.php';
require '../config/myTools.php';

// sample enrollees data
/*
Array
(
    [0] => Array
        (
            [id] => 1
            [student_id] => 30
            [teacher_subject_id] => 47
            [read_flg] => 1
            [student_name] => Ana Garcia
        )
)

sample grading criteria data
Array
(
    [0] => Array
        (
            [id] => 66
            [teacher_subject_id] => 47
            [criteria_name] => Quizzes
            [percentage] => 20
            [deleted] => 0
        )
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $criterion_id = $_POST['criterion_id'] ?? null;
    $period = $_POST['period'] ?? null;
    $teacherSubject = $_POST['teacher_subject'] ?? null;
    if ($criterion_id != 0 || empty($period) || empty($teacherSubject)) {
        echo '<div class="text-danger text-center py-3">Invalid parameters.</div>';
        exit;
    }

    $enrollees = myTools::getEnrolleesByTeacherSubjectID([
        'conn' => $conn,
        'teacher_subject_id' => $teacherSubject
    ]);

    $gradingCriteria = myTools::getGradingCriteriaByTeacherSubjectID([
        'conn' => $conn,
        'teacher_subject_id' => $teacherSubject
    ]);

    // is released
    $releasedQuery = $conn->query("SELECT * from released_grades where period = '$period' and teacher_subject_id = '$teacherSubject'");

    $releasedData = $releasedQuery->fetch_assoc() ?? [];


    if (empty($enrollees)) {
        echo '<div class="text-danger text-center py-3">No enrollees found.</div>';
        exit;
    }

    if (empty($gradingCriteria)) {
        echo '<div class="text-danger text-center py-3">No grading criteria found.</div>';
        exit;
    }

    // column by name first then criteria
?>
    <div style="position: relative; overflow-x: auto; max-width: 100%;">

        <div style="position: sticky; top: 0; z-index: 5; background: #fff; text-align: start; padding: 6px 0; border-bottom: 1px solid #dee2e6;">
            <button id="breakdownView" class="btn btn-sm btn-outline-primary">Show Breakdown</button>
            <button id="releaseGrades"
                class="btn btn-sm btn-outline-<?= empty($releasedData) ? 'success' : 'danger' ?>"
                value="<?= empty($releasedData) ?>"
                data-teacher="<?= htmlspecialchars($teacherSubject) ?>"
                data-period="<?= htmlspecialchars($period) ?>">
                <?= empty($releasedData) ? 'Release' : 'Revoke' ?>
            </button>

        </div>
        <!-- The Table -->
        <table class="table table-bordered align-middle text-center" style="width: max-content; border-collapse: collapse; margin: 0;" id="gradesTable">
            <thead class="table-light">
                <tr>
                    <th style="white-space: nowrap; position: sticky; left: 0; background: #fff; z-index: 2;">Name</th>
                    <?php foreach ($gradingCriteria as $key => $val) { ?>
                        <th style="white-space: nowrap;">
                            <?= htmlspecialchars($val['criteria_name']) ?> (<?= htmlspecialchars($val['percentage']) ?>%)
                        </th>
                    <?php } ?>
                    <th style="white-space: nowrap;" class="<?= !empty($releasedData) ? 'text-success' : 'text-danger' ?>"><?= !empty($releasedData) ? 'Total Grade' : 'Tentative Grade' ?></th>
                    <?php if (!empty($releasedData)) { ?>
                        <th style="white-space: nowrap;">GPE</th>
                        <!-- remarks -->
                        <th style="white-space: nowrap;">Remarks</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollees as $enrollee) {
                    $enrolleeId = $enrollee['id'];
                    $totalPercentage = 0;
                ?>
                    <tr>
                        <td style="white-space: nowrap; position: sticky; left: 0; background: #fff; z-index: 1;">
                            <?= htmlspecialchars($enrollee['student_name']) ?>
                        </td>
                        <?php foreach ($gradingCriteria as $criterion) {
                            $criteriaId = $criterion['id'];
                            $totalScore = myTools::getEnrolleeAllGradesByCriteriaAndPeriod([
                                'conn' => $conn,
                                'enrollee_id' => $enrolleeId,
                                'criteria_id' => $criteriaId,
                                'period' => $period
                            ]);
                            $totalItems = myTools::getTotalItemByCriteriaAndPeriod([
                                'conn' => $conn,
                                'criteria_id' => $criteriaId,
                                'period' => $period
                            ]);
                            $percentage = $criterion['percentage'];

                            $totalWeightedCriteria = ($totalItems > 0) ? (($totalScore / $totalItems)) * ($percentage) : 0;
                            $totalFormatted = number_format($totalWeightedCriteria, 2);
                            $totalPercentage += $totalFormatted;
                        ?>
                            <td style="white-space: nowrap;">
                                <span style="display: none;" class="for-breakdown">
                                    <?= $totalItems == 1
                                        ? "$totalScore x $percentage% = "
                                        : "($totalScore / $totalItems) x 100 x $percentage% = "
                                    ?>
                                </span>
                                <?= $totalFormatted ?>%
                            </td>


                        <?php } ?>
                        <td style="white-space: nowrap; font-weight: bold;">
                            <?= $totalPercentage ?>%
                        </td>
                        <?php if (!empty($releasedData)) { ?>
                            <td style="white-space: nowrap; font-weight: bold;">
                                <?= myTools::convertToCollegeGrade($totalPercentage) ?>
                            </td>
                            <td style="white-space: nowrap; font-weight: bold;">
                                <?= myTools::gradeRemark(myTools::convertToCollegeGrade($totalPercentage)) ?>
                            </td>
                        <?php } ?>


                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>