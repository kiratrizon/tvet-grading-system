<?php





// echo $email;
// exit;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendAdminNotification($teacherName, $subjectCode)
{
    global $conn;

    $mail = new PHPMailer(true);



    $adminQuery = $conn->query("SELECT `a_user_name` FROM `admin` LIMIT 1");
    $adminRow = $adminQuery->fetch_assoc();
    $adminEmail = $adminRow['a_user_name'];


    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ascbtvetdept1994@gmail.com';
        $mail->Password = 'xbyi qiuj cdre bcio';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Sender and Recipient
        $mail->setFrom('ascbtvetdept1994@gmail.com', 'Grading System');
        $mail->addAddress($adminEmail, 'Admin');

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'New Grades Imported';
        $mail->Body = "
            <h3>Notification: Grades Imported</h3>
            <p><strong>Teacher:</strong> $teacherName</p>
            <p><strong>Subject Code:</strong> $subjectCode</p>
            <p>New student grades have been imported into the system.</p>
        ";

        // Send Email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}



// @ascbtvet1994

// tvetgradingsystem
// xbyi qiuj cdre bcio