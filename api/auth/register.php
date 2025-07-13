<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Log received data (remove in production)
    error_log('Received registration data: ' . print_r($data, true));

    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'dateOfBirth', 'gender', 'email', 'phone', 'address', 'username', 'password', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate gender
    $validGenders = ['male', 'female', 'other'];
    if (!in_array($data['gender'], $validGenders)) {
        throw new Exception('Invalid gender value');
    }

    // Validate role
    $validRoles = ['student', 'teacher'];
    if (!in_array($data['role'], $validRoles)) {
        throw new Exception('Invalid role value');
    }

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception("Failed to start transaction: " . $conn->error);
    }

    try {
        // Get role ID
        $stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
        if (!$stmt) {
            throw new Exception("Database error preparing role query: " . $conn->error);
        }
        
        $stmt->bind_param("s", $data['role']);
        if (!$stmt->execute()) {
            throw new Exception("Error executing role query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $role = $result->fetch_assoc();
        $stmt->close();

        if (!$role) {
            throw new Exception('Invalid role: ' . $data['role']);
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, date_of_birth, gender, email, phone, address, username, password, role_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database error preparing user insert: " . $conn->error);
        }

        $stmt->bind_param("sssssssssi", 
            $data['firstName'],
            $data['lastName'],
            $data['dateOfBirth'],
            $data['gender'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['username'],
            $hashedPassword,
            $role['id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error creating user: " . $stmt->error);
        }

        $userId = $conn->insert_id;
        $stmt->close();

        if (!$userId) {
            throw new Exception("Failed to get user ID after insertion");
        }

        error_log("Successfully created user with ID: " . $userId);

        // Insert role-specific details
        if ($data['role'] === 'student') {
            if (!isset($data['rollNumber']) || !isset($data['program']) || !isset($data['semester']) || 
                !isset($data['batchYear']) || !isset($data['previousSchool']) || !isset($data['gpa'])) {
                throw new Exception('Missing required student details');
            }

            $stmt = $conn->prepare("INSERT INTO student_details (user_id, roll_number, program, semester, batch_year, previous_school, gpa) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error preparing student details insert: " . $conn->error);
            }

            $stmt->bind_param("issiisd", 
                $userId,
                $data['rollNumber'],
                $data['program'],
                $data['semester'],
                $data['batchYear'],
                $data['previousSchool'],
                $data['gpa']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error creating student details: " . $stmt->error);
            }
            $stmt->close();

        } else if ($data['role'] === 'teacher') {
            if (!isset($data['employeeId']) || !isset($data['department']) || !isset($data['designation']) || 
                !isset($data['qualification']) || !isset($data['specialization']) || !isset($data['joiningDate']) || 
                !isset($data['experienceYears'])) {
                throw new Exception('Missing required teacher details');
            }

            $stmt = $conn->prepare("INSERT INTO teacher_details (user_id, employee_id, department, designation, qualification, specialization, joining_date, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error preparing teacher details insert: " . $conn->error);
            }

            $stmt->bind_param("issssssi", 
                $userId,
                $data['employeeId'],
                $data['department'],
                $data['designation'],
                $data['qualification'],
                $data['specialization'],
                $data['joiningDate'],
                $data['experienceYears']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error creating teacher details: " . $stmt->error);
            }
            $stmt->close();
        }

        // Commit transaction
        if (!$conn->commit()) {
            throw new Exception("Failed to commit transaction: " . $conn->error);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful',
            'user_id' => $userId
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        if (!$conn->rollback()) {
            error_log("Failed to rollback transaction: " . $conn->error);
        }
        throw $e;
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 