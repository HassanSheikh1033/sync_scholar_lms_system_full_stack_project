-- Create users table if not exists
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `user_type` ENUM('student', 'teacher', 'admin') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table if not exists
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_code` VARCHAR(20) NOT NULL UNIQUE,
    `course_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `teacher_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `credits` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Create course_enrollments table if not exists
CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `course_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `status` ENUM('active', 'completed', 'dropped') NOT NULL DEFAULT 'active',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment` (`course_id`, `student_id`)
);

-- Insert sample teacher if not exists
INSERT INTO `users` (`username`, `password`, `email`, `user_type`)
SELECT 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher1@example.com', 'teacher'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'teacher1');

-- Insert sample course if not exists
INSERT INTO `courses` (`course_code`, `course_name`, `description`, `teacher_id`, `semester`, `credits`)
SELECT 'MTH-100', 'Calculus', 'Basic Mathematics', 
       (SELECT id FROM users WHERE username = 'teacher1' AND user_type = 'teacher'),
       4, 3
WHERE NOT EXISTS (SELECT 1 FROM `courses` WHERE `course_code` = 'MTH-100'); 