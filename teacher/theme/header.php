<?php

$teacher_id = $_SESSION['teacher_id'];
$admin = $conn->query("SELECT * FROM `teachers` WHERE t_id = '$teacher_id'");
$row = $admin->fetch_assoc();

$id = $row['t_id'];
$name = $row['t_name'];
$email = $row['t_user_name'];
$image = $row['t_image'];
$pass = $row['t_password'];

?>

<header>
    <div class="header-container">
        <div class="logo-wrapper d-flex flex-row gap-4 align-items-center">
            <div class="logo">
                <img src="../public/assets/icon/logo.svg">
            </div>
            <h5 class="text-white" style="text-transform: uppercase; line-height:1">Technical & Vocational <br> Education & Training</h5>
        </div>
        <div class="profile-wrapper d-flex flex-row gap-2 align-items-center">
            <div class="profile">
                <a href="settings.php">
                    <img src="../public/assets/images/<?= !empty($image) ? $image : 'imageholder.jpg' ?>" alt="Profile Picture">
                </a>
            </div>
            <div class="profile-name">
                <h6 style="font-size: 12px; line-height: 1; margin-bottom:4px" class="text-white"><?= $name; ?></h6>
                <h6 style="font-size: 10px; line-height: 1; margin:0" class="text-white"><em>Teacher</em></h6>
            </div>
        </div>
    </div>
</header>