<?php
// Prevent any output before headers
ob_start();

// Disable error display and enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
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
        'message' => $e->getMessage()
    ]);
    exit();
});

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

require_once '../config/db_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'User not logged in',
        'debug' => [
            'session' => $_SESSION,
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'
        ]
    ]);
    exit();
}

try {
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'No connection'));
    }

    $userId = $_SESSION['user_id'];
    
    // Debug: Check if user exists and is a teacher
    $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if (!$checkUser) {
        throw new Exception('Failed to prepare user check statement: ' . $conn->error);
    }

    $checkUser->bind_param("i", $userId);
    if (!$checkUser->execute()) {
        throw new Exception('Failed to execute user check: ' . $checkUser->error);
    }

    $userResult = $checkUser->get_result();
    if (!$userResult) {
        throw new Exception('Failed to get user result: ' . $checkUser->error);
    }
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User not found in database');
    }
    
    // Get courses for the teacher
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id AND status = 'active') as enrolled_students
              FROM courses c
              WHERE c.teacher_id = ?
              ORDER BY c.course_name";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare courses statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute courses query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get courses result: ' . $stmt->error);
    }

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            'id' => $row['id'],
            'course_code' => $row['course_code'],
            'course_name' => $row['course_name'],
            'description' => $row['description'],
            'enrolled_students' => $row['enrolled_students']
        ];
    }
    
    // Debug: Log the response
    error_log("Courses found for teacher $userId: " . count($courses));
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'courses' => $courses,
        'debug' => [
            'user_id' => $userId,
            'courses_count' => count($courses)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_teacher_courses.php: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching courses: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    // Close statements if they exist
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($checkUser) && $checkUser instanceof mysqli_stmt) {
        $checkUser->close();
    }
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?> 