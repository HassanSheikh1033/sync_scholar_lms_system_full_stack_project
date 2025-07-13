-- Create students table if not exists
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `roll_number` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table if not exists
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_code` VARCHAR(20) UNIQUE NOT NULL,
    `course_name` VARCHAR(100) NOT NULL,
    `teacher_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`)
);

-- Create course_enrollments table if not exists
CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`),
    UNIQUE KEY `unique_enrollment` (`course_id`, `student_id`)
);

-- Create attendance table if not exists
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `status` ENUM('present', 'absent', 'late') NOT NULL,
    `remarks` TEXT,
    `marked_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`),
    FOREIGN KEY (`marked_by`) REFERENCES `users`(`id`)
); 