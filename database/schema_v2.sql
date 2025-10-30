-- Create new database and schema with updated terminology
-- Adjust database name as needed

CREATE DATABASE IF NOT EXISTS grading_system_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE grading_system_v2;

-- Programs (formerly courses)
CREATE TABLE IF NOT EXISTS programs (
  id INT NOT NULL AUTO_INCREMENT,
  program_code VARCHAR(50) NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Courses (formerly subjects)
CREATE TABLE IF NOT EXISTS courses (
  id INT NOT NULL AUTO_INCREMENT,
  program_id INT NOT NULL,
  course_code VARCHAR(255) NOT NULL,
  course_title TEXT NOT NULL,
  year_level VARCHAR(100) NOT NULL,
  semester VARCHAR(100) NOT NULL,
  -- Optional legacy fields used by UI
  nth VARCHAR(50) NULL,
  units INT NULL,
  lec INT NULL,
  lab INT NULL,
  PRIMARY KEY (id),
  KEY (program_id),
  CONSTRAINT fk_courses_program FOREIGN KEY (program_id) REFERENCES programs(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Instructors (formerly teachers)
CREATE TABLE IF NOT EXISTS instructors (
  id INT NOT NULL AUTO_INCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  password TEXT NOT NULL,
  gender TEXT NOT NULL,
  status INT NOT NULL DEFAULT 0,
  image TEXT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Instructor-Courses (formerly teacher_subjects)
CREATE TABLE IF NOT EXISTS instructor_courses (
  id INT NOT NULL AUTO_INCREMENT,
  instructor_id INT NOT NULL,
  course_id INT NOT NULL,
  program_id VARCHAR(50) NOT NULL,
  room VARCHAR(50) NOT NULL,
  year_level VARCHAR(100) NOT NULL,
  semester VARCHAR(100) NOT NULL,
  school_year TEXT NOT NULL,
  assigned_date TEXT NOT NULL,
  room_id INT DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (instructor_id),
  KEY (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Users table (unchanged name as used by login)
CREATE TABLE IF NOT EXISTS web_users (
  web_id INT NOT NULL AUTO_INCREMENT,
  email TEXT NOT NULL,
  usertype VARCHAR(10) NOT NULL,
  PRIMARY KEY (web_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Admins (used by admin login and header)
CREATE TABLE IF NOT EXISTS admin (
  a_id INT NOT NULL AUTO_INCREMENT,
  a_name TEXT NOT NULL,
  a_user_name TEXT NOT NULL,
  a_password TEXT NOT NULL,
  a_image TEXT NOT NULL,
  PRIMARY KEY (a_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed default admin if empty
INSERT INTO admin (a_name, a_user_name, a_password, a_image)
SELECT 'Administrator', 'admin@example.com', 'admin', ''
WHERE NOT EXISTS (SELECT 1 FROM admin);

-- Ensure web_users has an admin entry
INSERT INTO web_users (email, usertype)
SELECT 'admin@example.com', 'a'
WHERE NOT EXISTS (SELECT 1 FROM web_users WHERE email = 'admin@example.com');

-- Students (legacy table name used across the app)
CREATE TABLE IF NOT EXISTS student_users (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  course VARCHAR(50) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  s_image TEXT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Enrollments (legacy join table)
CREATE TABLE IF NOT EXISTS teacher_subject_enrollees (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  teacher_subject_id INT NOT NULL,
  PRIMARY KEY (id),
  KEY (student_id),
  KEY (teacher_subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student grades (legacy table used by reports/UI)
CREATE TABLE IF NOT EXISTS student_grades (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  course VARCHAR(100) NOT NULL,
  year_level VARCHAR(150) NOT NULL,
  semester VARCHAR(150) NOT NULL,
  school_year VARCHAR(120) NOT NULL,
  course_code VARCHAR(150) NOT NULL,
  descriptive_title VARCHAR(255) NOT NULL,
  final_rating DECIMAL(3,1) NOT NULL,
  remarks VARCHAR(100) NOT NULL,
  teacher_id INT NOT NULL,
  subject_id INT NOT NULL,
  section TEXT NOT NULL,
  PRIMARY KEY (id),
  KEY (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Compatibility views to keep old names working immediately
CREATE OR REPLACE VIEW teachers AS SELECT id AS t_id, name AS t_name, email AS t_user_name, password AS t_password, gender AS t_gender, status, image AS t_image FROM instructors;
CREATE OR REPLACE VIEW subjects AS 
  SELECT id AS s_id,
         program_id AS s_course,
         course_code AS s_course_code,
         course_title AS s_descriptive_title,
         year_level AS s_year_level,
         semester AS s_semester,
         nth AS s_nth,
         units AS s_units,
         lec AS s_lee,
         lab AS s_lab
  FROM courses;
CREATE OR REPLACE VIEW courses_legacy AS SELECT * FROM programs; -- legacy alias if needed in code paths
CREATE OR REPLACE VIEW teacher_subjects AS 
  SELECT id, instructor_id AS teacher_id, course_id AS subject_id, program_id AS course, room AS section, year_level, semester, school_year, assigned_date, NULL AS schedule_day, '00:00:00' AS schedule_time_start, '00:00:00' AS schedule_time_end, room_id,
    NULL AS m, NULL AS t, NULL AS w, NULL AS th, NULL AS f, NULL AS s, NULL AS ss,
    NULL AS m_start, NULL AS t_start, NULL AS w_start, NULL AS th_start, NULL AS f_start, NULL AS s_start, NULL AS ss_start,
    '' AS m_end, '' AS t_end, '' AS w_end, '' AS th_end, '' AS f_end, '' AS s_end, '' AS ss_end
  FROM instructor_courses;

-- =============================
-- SEED DATA: DIT (Diploma in Information Technology)
-- =============================
-- Program
INSERT INTO programs (program_code, program_name)
SELECT 'DIT', 'Diploma in Information Technology'
WHERE NOT EXISTS (SELECT 1 FROM programs WHERE program_code = 'DIT');

SET @DIT := (SELECT id FROM programs WHERE program_code = 'DIT' LIMIT 1);

-- Courses - 1st Year, First Semester
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'CC 101', 'Introduction to Computing with Keyboard', 'First Year', 'First Semester'
WHERE NOT EXISTS (
  SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='CC 101' AND year_level='First Year' AND semester='First Semester'
);
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'GE 1', 'Understanding the Self', 'First Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='GE 1' AND year_level='First Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'GE 2', 'Readings in Philippine History', 'First Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='GE 2' AND year_level='First Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'GE 3', 'The Contemporary World', 'First Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='GE 3' AND year_level='First Year' AND semester='First Semester');

-- Courses - 1st Year, Second Semester
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'CC 102', 'Computer Programming 2 (Intermediate Programming)', 'First Year', 'Second Semester'
WHERE NOT EXISTS (
  SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='CC 102' AND year_level='First Year' AND semester='Second Semester'
);
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'GE 4', 'Mathematics in the Modern World', 'First Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='GE 4' AND year_level='First Year' AND semester='Second Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'GE 5', 'Purposive Communication', 'First Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='GE 5' AND year_level='First Year' AND semester='Second Semester');

-- Courses - 2nd Year, First Semester (initial set)
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'CC 104', 'Data Structures and Algorithms', 'Second Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='CC 104' AND year_level='Second Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'PF 101', 'Object Oriented Programming', 'Second Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='PF 101' AND year_level='Second Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IS 103', 'Infrastructures and Network Technologies', 'Second Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IS 103' AND year_level='Second Year' AND semester='First Semester');

-- Courses - 2nd Year, Second Semester (initial set)
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IS 102', 'Professional Issues in Information Systems', 'Second Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IS 102' AND year_level='Second Year' AND semester='Second Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IS 101', 'Information Assurance and Security', 'Second Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IS 101' AND year_level='Second Year' AND semester='Second Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IM 101', 'Fundamentals of Database Systems', 'Second Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IM 101' AND year_level='Second Year' AND semester='Second Semester');

-- Courses - 3rd Year, First Semester (initial set)
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IS Elec 1', 'Customer Relationship Management', 'Third Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IS Elec 1' AND year_level='Third Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'IS 106', 'IS Project Management 1', 'Third Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='IS 106' AND year_level='Third Year' AND semester='First Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'Net 102', 'Networking 2', 'Third Year', 'First Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='Net 102' AND year_level='Third Year' AND semester='First Semester');

-- Courses - 3rd Year, Second Semester (initial set)
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'CAP 101', 'Capstone Project 1', 'Third Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='CAP 101' AND year_level='Third Year' AND semester='Second Semester');
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT @DIT, 'CC 106', 'Application Devt & Emerging Technologies', 'Third Year', 'Second Semester'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE program_id=@DIT AND course_code='CC 106' AND year_level='Third Year' AND semester='Second Semester');


-- OPTIONAL: Direct import from legacy dump if loaded into `legacy_gs`
-- CREATE DATABASE legacy_gs; then import grading_system.sql into legacy_gs, and run below
-- This copies ALL DIT subjects from legacy into the new courses table and maps units/lec/lab
-- Comment out if you prefer manual seeding above only.
/*
USE grading_system_v2;

-- Ensure DIT exists
INSERT INTO programs (program_code, program_name)
SELECT 'DIT','Diploma in Information Technology'
WHERE NOT EXISTS (SELECT 1 FROM programs WHERE program_code='DIT');
SET @dit := (SELECT id FROM programs WHERE program_code='DIT' LIMIT 1);

-- Bulk copy courses
INSERT INTO courses (program_id, course_code, course_title, year_level, semester)
SELECT
  @dit,
  s.s_course_code,
  s.s_descriptive_title,
  s.s_year_level,
  s.s_semester
FROM legacy_gs.subjects s
JOIN legacy_gs.courses c ON s.s_course = CAST(c.id AS CHAR)
WHERE c.course_code = 'DIT'
ON DUPLICATE KEY UPDATE
  course_title = VALUES(course_title),
  year_level   = VALUES(year_level),
  semester     = VALUES(semester);

-- Map load metrics
UPDATE courses c
JOIN legacy_gs.courses lc ON lc.course_code = 'DIT'
JOIN legacy_gs.subjects s
  ON s.s_course = CAST(lc.id AS CHAR)
  AND s.s_course_code = c.course_code
  AND s.s_descriptive_title = c.course_title
SET c.nth = NULLIF(s.s_nth,''),
    c.units = NULLIF(s.s_units,'') + 0,
    c.lec = NULLIF(s.s_lee,'') + 0,
    c.lab = s.s_lab
WHERE c.program_id = @dit;
*/

