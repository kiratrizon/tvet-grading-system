<?php

session_start();
require_once '../config/conn.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$admin = $conn->query("SELECT * FROM `admin` WHERE a_id = '$teacher_id'");
$row = $admin->fetch_assoc();

$id = $row['a_id'];
$name = $row['a_name'];
$email = $row['a_user_name'];
$image = $row['a_image'];
$pass = $row['a_password'];

$imagepath = "../public/assets/images/";

if (isset($_POST['updatesettings'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    // Get correct old email before updating
    $oldEmailQuery = $conn->query("SELECT a_user_name FROM `admin` WHERE a_id = '$id'");
    $oldEmailRow = $oldEmailQuery->fetch_assoc();
    $oldEmail = $oldEmailRow['a_user_name']; // Correct old email now

    if (!empty($_FILES['image']['name'])) {
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($imageFileType, ['jpeg', 'png', 'jpg', 'gif'])) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
            header('location:settings.php');
            exit;
        }

        $uniqueImageName = uniqid('img_', true) . '.' . $imageFileType;
        $targetFile = $imagepath . $uniqueImageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $result = $conn->query("SELECT a_image FROM `admin` WHERE a_id = '$id'");
            $row = $result->fetch_assoc();
            $currentImage = $row['a_image'];

            if ($currentImage && $currentImage != 'imageholder.jpg') {
                $currentImagePath = $imagepath . $currentImage;
                if (file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }

            // ✅ Update admin info with new image
            $conn->query("UPDATE `admin` SET `a_name`='$name', `a_user_name`='$email', `a_password`='$pass', `a_image`='$uniqueImageName' WHERE a_id = '$id'");
        }
    } else {
        // Update admin info without image change
        $conn->query("UPDATE `admin` SET `a_name`='$name', `a_user_name`='$email', `a_password`='$pass' WHERE a_id = '$id'");
    }

    //Update email in `web_users` table if changed
    if ($oldEmail !== $email) {
        $updateWebUser = $conn->query("UPDATE `web_users` SET `email`='$email' WHERE `email`='$oldEmail'");

        if (!$updateWebUser) {
            $_SESSION['error'] = "Error updating web_users: " . $conn->error;
        } else {
            $_SESSION['email'] = $email;
        }
    }

    $_SESSION['success'] = "Updated Credentials Successfully";
    header('location:settings.php');
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../public/fonts/css/all.min.css">
    <link rel="stylesheet" href="../public/style/loading.css">
    <link rel="stylesheet" href="../public/style/settings.css">
    <title>Admin Settings</title>
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
                    <h1 style="line-height: 1; text-transform:uppercase; font-weight:700; margin-bottom:4px"><?= $_SESSION['teacher_name']; ?></h1>
                    <p>Manage your details and personal prefrences here.</p>
                    <hr>
                </div>
                <form action="settings.php" enctype="multipart/form-data" method="post" onsubmit="return validateEmail()">
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
                                <input type="text" name="name" id="name" class="form-control" value="<?= $name ?>">
                            </div>
                            <div class="form-group w-100">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" name="email" id="email" class="form-control" value="<?= $email ?>" required>
                                <small id="emailError" style="color: red; display: none;">Please enter a valid email address.</small>
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
    <script>
        function validateEmail() {
            var emailField = document.getElementById("email");
            var emailError = document.getElementById("emailError");
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailPattern.test(emailField.value)) {
                emailError.style.display = "block";
                return false;
            } else {
                emailError.style.display = "none";
                return true;
            }
        }
    </script>
</body>

</html>