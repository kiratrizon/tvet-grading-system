<?php
session_start();
require_once '../config/conn.php';
require_once '../vendor_excel/autoload.php';
require_once '../config/myTools.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a') {
    header('location: ../index.php');
    exit;
}

// Serve template download
if (isset($_GET['action']) && $_GET['action'] === 'template') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $headers = ['student_name', 'student_email(optional)', 'program_code', 'year_level', 'school_year'];
    $sheet->fromArray([$headers], null, 'A1');
    $sheet->fromArray([
        ['John Smith', 'john@example.com', 'DIT', 'First Year', '2024-2025'],
        ['Ana Garcia', '', 'DSOT', 'Second Year', '2024-2025']
    ], null, 'A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="enrollment_template.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'application/octet-stream'];
        if (!in_array($_FILES['file']['type'], $allowed)) {
            throw new Exception('Invalid file type. Please upload an Excel file (.xlsx/.xls).');
        }
        $file = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        if (empty($rows)) throw new Exception('Empty file.');
        // Find first non-empty row to use as headers
        $headerRowIndex = 0;
        for ($i = 0; $i < count($rows); $i++) {
            $nonEmpty = array_filter($rows[$i], function($v){ return trim((string)$v) !== ''; });
            if (!empty($nonEmpty)) { $headerRowIndex = $i; break; }
        }
        // Flexible header parsing
        $rawHeaders = array_map(function($h){ return strtolower(trim((string)$h)); }, $rows[$headerRowIndex]);
        $aliases = [
            'student_name' => ['student_name','name'],
            'student_email' => ['student_email(optional)','student_email','email'],
            'program_code' => ['program_code','program','course','course_code'],
            'year_level' => ['year_level','year'],
            'school_year' => ['school_year','sy','schoolyear']
        ];
        $idx = ['student_name'=>-1,'student_email'=>-1,'program_code'=>-1,'year_level'=>-1,'school_year'=>-1];
        foreach ($idx as $key => $_) {
            foreach ($aliases[$key] as $cand) {
                $pos = array_search($cand, $rawHeaders, true);
                if ($pos !== false) { $idx[$key] = $pos; break; }
            }
        }
        if ($idx['student_name'] < 0 || $idx['program_code'] < 0 || $idx['year_level'] < 0 || $idx['school_year'] < 0) {
            $missing = [];
            foreach (['student_name','program_code','year_level','school_year'] as $req) { if ($idx[$req] < 0) { $missing[] = $req; } }
            throw new Exception('Invalid template headers. Missing: ' . implode(', ', $missing));
        }
        // Slice out header and any pre-header lines
        $rows = array_slice($rows, $headerRowIndex + 1);

        // Build program map: code -> id
        $courseRows = $conn->query("SELECT id, course_code FROM courses")->fetch_all(MYSQLI_ASSOC);
        $codeToId = [];
        foreach ($courseRows as $cr) { $codeToId[strtoupper(trim($cr['course_code']))] = (int)$cr['id']; }

        $added = 0; $enrolled = 0; $skipped = 0;
        foreach ($rows as $r) {
            $studentName = trim((string)($r[$idx['student_name']] ?? ''));
            $studentEmail = $idx['student_email'] >= 0 ? trim((string)($r[$idx['student_email']] ?? '')) : '';
            $programCode = strtoupper(trim((string)($r[$idx['program_code']] ?? '')));
            $yearLevel = trim((string)($r[$idx['year_level']] ?? ''));
            $schoolYear = trim((string)($r[$idx['school_year']] ?? ''));

            if ($studentName === '' || $programCode === '' || $yearLevel === '' || $schoolYear === '') { $skipped++; continue; }
            $courseId = $codeToId[$programCode] ?? null; if (!$courseId) { $skipped++; continue; }

            // Find or create student
            $studentId = null;
            if ($studentEmail !== '') {
                $res = $conn->query("SELECT id FROM student_users WHERE email = '".$conn->real_escape_string($studentEmail)."'");
                if ($res && $res->num_rows) { $studentId = (int)$res->fetch_assoc()['id']; }
            }
            if (!$studentId) {
                $res = $conn->query("SELECT id FROM student_users WHERE name = '".$conn->real_escape_string($studentName)."' AND course = $courseId");
                if ($res && $res->num_rows) { $studentId = (int)$res->fetch_assoc()['id']; }
            }
            if (!$studentId) {
                // Create minimal student account
                $emailToUse = $studentEmail !== '' ? $studentEmail : (strtolower(preg_replace('/[^a-z0-9]+/i','.', $studentName)).'@example.local');
                $reg = myTools::registerStudents([
                    'conn' => $conn,
                    'email' => $emailToUse,
                    'name' => $studentName,
                    'course_id' => $courseId
                ]);
                if (!($reg['status'] ?? false)) { $skipped++; continue; }
                $studentId = (int)$reg['student_id'];
                $added++;
            }

            // Capacity-aware auto-enroll
            $enrollRes = myTools::autoEnrollStudentToProgramYearSY([
                'conn' => $conn,
                'student_id' => $studentId,
                'course_id' => $courseId,
                'year_level' => $yearLevel,
                'school_year' => $schoolYear,
                'capacity' => env('DEFAULT_SECTION_CAPACITY', 45)
            ]);
            $enrolled += $enrollRes['created'];
            if ($enrollRes['created'] === 0) { $skipped++; }
        }
        $message = "Imported: $added new students; Enrolled links created: $enrolled; Skipped rows: $skipped.";
    } catch (Exception $e) {
        $message = 'Error: '.$e->getMessage();
    }
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
    <title>Import Enrollment</title>
</head>
<body>
<?php include('./theme/header.php'); ?>
<div class="main-container">
    <?php include('./theme/sidebar.php'); ?>
    <main class="main">
        <div class="main-wrapper" style="padding: 2rem; max-width: 900px;">
            <h3 class="mb-3">Import Enrollment (Program, Year Level, School Year)</h3>

            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Template</div>
                        <div class="text-muted small">Columns: student_name, student_email(optional), program_code (e.g., DIT), year_level (e.g., First Year), school_year (e.g., 2024-2025)</div>
                    </div>
                    <a class="btn btn-outline-secondary" href="?action=template"><i class="fa-solid fa-download"></i> Download Template</a>
                </div>
            </div>

            <div class="card p-4">
                <form method="post" enctype="multipart/form-data" class="d-flex gap-3 align-items-end">
                    <div class="w-100">
                        <label class="form-label">Upload Excel (.xlsx/.xls)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-file-import"></i> Import</button>
                    </div>
                </form>
            </div>

            <div class="mt-3 text-muted small">
                Notes:
                <ul>
                    <li>If a student doesn’t exist, they’ll be created under the specified program.</li>
                    <li>They will be enrolled into ALL subjects for that Program + Year Level + School Year.</li>
                    <li>Semester is optional; both semesters will be included if present.</li>
                </ul>
            </div>
        </div>
    </main>
</div>
</body>
</html>


