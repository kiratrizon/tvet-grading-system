<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == '' || $_SESSION['usertype'] != 's') {
    header('location: ../index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
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
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <title>Student Evaluation</title>
</head>
<body>
<?php include('./theme/header.php'); ?>
<div class="main-container">
    <?php include('./theme/sidebar.php'); ?>
    <main class="main">
        <div class="main-wrapper" style="padding: 2rem; max-width: 900px;">
            <h3 class="mb-3">Instructor/Course Evaluation</h3>
            <div class="alert alert-info">Your responses are confidential. Please answer honestly.</div>

            <form id="evalForm" class="card p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Instructor (optional)</label>
                        <input type="text" name="instructor" class="form-control" placeholder="e.g., Prof. Santos">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year Level</label>
                        <input type="text" name="year_level" class="form-control" placeholder="e.g., First Year" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <input type="text" name="semester" class="form-control" placeholder="e.g., First Semester" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">School Year</label>
                        <input type="text" name="school_year" class="form-control" placeholder="e.g., 2024-2025" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <input type="text" name="program" class="form-control" placeholder="e.g., BSIT" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Subject (optional)</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g., IT 101">
                    </div>
                </div>

                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Clarity of instruction</label>
                        <select name="clarity" class="form-select" required>
                            <option value="">Select</option>
                            <option>5 - Excellent</option>
                            <option>4 - Very Good</option>
                            <option>3 - Good</option>
                            <option>2 - Fair</option>
                            <option>1 - Poor</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Helpfulness and support</label>
                        <select name="helpfulness" class="form-select" required>
                            <option value="">Select</option>
                            <option>5 - Excellent</option>
                            <option>4 - Very Good</option>
                            <option>3 - Good</option>
                            <option>2 - Fair</option>
                            <option>1 - Poor</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course organization</label>
                        <select name="organization" class="form-select" required>
                            <option value="">Select</option>
                            <option>5 - Excellent</option>
                            <option>4 - Very Good</option>
                            <option>3 - Good</option>
                            <option>2 - Fair</option>
                            <option>1 - Poor</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assessment fairness</label>
                        <select name="fairness" class="form-select" required>
                            <option value="">Select</option>
                            <option>5 - Excellent</option>
                            <option>4 - Very Good</option>
                            <option>3 - Good</option>
                            <option>2 - Fair</option>
                            <option>1 - Poor</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comments/Suggestions</label>
                        <textarea name="comments" class="form-control" rows="4" placeholder="Optional"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fa-regular fa-paper-plane"></i> Submit</button>
                    <span id="submitStatus" class="text-muted"></span>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    $(function(){
        $('#evalForm').on('submit', function(e){
            e.preventDefault();
            const data = $(this).serialize();
            $('#submitStatus').text('Submitting...');
            $.post('submit_evaluation.php', data, function(resp){
                if (resp && resp.success) {
                    $('#submitStatus').text('Thank you! Your evaluation has been recorded.');
                    $('#evalForm')[0].reset();
                } else {
                    $('#submitStatus').text(resp.message || 'Submission failed.');
                }
            }, 'json').fail(function(){
                $('#submitStatus').text('Submission failed.');
            });
        });
    });
</script>
</body>
</html>


