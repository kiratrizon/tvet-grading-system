<?php

session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

/*
// data passed
data: {
    course: course,
    year: year,
    semester: semester,
    sy: sy
}
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $q = isset($_POST['q']) ? trim($_POST['q']) : '';
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $sy = isset($_POST['sy']) ? $_POST['sy'] : '';
    $alias = 'ts';
    $conditions = [];
    // columns of teacher_subjects table course, semester, year_level, school_year
    if (!empty($course)) {
        $conditions[] = "$alias.course = '" . $conn->real_escape_string($course) . "'";
    }
    if (!empty($year)) {
        $conditions[] = "$alias.year_level = '" . $conn->real_escape_string($year) . "'";
    }
    if (!empty($semester)) {
        $conditions[] = "$alias.semester = '" . $conn->real_escape_string($semester) . "'";
    }
    if (!empty($sy)) {
        $conditions[] = "$alias.school_year = '" . $conn->real_escape_string($sy) . "'";
    }
    $whereClause = '';
    if (count($conditions) > 0) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }

    $columns = [
        'p.id as program_id',
        'p.program_code as program',
        'p.program_name as program_name',
        'ts.year_level as year_level, ts.semester as semester, ts.school_year as school_year'
    ];

    $sql = "SELECT " . implode(', ', $columns) . " from teacher_subjects ts join subjects s on ts.subject_id = s.s_id join programs p on ts.course = p.id
            $whereClause
            GROUP BY p.program_code, p.program_code, ts.year_level, ts.semester, ts.school_year
            ORDER BY p.program_code, ts.school_year DESC, ts.year_level, ts.semester";

    /*
    Array
    (
        [0] => Array
            (
                [program] => DBOT
                [year_level] => First Year
                [semester] => First Semester
                [school_year] => 2024-2025
            )

        [1] => Array
            (
                [program] => DIT
                [year_level] => First Year
                [semester] => First Semester
                [school_year] => 2024-2025
            )

        [2] => Array
            (
                [program] => DSOT
                [year_level] => Second Year
                [semester] => Second Semester
                [school_year] => 2025-2026
            )

    )
    */

    $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

?>
    <?php if (count($result) > 0) { ?>
        <?php foreach ($result as $item):

            $subjectsForThisEntry = $conn->query("SELECT ts.id from teacher_subjects ts where ts.course = '{$item['program_id']}' and ts.semester = '{$item['semester']}' and ts.school_year = '{$item['school_year']}' and ts.year_level = '{$item['year_level']}'")->fetch_all(MYSQLI_ASSOC);

            // make an array of subject ids
            $subjectIds = array_map(function ($sub) {
                return $sub['id'];
            }, $subjectsForThisEntry);
            // find all students enrolled in these subjects
            $subjectIdsList = implode(',', $subjectIds);
            $nameLike = '';
            if (!empty($q)) {
                $escaped = $conn->real_escape_string($q);
                $nameLike = " AND su.name LIKE '%$escaped%' ";
            }
            $students = $conn->query("SELECT su.id, su.name from teacher_subject_enrollees tse join student_users su on tse.student_id = su.id where tse.teacher_subject_id IN ($subjectIdsList) $nameLike group by tse.student_id order by su.name")->fetch_all(MYSQLI_ASSOC);
            // myTools::display($students);
            // exit;
        ?>
            <?php if ($students): ?>
                <div class="print_students">

                    <h3 class="mt-4 fw-bold"><?= htmlspecialchars($item['program']) ?> - <?= htmlspecialchars($item['program_name']) ?></h3>
                    <h5 class="mb-3">
                        <?= htmlspecialchars($item['year_level']) ?> |
                        <?= htmlspecialchars($item['semester']) ?> |
                        <?= htmlspecialchars($item['school_year']) ?>
                    </h5>

                    <div class="card shadow p-4 mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-student"
                                                data-id="<?= $student['id'] ?>"
                                                data-name="<?= htmlspecialchars($student['name']) ?>"
                                                data-subjects="<?= !empty($subjectIds) ? htmlspecialchars(json_encode($subjectIds), ENT_QUOTES, 'UTF-8') : "[]" ?>"
                                                data-year-level="<?= $item['year_level'] ?>"
                                                data-program="<?= $item['program'] ?>"
                                                data-semester="<?= $item['semester'] ?>"
                                                data-school-year="<?= $item['school_year'] ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                        </td>


                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>


    <?php } else { ?>
        <div class="alert alert-info">No data found for the selected filters.</div>
    <?php } ?>
    <!-- modal -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- larger modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Student Name -->
                    <p class="mb-2"><strong>Name:</strong> <span id="modalStudentName" class="text-primary"></span></p>

                    <!-- Program and Year -->
                    <p class="mb-2"><strong>Year & Program:</strong> <span id="modalStudentProgramYear" class="text-success"></span></p>

                    <!-- Semester -->
                    <p class="mb-2"><strong>Semester:</strong> <span id="modalStudentSemester" class="text-info"></span></p>

                    <!-- School Year -->
                    <p class="mb-3"><strong>School Year:</strong> <span id="modalStudentSchoolYear" class="text-warning"></span></p>

                    <!-- Bootstrap Table for Grades -->
                    <div class="table-responsive">
                        <table id="modalStudentGrades" class="table table-bordered table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Course</th>
                                    <th>Grade</th>
                                    <th>Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Grades will be dynamically injected here -->
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>


<?php } ?>