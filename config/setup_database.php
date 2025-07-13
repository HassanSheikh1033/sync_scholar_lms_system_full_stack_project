<?php
require_once 'db_config.php';

try {
    // Create roles table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating roles table: " . $conn->error);
    }

    // Insert default roles if they don't exist
    $roles = [
        ['admin', 'System Administrator'],
        ['teacher', 'Course Teacher'],
        ['student', 'Student']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO roles (name, description) VALUES (?, ?)");
    foreach ($roles as $role) {
        $stmt->bind_param("ss", $role[0], $role[1]);
        $stmt->execute();
    }
    $stmt->close();

    // Create users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT NOT NULL AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    ) ENGINE=InnoDB AUTO_INCREMENT=1";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create student_details table
    $sql = "CREATE TABLE IF NOT EXISTS student_details (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        roll_number VARCHAR(20) NOT NULL UNIQUE,
        program VARCHAR(50) NOT NULL,
        semester INT NOT NULL,
        batch_year YEAR NOT NULL,
        previous_school VARCHAR(100) NOT NULL,
        gpa DECIMAL(3,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating student_details table: " . $conn->error);
    }

    // Create teacher_details table
    $sql = "CREATE TABLE IF NOT EXISTS teacher_details (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        employee_id VARCHAR(20) NOT NULL UNIQUE,
        department VARCHAR(50) NOT NULL,
        designation VARCHAR(50) NOT NULL,
        qualification VARCHAR(100) NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        joining_date DATE NOT NULL,
        experience_years INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating teacher_details table: " . $conn->error);
    }

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
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating assignments table: " . $conn->error);
    }

    // Create student_assignments table
    $sql = "CREATE TABLE IF NOT EXISTS student_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('pending', 'submitted', 'graded') NOT NULL DEFAULT 'pending',
        submission_date DATETIME DEFAULT NULL,
        grade DECIMAL(5,2) DEFAULT NULL,
        feedback TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating student_assignments table: " . $conn->error);
    }

    // Create courses table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(20) NOT NULL UNIQUE,
        course_name VARCHAR(100) NOT NULL,
        description TEXT,
        teacher_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating courses table: " . $conn->error);
    }

    // Insert a sample course for testing if none exists
    $checkCourse = $conn->query("SELECT COUNT(*) as count FROM courses");
    $courseCount = $checkCourse->fetch_assoc()['count'];

    if ($courseCount == 0) {
        // Get the first teacher's ID
        $teacherQuery = "SELECT u.id FROM users u 
                        JOIN roles r ON u.role_id = r.id 
                        WHERE r.name = 'teacher' 
                        LIMIT 1";
        $teacherResult = $conn->query($teacherQuery);
        
        if ($teacherResult && $teacherResult->num_rows > 0) {
            $teacherId = $teacherResult->fetch_assoc()['id'];
            
            // Insert a sample course
            $insertCourse = $conn->prepare("INSERT INTO courses (course_code, course_name, description, teacher_id) 
                                          VALUES (?, ?, ?, ?)");
            $courseCode = "CS101";
            $courseName = "Introduction to Programming";
            $description = "Basic programming concepts and practices";
            
            $insertCourse->bind_param("sssi", $courseCode, $courseName, $description, $teacherId);
            $insertCourse->execute();
            $insertCourse->close();
        }
    }

    // Create course_enrollments table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS course_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('active', 'completed', 'dropped') NOT NULL DEFAULT 'active',
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_enrollment (course_id, student_id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating course_enrollments table: " . $conn->error);
    }

    // Create an admin user if it doesn't exist
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $adminRole = $conn->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
    $adminRoleId = $adminRole->fetch_assoc()['id'];

    $stmt = $conn->prepare("INSERT IGNORE INTO users (first_name, last_name, date_of_birth, gender, email, phone, address, username, password, role_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $adminFirstName = "Admin";
    $adminLastName = "User";
    $adminDob = "2000-01-01";
    $adminGender = "other";
    $adminEmail = "admin@example.com";
    $adminPhone = "1234567890";
    $adminAddress = "Admin Address";
    $adminUsername = "admin";

    $stmt->bind_param("sssssssssi", 
        $adminFirstName, $adminLastName, $adminDob, $adminGender, $adminEmail, 
        $adminPhone, $adminAddress, $adminUsername, $adminPassword, $adminRoleId
    );
    $stmt->execute();
    $stmt->close();

    // Create assignment_submissions table
    $sql = "CREATE TABLE IF NOT EXISTS assignment_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        student_id INT NOT NULL,
        submission_file VARCHAR(255) NOT NULL,
        submission_date DATETIME NOT NULL,
        grade DECIMAL(5,2) DEFAULT NULL,
        feedback TEXT,
        status ENUM('submitted', 'graded', 'late') NOT NULL DEFAULT 'submitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_submission (assignment_id, student_id)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating assignment_submissions table: " . $conn->error);
    }

    // Create attendance table
    $sql = "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        student_id INT NOT NULL,
        date DATE NOT NULL,
        status ENUM('present', 'absent', 'late') NOT NULL,
        remarks TEXT,
        marked_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (course_id, student_id, date)
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating attendance table: " . $conn->error);
    }

    echo json_encode(['status' => 'success', 'message' => 'Database setup completed successfully']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?> 



