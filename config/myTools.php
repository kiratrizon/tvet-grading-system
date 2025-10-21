<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
class myTools {

    public static function sendEmail($params = []){
        $to = $params['to'] ?? '';
        $name = $params['name'] ?? '';
        if (empty($to)) {
            return false;
        }
        $subject = $params['subject'] ?? 'No Subject';
        $body = $params['body'] ?? '';
        
        $mail = new PHPMailer(true);
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
            $mail->addAddress($to, $name);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send Email
            $mail->send();
            return true;
        } catch (Exception $e) {
        }
        return false;
    }
}