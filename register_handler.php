<?php
// Prevent any output before headers
ob_start();

// Disable error display and enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

// Set error handler to prevent HTML output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage() // Show actual error message instead of generic one
    ]);
    exit();
});

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    try {
        ob_end_clean(); // Clear any output buffer
        $response = [
            'status' => $status,
            'message' => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        // Ensure proper JSON encoding
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            error_log("JSON encoding error: " . json_last_error_msg());
            $json = json_encode([
                'status' => 'error',
                'message' => 'Error encoding response'
            ]);
        }
        
        echo $json;
        exit();
    } catch (Exception $e) {
        error_log("Error in sendJsonResponse: " . $e->getMessage());
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage() // Show actual error message
        ]);
        exit();
    }
}

try {
    // Log the raw input for debugging
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);

    // Parse JSON input
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        sendJsonResponse('error', 'Invalid JSON data received');
    }

    // Log the parsed data
    error_log("Parsed data: " . print_r($data, true));

    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'dob', 'gender', 'email', 'phone', 'address', 'username', 'password', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            error_log("Missing required field: $field");
            sendJsonResponse('error', "Missing required field: $field");
        }
    }

    // Validate role-specific fields
    if ($data['role'] === 'student') {
        $studentFields = [
            'rollNumber' => 'Roll Number',
            'program' => 'Program',
            'semester' => 'Semester',
            'batchYear' => 'Batch Year',
            'prevSchool' => 'Previous School',
            'gpa' => 'GPA'
        ];
        
        $missingFields = [];
        foreach ($studentFields as $field => $label) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $missingFields[] = $label;
            }
        }
        
        if (!empty($missingFields)) {
            error_log("Missing student fields: " . implode(', ', $missingFields));
            error_log("Available fields: " . implode(', ', array_keys($data)));
            sendJsonResponse('error', "Missing required student fields: " . implode(', ', $missingFields));
        }
    } elseif ($data['role'] === 'teacher') {
        $teacherFields = [
            'employeeId' => 'Employee ID',
            'department' => 'Department',
            'designation' => 'Designation',
            'qualification' => 'Qualification',
            'specialization' => 'Specialization',
            'joiningDate' => 'Joining Date',
            'experienceYears' => 'Experience Years'
        ];
        
        $missingFields = [];
        foreach ($teacherFields as $field => $label) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $missingFields[] = $label;
            }
        }
        
        if (!empty($missingFields)) {
            error_log("Missing teacher fields: " . implode(', ', $missingFields));
            sendJsonResponse('error', "Missing required teacher fields: " . implode(', ', $missingFields));
        }
    }

    // Include database configuration
    if (!file_exists('config/db_config.php')) {
        error_log("Database config file not found");
        sendJsonResponse('error', 'Database configuration file not found');
    }
    
    require_once 'config/db_config.php';
    
    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: No connection object");
        sendJsonResponse('error', 'Database connection failed');
    }
    if ($conn->connect_error) {
        error_log("Database connection error: " . $conn->connect_error);
        sendJsonResponse('error', 'Database connection error');
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        sendJsonResponse('error', 'Only POST method is allowed');
    }

    // Start transaction
    if (!$conn->begin_transaction()) {
        error_log("Failed to start transaction: " . $conn->error);
        sendJsonResponse('error', 'Failed to start transaction');
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

    try {
        // Get role ID
        $roleQuery = "SELECT id FROM roles WHERE name = ?";
        $roleStmt = $conn->prepare($roleQuery);
        if (!$roleStmt) {
            error_log("Failed to prepare role query: " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        $roleStmt->bind_param("s", $data['role']);
        if (!$roleStmt->execute()) {
            error_log("Failed to execute role query: " . $roleStmt->error);
            throw new Exception("Database error: " . $roleStmt->error);
        }

        $roleResult = $roleStmt->get_result();
        if ($roleResult->num_rows === 0) {
            error_log("Invalid role: " . $data['role']);
            throw new Exception('Invalid role value');
        }
        
        $roleId = $roleResult->fetch_assoc()['id'];
        $roleStmt->close();

        // Insert into users table
        $userQuery = "INSERT INTO users (first_name, last_name, date_of_birth, gender, email, phone, address, username, password, role_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $userStmt = $conn->prepare($userQuery);
        if (!$userStmt) {
            error_log("Failed to prepare user query: " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $userStmt->bind_param("sssssssssi", 
            $data['firstName'],
            $data['lastName'],
            $data['dob'],
            $data['gender'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['username'],
            $hashedPassword,
            $roleId
        );
        
        if (!$userStmt->execute()) {
            error_log("Failed to insert user: " . $userStmt->error);
            throw new Exception("Error creating user: " . $userStmt->error);
        }
        
        $userId = $conn->insert_id;
        $userStmt->close();

        // Insert role-specific details
        if ($data['role'] === 'student') {
            $studentQuery = "INSERT INTO student_details (user_id, roll_number, program, semester, batch_year, previous_school, gpa) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $studentStmt = $conn->prepare($studentQuery);
            if (!$studentStmt) {
                error_log("Failed to prepare student query: " . $conn->error);
                throw new Exception("Database error: " . $conn->error);
            }

            // Convert batch year to integer
            $batchYear = intval($data['batchYear']);
            // Convert semester to integer
            $semester = intval($data['semester']);
            // Convert GPA to float
            $gpa = floatval($data['gpa']);

            $studentStmt->bind_param("issiisd", 
                $userId,
                $data['rollNumber'],
                $data['program'],
                $semester,
                $batchYear,
                $data['prevSchool'],
                $gpa
            );
            
            if (!$studentStmt->execute()) {
                error_log("Failed to insert student details: " . $studentStmt->error);
                throw new Exception("Error creating student details: " . $studentStmt->error);
            }
            $studentStmt->close();
        } elseif ($data['role'] === 'teacher') {
            $teacherQuery = "INSERT INTO teacher_details (user_id, employee_id, department, designation, qualification, specialization, joining_date, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $teacherStmt = $conn->prepare($teacherQuery);
            $teacherStmt->bind_param("issssssi", 
                $userId,
                $data['employeeId'],
                $data['department'],
                $data['designation'],
                $data['qualification'],
                $data['specialization'],
                $data['joiningDate'],
                $data['experienceYears']
            );
            
            if (!$teacherStmt->execute()) {
                throw new Exception("Error creating teacher details: " . $teacherStmt->error);
            }
            $teacherStmt->close();
        }

        // Commit transaction
        if (!$conn->commit()) {
            error_log("Failed to commit transaction: " . $conn->error);
            throw new Exception("Failed to commit transaction");
        }
        
        // Send success response
        sendJsonResponse('success', 'Registration successful', [
            'redirect' => 'login.html'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
        throw $e; // Re-throw to be caught by outer try-catch
    }

} catch (Exception $e) {
    error_log("Registration failed: " . $e->getMessage());
    sendJsonResponse('error', $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

