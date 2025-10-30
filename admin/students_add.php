<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../index.php");
    exit;
}

$courses = $conn->query("SELECT id, program_name FROM programs ORDER BY program_name ASC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']) ?? '';
    $email = trim($_POST['email']) ?? '';
    $course_id = $_POST['course'] ?? null;
    $year_level = trim($_POST['year_level'] ?? '');
    $school_year = trim($_POST['school_year'] ?? '');

    $_SESSION['old_values'] = [
        'name' => $name,
        'email' => $email,
        'course_id' => $course_id,
        'year_level' => $year_level,
        'school_year' => $school_year
    ];
    if (empty($name) || empty($email) || empty($course_id)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("location: students_add.php");
        exit;
    }
    $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$isValidEmail) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("location: students_add.php");
        exit;
    }
    $result = myTools::registerStudents([
        'conn' => $conn,
        'email' => $email,
        'name' => $name,
        'course_id' => $course_id
    ]);
    if (!$result['status']) {
        $_SESSION['error'] = $result['message'] ?? 'An error occurred while adding the student.';
    } else {
        $studentId = $result['student_id'] ?? null;
        // auto-enroll if year level and school year are provided
        if (!empty($studentId) && $year_level !== '' && $school_year !== '') {
            $enrollRes = myTools::autoEnrollStudentToProgramYearSY([
                'conn' => $conn,
                'student_id' => $studentId,
                'course_id' => $course_id,
                'year_level' => $year_level,
                'school_year' => $school_year,
                'capacity' => env('DEFAULT_SECTION_CAPACITY', 45)
            ]);
            $_SESSION['success'] = ($result['message'] ?? 'Student added successfully.') . ' Enrolled in '.$enrollRes['created'].' subject(s).';
        } else {
            $_SESSION['success'] = $result['message'] ?? 'Student added successfully.';
        }
        unset($_SESSION['old_values']);
    }
    header("location: students_add.php");
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
    <link rel="stylesheet" href="../public/style/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="../public/style/buttons.dataTables.css">
    <script src="../public/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <title>Add Students</title>
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
            <div class="main-wrapper container py-4">
                <h2 class="text-center fw-bold text-uppercase mb-4">Add Student</h2>

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

                <form id="addStudentForm" class="mx-auto" style="max-width: 700px;" method="POST">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Juan Dela Cruz" required value="<?= $_SESSION['old_values']['name'] ?? '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="e.g. juan@example.com" required value="<?= $_SESSION['old_values']['email'] ?? '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="course" class="form-label">Diploma Program</label>
                            <select name="course" id="course" class="form-select" required>
                                <option selected disabled>Select Program</option>
                                <?php foreach ($courses as $course) : ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>" <?= (isset($_SESSION['old_values']['course_id']) && $_SESSION['old_values']['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($course['program_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="year_level" class="form-label">Year Level</label>
                            <select id="year_level" name="year_level" class="form-select">
                                <option value="">Select (optional)</option>
                                <?php $yl = $_SESSION['old_values']['year_level'] ?? ''; ?>
                                <option value="First Year" <?= $yl==='First Year'?'selected':''; ?>>First Year</option>
                                <option value="Second Year" <?= $yl==='Second Year'?'selected':''; ?>>Second Year</option>
                                <option value="Third Year" <?= $yl==='Third Year'?'selected':''; ?>>Third Year</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="school_year" class="form-label">School Year</label>
                            <input type="text" class="form-control" id="school_year" name="school_year" placeholder="e.g., 2024-2025" value="<?= $_SESSION['old_values']['school_year'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Save Record</button>
                    </div>
                </form>

                <!-- Enroll Existing Student into Program/Year/SY -->
                <div class="card mt-5 p-4" style="max-width: 900px; margin: 0 auto;">
                    <h5 class="mb-3">Enroll Existing Student</h5>
                    <form action="enroll_existing.php" method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="" selected disabled>Select Student</option>
                                <?php
                                $students = $conn->query("SELECT id, name FROM student_users ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
                                foreach ($students as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year Level</label>
                            <select name="year_level" class="form-select" required>
                                <option value="First Year">First Year</option>
                                <option value="Second Year">Second Year</option>
                                <option value="Third Year">Third Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">School Year</label>
                            <input type="text" name="school_year" class="form-control" placeholder="e.g., 2025-2026" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">Enroll to Program Subjects</button>
                        </div>
                    </form>
                    <div class="text-muted small mt-2">Uses the student's current Program; enrolls into all subjects for the selected Year Level and School Year.</div>
                </div>

                <!-- Or Import Enrollment (Program + Year Level + School Year) -->
                <form action="import_enrollment.php" method="post" enctype="multipart/form-data" class="mx-auto mt-5" style="max-width: 700px;">
                    <div class="mb-3">
                        <label for="file" class="form-label">Import Enrollment (Excel)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xls,.xlsx" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>

                <!-- download sample excel template -->
                <div class="text-center mt-3">
                    <a href="import_enrollment.php?action=template" class="btn btn-secondary">Download Enrollment Template</a>
                </div>
            </div>
        </main>

    </div>

    <script src="../public/js/loading.js"></script>
    <!-- Plugin sa Data Table -->
    <script src="../public/js/dataTable/dataTables.min.js"></script>
    <script src="../public/js/dataTable/dataTables.buttons.js"></script>
    <script src="../public/js/dataTable/buttons.dataTables.js"></script>
    <script src="../public/js/dataTable/jszip.min.js"></script>
    <script src="../public/js/dataTable/pdfmake.min.js"></script>
    <script src="../public/js/dataTable/vfs_fonts.js"></script>
    <script src="../public/js/dataTable/buttons.html5.min.js"></script>
    <script src="../public/js/dataTable/buttons.print.min.js"></script>

    <script src="../public/js/admin_edit_grades.js"></script>

    <script>
        $(document).ready(function() {

        });
    </script>
</body>

</html>