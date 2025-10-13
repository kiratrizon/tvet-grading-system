<?php

session_start();
require_once '../config/conn.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 's') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM `student_users` WHERE id = '$student_id'");
$row = $student->fetch_assoc();

$id = $row['id'];
$name = $row['name'];
$mail = $row['email'];
$pass = $row['password'];
$image = $row['s_image'];

// echo $email;
// exit;
// exit;


$imagepath = __DIR__ . "/../public/assets/images/";

if (isset($_POST['updatesettings'])) {
    $id = $_POST['id'];
    $email = trim($_POST['email']);
    $pass = $_POST['pass'];

    // Fetch old student data
    $oldStudentQuery = $conn->query("SELECT email, s_image FROM `student_users` WHERE id = '$id'");

    if (!$oldStudentQuery) {
        die("Error fetching old student data: " . $conn->error);
    }

    $oldStudentRow = $oldStudentQuery->fetch_assoc();
    $oldEmail = trim($oldStudentRow['email']);
    $currentImage = $oldStudentRow['s_image'];

    // Image upload logic
    if (!empty($_FILES['image']['name'])) {
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($imageFileType, ['jpeg', 'png', 'jpg', 'gif'])) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
            header('location: settings.php');
            exit;
        }

        // Unique filename to avoid overwriting
        $uniqueImageName = uniqid('img_', true) . '.' . $imageFileType;
        $targetFile = $imagepath . $uniqueImageName;

        // Check file upload errors
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            die("File upload error: " . $_FILES['image']['error']);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Delete old image if it's not the default one
            if ($currentImage && $currentImage != 'imageholder.jpg') {
                $currentImagePath = $imagepath . $currentImage;
                if (file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }

            // Update student with new image
            $updateStudent = $conn->query("UPDATE `student_users` SET `email`='$email', `password`='$pass', `s_image`='$uniqueImageName' WHERE id = '$id'");

            if (!$updateStudent) {
                die("Error updating student: " . $conn->error);
            }

            // Update session image for instant refresh
            $_SESSION['user_image'] = $uniqueImageName;
        } else {
            die("Failed to upload file. Check permissions or path.");
        }
    } else {
        // Update student without changing image
        $updateStudent = $conn->query("UPDATE `student_users` SET `email`='$email', `password`='$pass' WHERE id = '$id'");

        if (!$updateStudent) {
            die("Error updating student: " . $conn->error);
        }
    }

    $_SESSION['success'] = "Updated Credentials Successfully";
    header('location: settings.php');
    exit;
}

// exit;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <link rel="stylesheet" href="../public/style/settings.css">
    <title>Settings</title>
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
            <div class="main-wrapper">
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

                    <?php if (isset($_SESSION['updated'])): ?>
                        <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <br> <?= $_SESSION['updated'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['updated']); ?>
                    <?php endif; ?>
                </div>
                <div class="text-wrapper">
                    <h1 style="line-height: 1; text-transform:uppercase; font-weight:700; margin-bottom:4px"><?= $name; ?></h1>
                    <p>Manage your details and personal prefrences here.</p>
                    <hr>
                </div>
                <form action="settings.php" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="d-flex flex-row gap-4 align-items-center p-4">
                        <div class="change-profile">
                            <div class="change-image shadow mb-4">
                                <img src="../public/assets/images/<?= !empty($image) ? $image : 'imageholder.jpg' ?>" id="imageholder">
                            </div>

                            <input type="file" name="image" id="image" class="form-control">
                        </div>
                        <div class="d-flex flex-column gap-4 w-100 p-4">
                            <div class="form-group w-100">
                                <label for="name">
                                    <i class="fas fa-user"></i> Name
                                </label>
                                <input type="text" name="name" id="name" class="form-control" value="<?= $name ?>" readonly>
                            </div>
                            <div class="form-group w-100">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="text" name="email" id="email" class="form-control" value="<?= $mail ?>">
                            </div>
                            <div class="form-group w-100">
                                <label for="pass">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" name="pass" id="pass" class="form-control" value="<?= $pass ?>">
                            </div>


                            <div class="mb-t">
                                <button type="submit" class="btn btn-primary" name="updatesettings">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </main>
    </div>
    <script src="../public/js/loading.js"></script>
    <script src="../public/js/settings.js"></script>

</body>

</html>