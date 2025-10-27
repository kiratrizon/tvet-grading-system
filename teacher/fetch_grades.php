<?php

session_start();
require '../config/conn.php';
require '../config/myTools.php';

// sample data
/*
Array
(
    [criterion_id] => 66
    [period] => 1
)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $criterionId  = $_POST['criterion_id'] ?? null;
    $period       = $_POST['period'] ?? null;
    $teacherSubject = $_POST['teacher_subject'] ?? null;
    if (!$criterionId || !$period || !$teacherSubject) {
        // return with status code 400
        http_response_code(400);
        exit;
    }
    $percentageOfCriterion = $conn->query("SELECT percentage FROM grading_criteria WHERE id = '$criterionId' and deleted = '0'")->fetch_assoc();
    if (!$percentageOfCriterion) {
        // return with status code 404
        http_response_code(404);
        exit;
    }
    $percentageOfCriterion = (float)$percentageOfCriterion['percentage'];
    // fetch the id from criteria_note_records using criterionId; this is like total activities
    $cnr = $conn->query("SELECT * FROM criteria_note_records WHERE grading_criterion_id = '$criterionId' and period = '$period';")->fetch_all(MYSQLI_ASSOC);
    if (!$cnr) {
        // return with status code 404
        http_response_code(404);
        exit;
    }

    // total enrollees in the teacher_subject_enrollees
    $tempRowTable = $conn->query("SELECT tse.id as enrollee_id, su.name as student_name FROM teacher_subject_enrollees tse join student_users su on tse.student_id = su.id WHERE tse.teacher_subject_id = '$teacherSubject' order by su.name ASC;")->fetch_all(MYSQLI_ASSOC);


    $releasedQuery = $conn->query("SELECT * from released_grades where period = '$period' and teacher_subject_id = '$teacherSubject'");

    $releasedData = $releasedQuery->fetch_assoc() ?? [];

    // make enrollee_id as key and student_name as value
    $rowTable = [];
    foreach ($tempRowTable as $val) {
        $enrolleeId = $val['enrollee_id'];
        unset($val['enrollee_id']);
        $scores = [];
        foreach ($cnr as $index => $cnrVal) {
            $cnrId = $cnrVal['id'];
            $scores[$cnrId] = [];
            // fetch the score for this enrollee and cnrId
            $scoreRow = $conn->query("SELECT score, id as grade_id FROM criteria_grades WHERE enrollee_id = '$enrolleeId' and criteria_note_record_id = '$cnrId';")->fetch_assoc();
            if (isset($scoreRow) && !empty($scoreRow)) {
                $scores[$cnrId]['raw'] = $scoreRow['score'] ?? null;
                $scores[$cnrId]['grade_id'] = $scoreRow['grade_id'];
                if (isset($scores[$cnrId]['raw'])) {
                    $value = ($scores[$cnrId]['raw'] / $cnrVal['total_item']) * 100;

                    // Remove trailing .00 if it's an integer
                    $scores[$cnrId]['percentage'] = (fmod($value, 1) === 0.0
                        ? (int) $value
                        : number_format($value, 2)) . '%';
                }
            } else {
                $scores[$cnrId]['raw'] = null;
                $scores[$cnrId]['percentage'] = null;
                $scores[$cnrId]['grade_id'] = "nograde_$index";
            }
        }
        $val['scores'] = $scores;
        $rowTable[$enrolleeId] = $val;
    }

    if (!$rowTable) {
        // return with status code 404
        http_response_code(404);
        exit;
    }
    // myTools::display(($rowTable));
    // exit;
?>
    <!-- style -->
    <style>
        /* Hide arrows for number inputs in all browsers */

        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
    <!-- Scrollable Table with Sticky Toggle Button -->
    <div style="position: relative; overflow-x: auto; max-width: 100%;">

        <!-- Sticky Centered Button -->
        <div style="position: sticky; top: 0; z-index: 5; background: #fff; text-align: start; padding: 6px 0; border-bottom: 1px solid #dee2e6;">
            <button id="toggleView" class="btn btn-sm btn-outline-primary" data-raw="true">Percentage</button>
        </div>

        <!-- The Table -->
        <table id="gradesTable" class="table table-bordered align-middle text-center" style="width: max-content; border-collapse: collapse; margin: 0;">
            <thead class="table-light">
                <tr>
                    <th style="white-space: nowrap; position: sticky; left: 0; background: #fff; z-index: 2;">Name</th>
                    <?php foreach ($cnr as $key => $val) { ?>
                        <th style="white-space: nowrap;">
                            <?= htmlspecialchars($val['note']) ?>
                            <?= $val['total_item'] == 1 ? "" : "/" . $val['total_item'] ?>
                        </th>
                    <?php } ?>
                    <th style="white-space: nowrap;">
                        Total/Weighted Grade
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rowTable as $enrolleeId => $val) {
                    $totalScore = 0;
                    $totalItems = 0;
                ?>
                    <tr>
                        <td style="white-space: nowrap; position: sticky; left: 0; background: #fff; z-index: 1;">
                            <?= htmlspecialchars($val['student_name']) ?>
                        </td>
                        <?php foreach ($cnr as $key => $cnrVal) {
                            $cnrId = $cnrVal['id'];
                            $scoreData = $val['scores'][$cnrId] ?? [];
                            // accumulate total score
                            if (isset($scoreData['raw']) && is_numeric($scoreData['raw'])) {
                                $totalScore += $scoreData['raw'];
                            }
                            $totalItems += $cnrVal['total_item'];
                        ?>
                            <td class="text-danger" style="white-space: nowrap;">
                                <!-- Raw Score Form (visible by default) -->
                                <div class="grade-form-wrapper grade-raw" style="display: inline-block;">
                                    <form class="grade-form-raw d-inline-block" style="margin:0;">
                                        <input type="hidden" name="enrollee_id" value="<?= htmlspecialchars($enrolleeId) ?>">
                                        <input type="hidden" name="cnr_id" value="<?= htmlspecialchars($cnrId) ?>">
                                        <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">

                                        <input type="number"
                                            name="raw"
                                            class="form-control form-control-sm text-center grade-input raw-<?= $key ?>_<?= $cnrId ?>_<?= $enrolleeId ?>"
                                            value="<?= htmlspecialchars($scoreData['raw'] ?? '') ?>"
                                            style="width: 70px;"
                                            <?= !empty($releasedData) ? 'readonly' : '' ?>
                                            data-released="<?= !empty($releasedData) ? '1' : '0' ?>">
                                    </form>
                                </div>

                                <!-- Percentage Form (hidden by default) -->
                                <div class="grade-form-wrapper grade-percentage" style="display: none;">
                                    <form class="grade-form-percentage d-inline-block" style="margin:0;">
                                        <input type="hidden" name="enrollee_id" value="<?= htmlspecialchars($enrolleeId) ?>">
                                        <input type="hidden" name="cnr_id" value="<?= htmlspecialchars($cnrId) ?>">
                                        <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">

                                        <input type="number"
                                            name="percentage"
                                            class="form-control form-control-sm text-center grade-input percentage-<?= $key ?>_<?= $cnrId ?>_<?= $enrolleeId ?>"
                                            value="<?= isset($scoreData['percentage']) ? floatval($scoreData['percentage']) : '' ?>"
                                            style="width: 70px;"
                                            <?= !empty($releasedData) ? 'readonly' : '' ?>
                                            data-released="<?= !empty($releasedData) ? '1' : '0' ?>">
                                    </form>
                                    %
                                </div>
                            </td>

                        <?php }
                        // calculate weighted score
                        $percentageTotalScore = ($totalItems > 0) ? ($totalScore / $totalItems) * 100 : 0;
                        // how many cnr activities
                        $weightedScore = $percentageTotalScore * ($percentageOfCriterion / 100);
                        ?>
                        <!-- another td for total score but not in form -->
                        <td style="white-space: nowrap;" class="total-<?= $key ?>_<?= $cnrId ?>_<?= $enrolleeId ?>">
                            <?= $totalItems == 1
                                ? "$totalScore x $percentageOfCriterion% = "
                                : "($totalScore / $totalItems) x 100 x $percentageOfCriterion% = "
                            ?>


                            <?= number_format($weightedScore, 2) ?>%
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

<?php } ?>