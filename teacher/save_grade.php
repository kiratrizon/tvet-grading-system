<?php
session_start();
require '../config/conn.php';
require '../config/myTools.php';

$teacherId = $_SESSION['teacher_id'];
if (empty($teacherId)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrolleeId = $_POST['enrollee_id'] ?? null;
    $cnrId = $_POST['cnr_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $rawOrPercentage = $_POST[$type] ?? null;
    $period = $_POST['period'] ?? null;
    if (!$enrolleeId || !$cnrId || !$type || !$rawOrPercentage || !$period) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }
    $rawScore = null;
    $percentageScore = null;
    // get the item grading first

    $totalItemQuery = $conn->query("SELECT total_item, grading_criterion_id FROM criteria_note_records where id = '$cnrId' and period = '$period'")->fetch_assoc();

    if (!$totalItemQuery) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid criteria note record ID']);
        exit;
    }
    $total_item = $totalItemQuery['total_item'];
    if ($type == "raw") {
        $rawScore = floatval($rawOrPercentage);
        $percentageScore = ($rawScore / $total_item) * 100;
    } elseif ($type == "percentage") {
        $percentageScore = floatval($rawOrPercentage);
        $rawScore = ($percentageScore / 100) * $total_item;
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid score type']);
        exit;
    }

    // get score id if exists
    $scoreQuery = $conn->query("SELECT id FROM criteria_grades WHERE enrollee_id = '$enrolleeId' AND criteria_note_record_id = '$cnrId'");
    $scoreId = null;
    if ($scoreQuery->num_rows > 0) {
        $scoreData = $scoreQuery->fetch_assoc();
        $scoreId = $scoreData['id'];
    }

    // pregmatch if $scoreId is literal number
    if ($scoreId) {
        $updateScore = $conn->query("UPDATE criteria_grades SET score = '$rawScore' WHERE id = '$scoreId'");
        if (!$updateScore) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update score']);
            exit;
        }
    } else {
        $insertScore = $conn->query("INSERT INTO criteria_grades (enrollee_id, criteria_note_record_id, score) VALUES ('$enrolleeId', '$cnrId', '$rawScore')");
        if (!$insertScore || $conn->affected_rows <= 0) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert score']);
            exit;
        }
        $scoreId = $conn->insert_id;
    }


    $basedCriterionId = $totalItemQuery['grading_criterion_id'];
    $totalItem = myTools::getTotalItemByCriteriaAndPeriod([
        'conn' => $conn,
        'criteria_id' => $basedCriterionId,
        'period' => $period
    ]);
    $totalScore = myTools::getEnrolleeAllGradesByCriteriaAndPeriod([
        'conn' => $conn,
        'criteria_id' => $basedCriterionId,
        'period' => $period,
        'enrollee_id' => $enrolleeId
    ]);

    $adminEmails = [];
    $adminQuery = $conn->query("SELECT a_user_name FROM admin");
    while ($adminRow = $adminQuery->fetch_assoc()) {
        // verify email format
        if (filter_var($adminRow['a_user_name'], FILTER_VALIDATE_EMAIL)) {
            $adminEmails[] = $adminRow['a_user_name'];
        }
    }

    // for mailer purpose

    echo json_encode([
        'score_id' => $scoreId,
        'raw_score' => $rawScore,
        'percentage_score' => $percentageScore,
        'total_score' => $totalScore,
        'total_items' => $totalItem
    ]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
exit;
