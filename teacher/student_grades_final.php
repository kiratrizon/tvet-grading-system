<?php
session_start();

require '../config/conn.php';
require '../config/myTools.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherSubject = $_POST['teacher_subject_id'] ?? null;

    $coverage = myTools::periodList(compact('conn'));
    $students = myTools::getStudentsByTeacherSubject(['teacher_subject_id' => $teacherSubject, 'conn' => $conn]);
?>
    <table id="teacherTable" class="display nowrap table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Enrollee ID</th>
                <th><i class="fa-solid fa-user"></i> Name</th>
                <!-- <th class="text-center"><i class="fa-solid fa-cogs"></i> Action</th> -->
                <?php foreach ($coverage as $key => $cov) { ?>
                    <th><?= $cov['label'] ?> <?= $cov['weight'] ?>%</th>
                <?php } ?>
                <th>Final Rating</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td class="text-start"><?= $student['id']; ?></td>
                    <td><?= $student['student_name']; ?> <?= !$student['read_flg'] ? '<span class="text-danger">(New)</span>' : '' ?></td>

                    <!-- <td>
                                            <div class="action">
                                                <button type="submit" class="btn btn-primary update" data-bs-toggle="modal" data-bs-target="#editGrades">
                                                    <i class="fa-solid fa-pencil-alt"></i>
                                                </button>
                                                <button type="submit" class="btn btn-warning view" data-bs-toggle="modal" data-bs-target="#viewGrades">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger flag-lr" data-bs-toggle="modal" data-bs-target="#flagLRModal" data-student-id="" data-subject-id="" data-studid="" data-studremarks="">
                                                    <i class="fa-solid fa-flag"></i>
                                                </button>
                                            </div>
                                        </td> -->

                    <?php
                    $studentTotalCoverage = 0;
                    $totalAdded = 0;
                    foreach ($coverage as $key => $cov) {

                        $totalReleaseGradeByPeriod = myTools::getEnrolleesReleasedGrades([
                            'conn' => $conn,
                            'enrollee_id' => $student['id'],
                            'teacher_subject_id' => $teacherSubject,
                            'period' => $key
                        ]);
                        if ($totalReleaseGradeByPeriod) {
                            $studentTotalCoverage += number_format($totalReleaseGradeByPeriod * ($cov['weight'] / 100), 2);
                            $totalAdded++;
                        }
                    ?>
                        <td class="text-center">
                            <?= isset($totalReleaseGradeByPeriod) ? myTools::convertToCollegeGrade($totalReleaseGradeByPeriod) : '-' ?>
                        </td>
                    <?php }
                    $finalGrade = myTools::convertToCollegeGrade($studentTotalCoverage);
                    ?>
                    <td class="text-center">
                        <?= ((count($coverage) == $totalAdded) && $totalAdded > 0) ? $finalGrade : '-' ?>
                    </td>
                    <td class="text-center">
                        <?= ((count($coverage) == $totalAdded) && $totalAdded > 0) ? myTools::gradeRemark($finalGrade) : '-' ?>
                    </td>
                </tr>
            <?php
            endforeach ?>
        </tbody>
    </table>
<?php }
?>