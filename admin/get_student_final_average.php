<?php


session_start();

require_once "../config/conn.php";
require_once "../config/myTools.php";

// $_POST =
/*
Array
(
    [id] => 1
    [subjects] => Array
        (
            [0] => 35
            [1] => 41
            [2] => 42
            [3] => 47
        )

    [program] => DBOT
    [semester] => First Semester
    [school_year] => 2024-2025
)
*/

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $response = [];
    $id = $_POST['id'] ?? null;
    $subjects = $_POST['subjects'] ?? [];

    $errorResponse = "<p text-danger>No Data.</p>";
    if (!$id || !$subjects) {
        echo $errorResponse;
        exit;
    }

    $subjectsImploded = implode(",", $subjects);

    $studentSubjectsQuery = $conn->query("SELECT tse.id, tse.teacher_subject_id from teacher_subject_enrollees tse where tse.teacher_subject_id in ($subjectsImploded) and tse.student_id = '$id'")->fetch_all(MYSQLI_ASSOC);

    // map and return id
    if (!$studentSubjectsQuery) {
        echo $errorResponse;
        exit;
    }
    $studentSubjects = [];

    foreach ($studentSubjectsQuery as $val) {
        $studentSubjects[$val['id']] = $val['teacher_subject_id'];
    }

    $periods = myTools::periodList(compact('conn'));

    foreach ($studentSubjects as $id => $teacher_subject_id) {
        // myTools::display($studentSubjects);
        // exit;
        $subject = myTools::getSubjectByTeacherSubjectId(compact('conn', 'teacher_subject_id'));
        $criteria = myTools::getGradingCriteriaByTeacherSubjectID(compact('conn', 'teacher_subject_id'));
        $response[$teacher_subject_id] = $subject;
        $totalByPeriod = 0;
        foreach ($periods as $periodId => $periodVal) {
            $releasedQuery = $conn->query("SELECT * from released_grades where period = '$periodId' and teacher_subject_id = '$teacher_subject_id'");
            $releasedData = $releasedQuery->fetch_assoc() ?? [];
            $periodWeight = $periodVal['weight'];
            if (!!$releasedData) {
                // calculate
                $totalPercent = 0;
                foreach ($criteria as $criteriaKey => $criteriaVal) {
                    $criterionId = $criteriaVal['id'];
                    $criterionPercentage = $criteriaVal['percentage'];
                    $totalScore = myTools::getEnrolleeAllGradesByCriteriaAndPeriod([
                        'conn' => $conn,
                        'enrollee_id' => $id,
                        'period' => $periodId,
                        'criteria_id' => $criterionId
                    ]);
                    $totalItems = $totalItems = myTools::getTotalItemByCriteriaAndPeriod([
                        'conn' => $conn,
                        'period' => $periodId,
                        'criteria_id' => $criterionId
                    ]);

                    $totalWeightedCriteria = ($totalItems > 0) ? (($totalScore / $totalItems)) * ($criterionPercentage) : 0;
                    $totalFormatted = number_format($totalWeightedCriteria, 2);
                    if ($totalFormatted) {
                        $totalPercent += $totalFormatted;
                    }
                }
                $totalByPeriod += $totalPercent * ($periodWeight / 100);
            }
            $finalRating = myTools::convertToCollegeGrade($totalByPeriod);
            $remark = myTools::gradeRemark($finalRating);
            $response[$teacher_subject_id]['final_rating'] = $finalRating;
            $response[$teacher_subject_id]['rating_by_units'] = $finalRating * $subject['units'];
            $response[$teacher_subject_id]['remark'] = $remark;
        }
    }

    $totalUnits = 0;
    $totalRatingByUnits = 0;
    $data = [
        'response' => []
    ];
    foreach ($response as $key => $r) {
        $ratingByUnits = $r['rating_by_units'] ?? null;
        if (!isset($ratingByUnits)) {
            continue;
        }
        $totalRatingByUnits += $ratingByUnits;
        $totalUnits += $r['units'] ?? 0;
        $data['response'][$key] = $r;
    }
    $data['average'] = $totalRatingByUnits / $totalUnits; ?>
    <thead class="table-light">
        <tr>
            <th>Course Code</th>
            <th>Description</th>
            <th>Units</th>
            <th>Remarks</th>
            <th>Final Rating</th>
        </tr>
    </thead>
    <tbody>
        <!-- dynamically filled via JS -->

        <?php foreach ($data['response'] as $key => $resp) {
        ?>

            </tfoot>
            <tr>
                <td><?= $resp['course_code'] ?></td>
                <td><?= $resp['description'] ?></td>
                <td><?= $resp['units'] ?></td>
                <td><?= $resp['remark'] ?></td>
                <td><?= $resp['final_rating'] ?></td>
            </tr>

        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end fw-bold">Average Final Rating</td>
            <td><?= $data['average'] ?></td>
        </tr>
    <?php }
