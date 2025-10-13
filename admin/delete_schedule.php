<?php
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = intval($_POST['schedule_id']);

    $deleteQuery = $conn->query("DELETE FROM teacher_subjects WHERE id = $schedule_id");

    if ($deleteQuery) {
        echo json_encode(["status" => "success", "message" => "Schedule deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete schedule!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request!"]);
}
