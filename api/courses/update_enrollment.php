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
header('Access-Control-Allow-Methods: POST');
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
        'message' => 'Only teachers can update enrollments'
    ]);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['id']) || !isset($data['course_id']) || !isset($data['student_id']) || !isset($data['status'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];
$enrollmentId = $data['id'];
$courseId = $data['course_id'];
$studentId = $data['student_id'];
$status = $data['status'];

try {
    // Check if the course belongs to the teacher
    $checkCourseStmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    if (!$checkCourseStmt) {
        throw new Exception('Failed to prepare course check statement: ' . $conn->error);
    }
    
    $checkCourseStmt->bind_param("ii", $courseId, $teacherId);
    if (!$checkCourseStmt->execute()) {
        throw new Exception('Failed to execute course check query: ' . $checkCourseStmt->error);
    }
    
    $courseResult = $checkCourseStmt->get_result();
    if ($courseResult->num_rows === 0) {
        throw new Exception('You do not have permission to update enrollments for this course');
    }
    
    // Check if the enrollment exists and belongs to the teacher's course
    $checkEnrollmentStmt = $conn->prepare("
        SELECT e.id 
        FROM course_enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.id = ? AND c.teacher_id = ?
    ");
    if (!$checkEnrollmentStmt) {
        throw new Exception('Failed to prepare enrollment check statement: ' . $conn->error);
    }
    
    $checkEnrollmentStmt->bind_param("ii", $enrollmentId, $teacherId);
    if (!$checkEnrollmentStmt->execute()) {
        throw new Exception('Failed to execute enrollment check query: ' . $checkEnrollmentStmt->error);
    }
    
    $enrollmentResult = $checkEnrollmentStmt->get_result();
    if ($enrollmentResult->num_rows === 0) {
        throw new Exception('Enrollment not found or you do not have permission to update it');
    }
    
    // Update enrollment
    $updateStmt = $conn->prepare("UPDATE course_enrollments SET course_id = ?, student_id = ?, status = ? WHERE id = ?");
    if (!$updateStmt) {
        throw new Exception('Failed to prepare enrollment update statement: ' . $conn->error);
    }
    
    $updateStmt->bind_param("iisi", $courseId, $studentId, $status, $enrollmentId);
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to execute enrollment update query: ' . $updateStmt->error);
    }
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Enrollment updated successfully'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating enrollment: ' . $e->getMessage()
    ]);
} finally {
    // Close statements if they exist
    if (isset($checkCourseStmt) && $checkCourseStmt instanceof mysqli_stmt) {
        $checkCourseStmt->close();
    }
    if (isset($checkEnrollmentStmt) && $checkEnrollmentStmt instanceof mysqli_stmt) {
        $checkEnrollmentStmt->close();
    }
    if (isset($updateStmt) && $updateStmt instanceof mysqli_stmt) {
        $updateStmt->close();
    }
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
