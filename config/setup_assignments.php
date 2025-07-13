<!-- <?php
require_once 'db_config.php';

try {
    // Create assignments table
    $sql = "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        course_id INT NOT NULL,
        teacher_id INT NOT NULL,
        due_date DATETIME NOT NULL,
        total_marks DECIMAL(5,2) NOT NULL,
        file_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id),
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating assignments table: " . $conn->error);
    }

    // Create courses table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(20) NOT NULL UNIQUE,
        course_name VARCHAR(255) NOT NULL,
        description TEXT,
        teacher_id INT NOT NULL,
        semester INT NOT NULL,
        credits INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating courses table: " . $conn->error);
    }

    // Create student_assignments table for submissions
    $sql = "CREATE TABLE IF NOT EXISTS student_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        student_id INT NOT NULL,
        submission_file VARCHAR(255),
        submission_text TEXT,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        marks_obtained DECIMAL(5,2),
        feedback TEXT,
        status ENUM('pending', 'submitted', 'graded', 'late') DEFAULT 'pending',
        FOREIGN KEY (assignment_id) REFERENCES assignments(id),
        FOREIGN KEY (student_id) REFERENCES users(id),
        UNIQUE KEY unique_submission (assignment_id, student_id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating student_assignments table: " . $conn->error);
    }

    // Create course_enrollments table
    $sql = "CREATE TABLE IF NOT EXISTS course_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        student_id INT NOT NULL,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
        FOREIGN KEY (course_id) REFERENCES courses(id),
        FOREIGN KEY (student_id) REFERENCES users(id),
        UNIQUE KEY unique_enrollment (course_id, student_id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating course_enrollments table: " . $conn->error);
    }

    echo json_encode(['status' => 'success', 'message' => 'Assignment tables created successfully']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?> 



 -->
