<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../vendor_excel/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class myTools
{

    public static function sendEmail($params = [])
    {
        $to = $params['to'] ?? [];
        $name = $params['name'] ?? [];
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
            foreach ($to as $index => $email) {
                $recipientName = $name[$index] ?? '';
                $mail->addAddress($email, $recipientName);
            }

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send Email
            try {
                $mail->send();
            } catch (Exception $e) {
                // Log error or handle accordingly
                // log into error.log file
                error_log("Email could not be sent to $to. Mailer Error: {$mail->ErrorInfo}");
            }
            return true;
        } catch (Exception $e) {
            // Log error or handle accordingly
            error_log("Email setup failed for $to. Exception: " . $e->getMessage());
        }
        return false;
    }

    // Debugging function
    public static function display($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    public static function registerStudents($params = [])
    {
        $conn = $params['conn'] ?? null;
        $email = $params['email'] ?? '';
        $name = $params['name'] ?? '';
        $course_id = $params['course_id'] ?? null;
        $response = [];
        $response['status'] = false;
        if (!$conn || !($conn instanceof mysqli)) {
            $response['message'] = 'Invalid database connection.';
            return $response;
        }
        if (empty($email) || empty($name) || empty($course_id)) {
            $response['message'] = 'All fields are required.';
            return $response;
        }
        $getLastInsertedId = ($conn->query("SELECT MAX(id) AS last_id FROM student_users")->fetch_assoc()['last_id'] ?? 0) + 1;
        $defaultPasswordUnhashed = 'student' . $getLastInsertedId;
        $defaultPasswordHashed = password_hash($defaultPasswordUnhashed, PASSWORD_DEFAULT);
        // before inserting, check if email already exists
        $emailCheck = $conn->query("SELECT id from student_users WHERE email = '$email' limit 1");
        if ($emailCheck->num_rows > 0) {
            $response['message'] = 'Student with this email already exists.';
            return $response;
        } else {
            $stmt = $conn->prepare("INSERT INTO student_users (name, email, password, course) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $email, $defaultPasswordHashed, $course_id);
            if ($stmt->execute()) {
                // Send email with default password
                $emailParams = [
                    'to' => [$email],
                    'name' => [$name],
                    'subject' => 'Your Student Account Details',
                    'body' => "<p>Dear $name,</p>
                            <p>Your student account has been created successfully.</p>
                            <p>Your default password is: <strong>$defaultPasswordUnhashed</strong></p>
                            <p>Please log in and change your password immediately for security reasons.</p>
                            <p>Best regards,<br>Grading System Team</p>"
                ];
                self::sendEmail($emailParams);
                $response['status'] = true;
                $response['message'] = 'Student added successfully. An email has been sent to the student with their login details.';
            } else {
                $response['message'] = 'Failed to add student. Please try again.';
            }
            $stmt->close();
        }
        return $response;
    }

    public static function getStudentsByTeacherSubject($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;
        $response = [];
        if (!$conn || !($conn instanceof mysqli)) {
            return $response;
        }
        if (empty($teacher_subject_id)) {
            return $response;
        }
        $stmt = $conn->prepare("
            SELECT tse.*, su.name AS student_name
            FROM teacher_subject_enrollees tse
            JOIN student_users su ON tse.student_id = su.id
            WHERE tse.teacher_subject_id = ?
        ");
        $stmt->bind_param("i", $teacher_subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        $stmt->close();
        return $response;
    }

    public static function convertToCollegeGrade($percent)
    {
        if ($percent >= 97) return 1.0;
        if ($percent >= 94) return 1.25;
        if ($percent >= 91) return 1.5;
        if ($percent >= 88) return 1.75;
        if ($percent >= 85) return 2.0;
        if ($percent >= 82) return 2.25;
        if ($percent >= 79) return 2.5;
        if ($percent >= 76) return 2.75;
        if ($percent >= 75) return 3.0;
        if ($percent >= 70) return 3.5;
        if ($percent >= 60) return 4.0;
        return 5.0; // Fail
    }

    public static function gradeRemark($grade)
    {
        if ($grade == 1.0) return "Passed";
        if ($grade <= 1.75) return "Passed";
        if ($grade <= 2.5) return "Passed";
        if ($grade == 2.75) return "Passed";
        if ($grade == 3.0) return "Passed";
        if ($grade == 3.5) return "Conditional";
        if ($grade == 4.0) return "Conditional";
        return "Failed";
    }

    public static function exportExcelTemplate($params = [])
    {
        $headers = $params['headers'] ?? [];
        $filename = $params['filename'] ?? 'template.xlsx';
        $title = $params['title'] ?? 'Template';
        if (empty($headers)) {
            return false;
        }
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row
        $chars = range('A', 'Z');
        foreach ($headers as $index => $header) {
            $col = $chars[$index] ?? null;
            if ($col) {
                $sheet->setCellValue($col . '1', $header);
            }
        }
        $sheet->setTitle($title);
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        return true;
    }
}
