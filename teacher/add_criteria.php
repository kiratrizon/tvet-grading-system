<?php

session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 't') {
    header("location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$ts_id = $_GET['ts_id'] ?? '';

if (empty($ts_id)) {
    $_SESSION['error'] = "Invalid subject selection.";
    header("location: mysubjects.php");
    exit;
}

$teacherSubject = $conn->query("SELECT ts.*, s.s_course_code as course_code, s.s_descriptive_title as description, s.s_units as units FROM teacher_subjects ts join subjects s on ts.subject_id = s.s_id WHERE ts.id = '$ts_id' AND ts.teacher_id = '$teacher_id'")->fetch_assoc();
if (empty($teacherSubject)) {
    $_SESSION['error'] = "Subject not found or you do not have permission to modify it.";
    header("location: mysubjects.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $criteria_names = $_POST['criteria_name'] ?? [];
    $criteria_percents = $_POST['criteria_percent'] ?? [];
    $criteria_ids = $_POST['criteria_id'] ?? [];

    if (count($criteria_names) !== count($criteria_percents) || empty($criteria_names)) {
        $_SESSION['error'] = "Invalid criteria data submitted.";
        header("location: add_criteria.php?ts_id=$ts_id");
        exit;
    }

    // Check if total percentage equals 100
    $total_percent = array_sum($criteria_percents);
    if ($total_percent != 100) {
        $_SESSION['error'] = "The total percentage of all criteria must equal 100%. Currently, it equals $total_percent%. Please adjust the values accordingly.";
        header("location: add_criteria.php?ts_id=$ts_id");
        exit;
    }

    // get all ids to determine which to delete
    $existingCriteriaIds = $conn->query("SELECT id FROM grading_criteria WHERE teacher_subject_id = '$ts_id'")->fetch_all(MYSQLI_ASSOC);
    $existingIdsArray = array_map(function($item) {
        return $item['id'];
    }, $existingCriteriaIds);
    // array diff to find which ids to delete
    $idsToDelete = array_diff($existingIdsArray, array_map('intval', $criteria_ids));
    if (!empty($idsToDelete)) {
        $idsToDeleteStr = implode(",", $idsToDelete);
        $conn->query("UPDATE FROM grading_criteria set deleted = 1 WHERE id IN ($idsToDeleteStr) AND teacher_subject_id = '$ts_id'");
    }
    // myTools::display(json_encode($idsToDelete));exit;
    foreach ($criteria_names as $index => $name) {
        $name = trim($name);
        $percent = (float) $criteria_percents[$index];
        $criteria_id = (int) $criteria_ids[$index];

        if (empty($name) || $percent <= 0 || $percent > 100) {
            continue; // Skip invalid entries
        }

        if ($criteria_id > 0) {
            $conn->query("UPDATE grading_criteria SET criteria_name = '$name', percentage = '$percent' WHERE id = '$criteria_id' AND teacher_subject_id = '$ts_id'");
        } else {
            $conn->query("INSERT INTO grading_criteria (teacher_subject_id, criteria_name, percentage) VALUES ('$ts_id', '$name', '$percent')");
        }
    }

    $_SESSION['success'] = "Grading criteria saved successfully.";
    header("location: add_criteria.php?ts_id=$ts_id");
    exit;
}


// get existing criteria
$existingCriteria = $conn->query("SELECT * FROM grading_criteria WHERE teacher_subject_id = '$ts_id' and deleted = 0")->fetch_all(MYSQLI_ASSOC);


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
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <title>Grading Criteria</title>
</head>

<body>

    <?php include('./theme/header.php'); ?>
    <div class="main-container">
        <?php include('./theme/sidebar.php'); ?>
        <div id="loading-overlay">
            <div class="spinner"></div>
            <p class="loading-text">Please wait... Processing your request</p>
        </div>
        <main class="main">
            <div class="main-wrapper" style="padding: 4%;">

                <!-- ✅ SESSION MESSAGES -->
                <div class="message-wrapper">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="text-center alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Warning!</strong> <br> <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <br> <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                </div>

                
                <!-- ✅ GRADING CRITERIA -->
                <div class="my-4">
                    <h3 class="mb-3">Grading Criteria | <?= $teacherSubject['course_code'] . ' - ' . $teacherSubject['description'] ?></h3>
                    <a href="mysubjects.php" class="btn btn-primary" style="border-radius: 50px;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="import d-flex flex-row gap-5 mb-4">
                    <form id="criteriaForm" method="POST">
                        <div id="criteriaList" class="card p-4 shadow-sm" style="max-width: 600px;">
                            <?php if (!empty($existingCriteria)):
                                foreach ($existingCriteria as $criteria):
                            ?>
                                    <div class="criteria-item d-flex align-items-center mb-3">
                                        <!-- Hidden ID field -->
                                        <input type="hidden" name="criteria_id[]" value="<?= (int) $criteria['id'] ?>">

                                        <input type="text" 
                                            name="criteria_name[]" 
                                            class="form-control me-2" 
                                            placeholder="Criteria name (e.g. Quizzes)" 
                                            value="<?= htmlspecialchars($criteria['criteria_name']) ?>" 
                                            required>
                                        
                                        <input type="number" 
                                            name="criteria_percent[]" 
                                            class="form-control me-2" 
                                            placeholder="%" 
                                            min="1" 
                                            max="100" 
                                            value="<?= htmlspecialchars($criteria['percentage']) ?>" 
                                            required>

                                        <button type="button" class="btn btn-danger remove-criteria" value="<?= (int) $criteria['id'] ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <!-- Default empty row if no existing criteria -->
                                <div class="criteria-item d-flex align-items-center mb-3">
                                    <input type="hidden" name="criteria_id[]" value="0">
                                    <input type="text" name="criteria_name[]" class="form-control me-2" placeholder="Criteria name (e.g. Quizzes)" required>
                                    <input type="number" name="criteria_percent[]" class="form-control me-2" placeholder="%" min="1" max="100" required>
                                    <button type="button" class="btn btn-danger remove-criteria"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Add & Save Buttons -->
                        <div class="mt-3">
                            <button type="button" id="addCriteria" class="btn btn-primary">
                                <i class="fa-regular fa-square-plus"></i> Add Criteria
                            </button>
                            <button type="submit" class="btn btn-success">Save Criteria</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

    </div>

    <script>
        $(document).ready(function() {
            // Add new criteria row
            $("#addCriteria").on("click", function () {
                const newItem = `
                    <div class="criteria-item d-flex align-items-center mb-3">
                        <input type="hidden" name="criteria_id[]" value="0">
                        <input type="text" name="criteria_name[]" class="form-control me-2" placeholder="Criteria name (e.g. Quizzes)" required>
                        <input type="number" name="criteria_percent[]" class="form-control me-2" placeholder="%" min="1" max="100" required>
                        <button type="button" class="btn btn-danger remove-criteria"><i class="fa-solid fa-trash"></i></button>
                    </div>`;
                $("#criteriaList").append(newItem);
            });

            // Remove a criteria row
            $(document).on('click', '.remove-criteria', function() {
                if ($(this).val() != 0){
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This criteria might be in use in existing student grades.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).closest('.criteria-item').remove();
                            Swal.fire(
                                'Deleted!',
                                'The criteria has been deleted.',
                                'success'
                            )
                        }
                    })
                } else {
                    $(this).closest('.criteria-item').remove();
                }
            });

            // Optional: validate total = 100% before submit
            $('#criteriaForm').on('submit', function(e) {
                let total = 0;
                $('input[name="criteria_percent[]"]').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });

                if (total !== 100) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Total Percentage',
                        text: 'The total percentage of all criteria must equal 100%. Currently, it equals ' + total + '%. Please adjust the values accordingly.'
                    })
                }
            });
        });
    </script>

</body>

</html>