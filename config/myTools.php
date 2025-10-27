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
            WHERE tse.teacher_subject_id = ? ORDER BY su.name ASC
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
        if ($percent >= 95) return 1.0;
        if ($percent >= 94) return 1.1;
        if ($percent >= 93) return 1.2;
        if ($percent >= 92) return 1.3;
        if ($percent >= 91) return 1.4;
        if ($percent >= 90) return 1.5;
        if ($percent >= 89) return 1.6;
        if ($percent >= 88) return 1.7;
        if ($percent >= 87) return 1.8;
        if ($percent >= 86) return 1.9;
        if ($percent >= 85) return 2.0;
        if ($percent >= 84) return 2.1;
        if ($percent >= 83) return 2.2;
        if ($percent >= 82) return 2.3;
        if ($percent >= 81) return 2.4;
        if ($percent >= 80) return 2.5;
        if ($percent >= 79) return 2.6;
        if ($percent >= 78) return 2.7;
        if ($percent >= 77) return 2.8;
        if ($percent >= 76) return 2.9;
        if ($percent >= 75) return 3.0;
        if ($percent >= 70) return 3.5; // Conditional (Below passing)
        if ($percent >= 60) return 4.0; // Failed but with effort
        return 5.0; // Failed
    }

    public static function gradeRemark($grade)
    {
        if ($grade <= 3.0) return "Passed";
        if ($grade <= 4.0 && $grade > 3.0) return "Conditional";
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
        $title = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]/', '', $title); // remove invalid chars
        $title = mb_substr($title, 0, 31); // limit to 31 chars
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

    public static function getEnrolleeAllGradesByCriteriaAndPeriod($params = [])
    {
        $conn = $params['conn'] ?? null;
        $criteria_id = $params['criteria_id'] ?? null;
        $period = $params['period'] ?? null;
        $enrollee_id = $params['enrollee_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return null;
        }
        if (empty($criteria_id) || empty($period) || empty($enrollee_id)) {
            return null;
        }

        $queryTotalScore = "SELECT sum(cg.score) as total_score FROM criteria_grades cg JOIN criteria_note_records cnr ON cg.criteria_note_record_id = cnr.id WHERE cg.enrollee_id = '$enrollee_id' AND cnr.grading_criterion_id = '$criteria_id' AND cnr.period = '$period'";

        // total score for the enrollee in that criterion
        $totalScoreData = $conn->query($queryTotalScore)->fetch_assoc();
        $totalScore = $totalScoreData['total_score'] ?? 0;

        return $totalScore;
    }

    public static function getTotalItemByCriteriaAndPeriod($params = [])
    {
        $conn = $params['conn'] ?? null;
        $criteria_id = $params['criteria_id'] ?? null;
        $period = $params['period'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return null;
        }
        if (empty($criteria_id) || empty($period)) {
            return null;
        }
        $totalItem = $conn->query("SELECT SUM(total_item) as total_items FROM criteria_note_records WHERE grading_criterion_id = '$criteria_id' AND period = '$period'")->fetch_assoc();
        return $totalItem['total_items'] ?? 0;
    }

    public static function getEnrolleesByTeacherSubjectID($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject = $params['teacher_subject_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return [];
        }
        if (empty($teacher_subject)) {
            return [];
        }

        $stmt = $conn->prepare("
            SELECT tse.*, su.name AS student_name
            FROM teacher_subject_enrollees tse
            JOIN student_users su ON tse.student_id = su.id
            WHERE tse.teacher_subject_id = ? ORDER BY su.name ASC
        ");
        $stmt->bind_param("i", $teacher_subject);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = [];
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        $stmt->close();
        return $response;
    }

    public static function getGradingCriteriaByTeacherSubjectID($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject = $params['teacher_subject_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return null;
        }
        if (empty($teacher_subject)) {
            return null;
        }

        $stmt = $conn->prepare("
            SELECT *
            FROM grading_criteria
            WHERE teacher_subject_id = ? and deleted = 0
        ");
        $stmt->bind_param("i", $teacher_subject);
        $stmt->execute();
        $result = $stmt->get_result();

        $response = [];
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        $stmt->close();
        return $response;
    }

    public static function releaseGrades($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;
        $period = $params['period'] ?? null;
        $release = $params['release'] ?? false;

        if (!$conn || !($conn instanceof mysqli)) {
            return false;
        }
        if (empty($teacher_subject_id) || empty($period)) {
            return false;
        }

        if ($release) {
            $now = date('Y-m-d H:i:s');
            // query if exist
            $releasedQuery = $conn->query("SELECT id from released_grades where period = '$period' and teacher_subject_id = '$teacher_subject_id'");

            if ($releasedQuery->num_rows > 0) {
                // already released
                // update the created timestamp
                $id = $releasedQuery->fetch_assoc()['id'];
                $stmt = $conn->prepare("UPDATE released_grades SET created = ? WHERE id = ?");
                $stmt->bind_param("si", $now, $id);
                $stmt->execute();
            } else {
                // insert new release record
                $stmt = $conn->prepare("INSERT INTO released_grades (teacher_subject_id, period) VALUES (?, ?)");
                $stmt->bind_param("is", $teacher_subject_id, $period);
                $stmt->execute();
            }
        } else {
            // revoke release
            $stmt = $conn->prepare("DELETE FROM released_grades WHERE teacher_subject_id = ? AND period = ?");
            $stmt->bind_param("is", $teacher_subject_id, $period);
            $stmt->execute();
        }
        $stmt->close();
        return true;
    }

    public static function getAvailablePeriodsByTeacherSubjectID($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return [];
        }
        if (empty($teacher_subject_id)) {
            return [];
        }

        $query = $conn->query("SELECT period from released_grades where teacher_subject_id = '$teacher_subject_id'")->fetch_all(MYSQLI_ASSOC);
        $queryPeriods = self::periodList(['conn' => $conn]);
        $periods = [];
        foreach ($queryPeriods as $key => $val) {
            $periods[$key] = $val['label'];
        }
        foreach ($query as $row) {
            unset($periods[$row['period']]);
        }

        return $periods;
    }

    public static function getEnrolleesReleasedGrades($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;
        $period = $params['period'] ?? null;
        $studentId = $params['enrollee_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return null;
        }
        if (empty($teacher_subject_id) || empty($period) || empty($studentId)) {
            return null;
        }

        // check if already released
        $releasedQuery = $conn->query("SELECT * from released_grades where period = '$period' and teacher_subject_id = '$teacher_subject_id'");
        if (!$releasedQuery->num_rows) {
            // already released
            return null;
        }
        // get all grading criteria for this teacher_subject_id
        $gradingCriteria = self::getGradingCriteriaByTeacherSubjectID([
            'conn' => $conn,
            'teacher_subject_id' => $teacher_subject_id
        ]);
        $totalPercentage = 0;
        foreach ($gradingCriteria as $criterion) {
            $criterionId = $criterion['id'];
            // get total item for this criterion and period
            $totalItem = self::getTotalItemByCriteriaAndPeriod([
                'conn' => $conn,
                'criteria_id' => $criterionId,
                'period' => $period
            ]);
            // get enrollee's total score for this criterion and period
            $totalScore = self::getEnrolleeAllGradesByCriteriaAndPeriod([
                'conn' => $conn,
                'criteria_id' => $criterionId,
                'period' => $period,
                'enrollee_id' => $studentId
            ]);
            // calculate percentage
            $percentage = 0;
            if ($totalItem > 0) {
                $percentage = number_format((($totalScore / $totalItem) * 100) * ($criterion['percentage'] / 100), 2);
            }
            $totalPercentage += $percentage;
        }
        return $totalPercentage;
    }

    public static function periodList($params = [])
    {
        $conn = $params['conn'] ?? null;
        if (!$conn || !($conn instanceof mysqli)) {
            return [];
        }
        $periods = $conn->query("SELECT * FROM periods order by weight")->fetch_all(MYSQLI_ASSOC);
        $newPeriods = [];
        foreach ($periods as $period) {
            $id = $period['id'];
            unset($period['id']);
            $newPeriods[$id] = $period;
        }
        return $newPeriods;
    }

    public static function getTeacherSubjectById($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;

        if (!$conn || !($conn instanceof mysqli)) {
            return null;
        }
        if (empty($teacher_subject_id)) {
            return null;
        }

        $subjectData = $conn->query("SELECT * FROM teacher_subjects WHERE id = '$teacher_subject_id'")->fetch_assoc();
        return $subjectData ?? null;
    }

    public static function getSubjectByTeacherSubjectId($params = [])
    {
        $conn = $params['conn'] ?? null;
        $teacher_subject_id = $params['teacher_subject_id'] ?? null;

        if (!$conn || !$teacher_subject_id) {

            return [];
        }

        $query = $conn->query("SELECT s.s_course_code as course_code, s.s_descriptive_title as description, s.s_units as units from teacher_subjects ts join subjects s on s.s_id = ts.subject_id where ts.id = '$teacher_subject_id' limit 1")->fetch_assoc() ?? [];

        return $query;
    }
}
