<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a') {
    header('location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'list') {
    header('Content-Type: application/json');
    $program = $_POST['program'] ?? '';
    $year = $_POST['year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $sy = $_POST['sy'] ?? '';

    $conditions = [];
    if ($program !== '') $conditions[] = "c.course_code = '" . $conn->real_escape_string($program) . "'";
    if ($year !== '') $conditions[] = "ts.year_level = '" . $conn->real_escape_string($year) . "'";
    if ($semester !== '') $conditions[] = "ts.semester = '" . $conn->real_escape_string($semester) . "'";
    if ($sy !== '') $conditions[] = "ts.school_year = '" . $conn->real_escape_string($sy) . "'";
    $where = count($conditions) ? ('WHERE ' . implode(' AND ', $conditions)) : '';

    $sql = "SELECT ts.id, s.s_course_code as course_code, s.s_descriptive_title as title, ts.section, ts.year_level, ts.semester, ts.school_year
            FROM teacher_subjects ts
            JOIN subjects s ON s.s_id = ts.subject_id
            JOIN courses c ON c.id = ts.course
            $where
            ORDER BY s.s_course_code, ts.section";
    $rows = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['items' => $rows]);
    exit;
}
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
    <title>Subject Grades</title>
</head>
<body>
<?php include('./theme/header.php'); ?>
<div class="main-container">
    <?php include('./theme/sidebar.php'); ?>
    <main class="main">
        <div class="main-wrapper" style="padding: 2rem;">
            <h3 class="mb-3">Subject Grades</h3>

            <div class="card p-3 mb-3">
                <form id="filterSubjects" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Program</label>
                        <select name="program" class="form-select" id="programSelect">
                            <option value="">All Programs</option>
                            <?php
                            $progRes = $conn->query("SELECT course_code, course_name FROM courses ORDER BY course_code");
                            while ($p = $progRes->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($p['course_code']) ?>">
                                    <?= htmlspecialchars($p['course_code']) ?> - <?= htmlspecialchars($p['course_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year Level</label>
                        <input type="text" name="year" class="form-control" placeholder="e.g., First Year">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <input type="text" name="semester" class="form-control" placeholder="e.g., First Semester">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">School Year</label>
                        <input type="text" name="sy" class="form-control" placeholder="e.g., 2024-2025">
                    </div>
                    <div class="col-12 d-flex align-items-end">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Find Subjects</button>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card p-3 h-100">
                        <h6 class="mb-2">Subjects</h6>
                        <div id="subjectsList" class="list-group small" style="max-height: 60vh; overflow:auto;">
                            <div class="text-muted">Use the filters to list subjects.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <select id="periodSelect" class="form-select form-select-sm" style="max-width: 220px;">
                                <option value="Midterm">Midterm</option>
                                <option value="Final">Final</option>
                            </select>
                            <button id="refreshGrades" class="btn btn-sm btn-outline-primary" disabled><i class="fa-solid fa-rotate"></i> Load Grades</button>
                            <button id="printGrades" class="btn btn-sm btn-outline-secondary" disabled onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                        </div>
                        <div id="gradesContainer" style="min-height: 200px;">
                            <div class="text-muted">Select a subject to view grades.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    let selectedSubjectId = null;
    function renderSubjects(items) {
        const list = $('#subjectsList');
        list.empty();
        if (!items || items.length === 0) {
            list.html('<div class="text-muted">No subjects found.</div>');
            return;
        }
        items.forEach(item => {
            const text = `${item.course_code} - ${item.title} (${item.section || 'Sec'})`;
            const btn = $('<button type="button" class="list-group-item list-group-item-action"></button>')
                .text(text)
                .data('id', item.id)
                .on('click', function() {
                    selectedSubjectId = $(this).data('id');
                    $('#refreshGrades, #printGrades').prop('disabled', false);
                    loadGrades();
                });
            list.append(btn);
        });
    }

    function loadGrades() {
        if (!selectedSubjectId) return;
        const period = $('#periodSelect').val();
        $('#gradesContainer').html('<div class="text-muted">Loading...</div>');
        $.post('../teacher/fetch_grades_all.php', { teacher_subject: selectedSubjectId, period: period, criterion_id: 0 }, function(html) {
            $('#gradesContainer').html(html);
        }).fail(function() {
            $('#gradesContainer').html('<div class="text-danger">Failed to load grades.</div>');
        });
    }

    $(function(){
        $('#filterSubjects').on('submit', function(e){
            e.preventDefault();
            const data = $(this).serialize() + '&action=list';
            $('#subjectsList').html('<div class="text-muted">Searching...</div>');
            $.post('', data, function(resp){
                renderSubjects(resp.items || []);
            }).fail(function(){
                $('#subjectsList').html('<div class="text-danger">Failed to list subjects.</div>');
            });
        });
        $('#refreshGrades').on('click', function(){ loadGrades(); });
    });
</script>
</body>
</html>


