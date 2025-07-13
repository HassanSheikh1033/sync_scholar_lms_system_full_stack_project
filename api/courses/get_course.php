<?php
// Prevent any output before headers
ob_start();

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');
error_reporting(E_ALL);

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

require_once '../../config/db_config.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Only teachers can access course management'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];

// Check if course ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Course ID is required'
    ]);
    exit();
}

$courseId = $_GET['id'];

try {
    // Get course details
    $query = "SELECT * FROM courses WHERE id = ? AND teacher_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare course statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $courseId, $teacherId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute course query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get course result: ' . $stmt->error);
    }

    if ($result->num_rows === 0) {
        throw new Exception('Course not found or you do not have permission to access it');
    }

    $course = $result->fetch_assoc();
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'course' => $course
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching course: ' . $e->getMessage()
    ]);
} finally {
    // Close statement if it exists
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>