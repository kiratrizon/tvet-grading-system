<?php
session_start();
require_once 'config/conn.php';

$_SESSION['user'] = "";
$_SESSION['usertype'] = "";
$_SESSION['teacher_id'] = "";
$_SESSION['teacher_name'] = "";
$_SESSION['student_id'] = "";
$_SESSION['student_name'] = "";

// Function to check if user is logged in
function is_logged_in()
{
    return isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
}

// Function to set user status to active
function set_user_active()
{
    $_SESSION["active"] = true;
}

// Function to set user status to inactive
function set_user_inactive()
{
    $_SESSION["active"] = false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usermail = trim($_POST['user']);
    $userpassword = trim($_POST['pass']);

    // Check web_users table first
    $getemail = $conn->query("SELECT * FROM web_users WHERE email = '$usermail'");

    if ($getemail && $getemail->num_rows == 1) {
        $usertype = $getemail->fetch_assoc()['usertype'];

        if ($usertype == 'a') {
            // Admin
            $validate = $conn->query("SELECT * FROM admin WHERE a_user_name = '$usermail'");
            if ($validate && $validate->num_rows == 1) {
                $row = $validate->fetch_assoc();
                if (password_verify($userpassword, $row['a_password']) || $userpassword === $row['a_password']) {
                    $_SESSION['teacher_id'] = $row['a_id'];
                    $_SESSION['teacher_name'] = $row['a_name'];
                    $_SESSION['user'] = $usermail;
                    $_SESSION['usertype'] = 'a';
                    header('location: admin/index.php');
                    exit;
                }
            }
        } elseif ($usertype == 't') {
            // Teacher
            $validate = $conn->query("SELECT * FROM teachers WHERE t_user_name = '$usermail'");
            if ($validate && $validate->num_rows == 1) {
                $row = $validate->fetch_assoc();
                if (password_verify($userpassword, $row['t_password']) || $userpassword === $row['t_password']) {
                    $_SESSION['teacher_id'] = $row['t_id'];
                    $_SESSION['teacher_name'] = $row['t_name'];
                    $_SESSION['user'] = $usermail;
                    $_SESSION['usertype'] = 't';
                    $_SESSION["logged_in"] = true;
                    $conn->query("UPDATE teachers SET status = 1 WHERE t_user_name = '$usermail'");
                    header('location: teacher/index.php');
                    exit;
                }
            }
        }
    }

    // Student login fallback
    $getStudent = $conn->query("SELECT * FROM student_users WHERE email = '$usermail'");
    if ($getStudent && $getStudent->num_rows == 1) {
        $student = $getStudent->fetch_assoc();
        if (password_verify($userpassword, $student['password']) || $userpassword === $student['password']) {
            $_SESSION['user'] = $usermail;
            $_SESSION['usertype'] = 's';
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            header('location: student/index.php');
            exit;
        }
    }

    // Invalid credentials
    $error = 1;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="public/assets/icon/logo.svg">
    <link rel="stylesheet" href="public/style/bootstrap.min.css">
    <link rel="stylesheet" href="public/style/login.css">
    <link rel="stylesheet" href="public/style/main.css">
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link rel="stylesheet" href="public/style/loading.css">
    <title>Grading System TVET</title>
</head>

<body>
    <div class="bg-container"></div>
    <div id="loading-overlay">
        <div class="spinner"></div>
        <p class="loading-text">Please wait... Processing your request</p>
    </div>
    <main>
        <div class="">
            <div class="form-wrapper">
                <div class="image-wrapper">
                    <img src="public/assets/images/login_bg.jpg">
                    <div class="text">
                        <h1>TVET</h1>
                        <h3>Grading System</h3>
                        <p class="text-center" style="font-weight: 300; line-height: 1.2rem; font-size: 14px;">
                            Log in to manage student records, track progress, and ensure accurate grading.
                            Your role matters in shaping students' futures. Let's get started!
                        </p>
                    </div>
                </div>
                <div class="form">
                    <?php if (isset($error)): ?>
                        <div class="text-center alert alert-danger alert-dismissible fade show" role="alert" style="font-size:12px">
                            <strong>Warning!</strong> <br> Wrong Credentials: Invalid Email or Password!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                    <?php endif; ?>

                    <form action="" method="post">
                        <div class="d-flex flex-column gap-4 mb-5">
                            <div class="form-group">
                                <label for="user" class="form-label text-white">Email</label>
                                <input type="text" name="user" id="user" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="pass" class="form-label text-white">Password</label>
                                <input type="password" name="pass" id="pass" class="form-control" required>
                            </div>
                        </div>
                        <div class="action">
                            <button type="submit" class="btn btn-primary form-control" name="login">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="public/js/loading.js"></script>

    <script>
        function createBubble() {
            const bubble = document.createElement("img");
            bubble.classList.add("bubble");
            bubble.src = "public/assets/icon/logo.svg";

            const size = Math.random() * 50 + 16;
            bubble.style.width = `${size}px`;

            bubble.style.left = `${Math.random() * 100}vw`;
            bubble.style.opacity = Math.random() * 0.2 + 0.1;

            document.querySelector(".bg-container").appendChild(bubble);

            gsap.to(bubble, {
                y: -window.innerHeight - size,
                duration: Math.random() * 10 + 5,
                ease: "linear",
                onComplete: () => bubble.remove(),
            });
        }

        setInterval(createBubble, 500);
        gsap.to(".bg-container", {
            keyframes: [{
                    background: "linear-gradient(45deg, rgba(138, 43, 226, 0.8), rgba(255, 94, 98, 0.8))",
                    duration: 2
                },
                {
                    background: "linear-gradient(45deg, rgba(255, 94, 98, 0.8), rgba(138, 43, 226, 0.8))",
                    duration: 2
                }
            ],
            repeat: -1,
            yoyo: true,
            ease: "power1.inOut"
        });
    </script>

</body>

</html>