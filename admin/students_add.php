<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../index.php");
    exit;
}

$courses = $conn->query("SELECT * FROM courses ORDER BY course_name ASC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']) ?? '';
    $email = trim($_POST['email']) ?? '';
    $course_id = $_POST['course'] ?? null;

    $_SESSION['old_values'] = [
        'name' => $name,
        'email' => $email,
        'course_id' => $course_id
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
    $getLastInsertedId = ($conn->query("SELECT MAX(id) AS last_id FROM student_users")->fetch_assoc()['last_id'] ?? 0) + 1;
    $defaultPasswordUnhashed = 'student' . $getLastInsertedId;
    $defaultPasswordHashed = password_hash($defaultPasswordUnhashed, PASSWORD_DEFAULT);
    // before inserting, check if email already exists
    $emailCheck = $conn->query("SELECT id from student_users WHERE email = '$email' limit 1");
    if ($emailCheck->num_rows > 0) {
        $_SESSION['error'] = "Email already exists. Please use a different email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO student_users (name, email, password, course) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $defaultPasswordHashed, $course_id);
        if ($stmt->execute()) {
            // Send email with default password
            $emailParams = [
                'to' => $email,
                'name' => $name,
                'subject' => 'Your Student Account Details',
                'body' => "<p>Dear $name,</p>
                        <p>Your student account has been created successfully.</p>
                        <p>Your default password is: <strong>$defaultPasswordUnhashed</strong></p>
                        <p>Please log in and change your password immediately for security reasons.</p>
                        <p>Best regards,<br>Grading System Team</p>"
            ];
            myTools::sendEmail($emailParams);
            $_SESSION['success'] = "Student added successfully. An email has been sent to the student with their login details.";
            unset($_SESSION['old_values']);
        } else {
            $_SESSION['error'] = "An error occurred while adding the student. Please try again.";
        }
    }
    $stmt->close();
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
    <title>Student Grades</title>
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
                        <label for="course" class="form-label">Course</label>
                        <select name="course" id="course" class="form-select" required>
                            <option selected disabled>Select Course</option>
                            <?php foreach ($courses as $course) : ?>
                                <option value="<?php echo htmlspecialchars($course['id']); ?>" <?= (isset($_SESSION['old_values']['course_id']) && $_SESSION['old_values']['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Save Record</button>
                </div>
            </form>
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