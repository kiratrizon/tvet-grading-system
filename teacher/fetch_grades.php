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
    if (!$criterionId || !$period) {
        // return with status code 400
        http_response_code(400);
        exit;
    }
    // fetch the id from criteria_note_records using criterionId;
    $cnr = $conn->query("SELECT * FROM criteria_note_records WHERE grading_criterion_id = '$criterionId' and period = '$period';")->fetch_all(MYSQLI_ASSOC);
    if (!$cnr) {
        // return with status code 404
        http_response_code(404);
        exit;
    }
    $rowTable = [];
    $newCnr = [];
    foreach ($cnr as $key => $val) {
        $newCnr[$val['id']] = $val;
        $cnrId = $val['id'];
        // this is the relevant id for table criteria_grades
        $grades = $conn->query("
            SELECT sg.id as sg_id, sg.score as sg_score, sg.enrollee_id as sg_enrollee_id, su.name as student_name FROM criteria_grades sg
            JOIN teacher_subject_enrollees tse on sg.enrollee_id = tse.id join student_users su on tse.student_id = su.id
            WHERE sg.criteria_note_record_id = '$cnrId';
        ")->fetch_all(MYSQLI_ASSOC);

        foreach ($grades as $grade) {
            if (key_exists($grade['sg_enrollee_id'], $rowTable)) {
                continue;
            } else {
                $rowTable[$grade['sg_enrollee_id']] = $grade['student_name'];
            }
        }
        $newCnr[$val['id']]['grades'] = $grades;
    }

    // myTools::display($newCnr);
    // exit;
?>
    <div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <?php foreach ($newCnr as $colId => $val) { ?>
                            <th><?= htmlspecialchars($val['note']) ?> <?= $val['total_item'] == 1 ? "" : "/" . $val['total_item'] ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rowTable as $enrolleeId => $studentName) { ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($studentName) ?>
                            </td>
                            <?php foreach ($newCnr as $colId => $val) {
                                $score = null;
                                foreach ($newCnr[$colId]['grades'] as $grade) {
                                    if ($grade['sg_enrollee_id'] == $enrolleeId) {
                                        $score = $grade['sg_score'];
                                        break;
                                    }
                                }
                            ?>
                                <td><?= $score ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

<?php } ?>