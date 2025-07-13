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
        'message' => 'Only teachers can delete courses'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Course ID is required'
    ]);
    exit();
}

$courseId = intval($data['id']);

try {
    // Check if course exists and belongs to the teacher
    $checkStmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    if (!$checkStmt) {
        throw new Exception('Failed to prepare check statement: ' . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $courseId, $teacherId);
    if (!$checkStmt->execute()) {
        throw new Exception('Failed to execute check query: ' . $checkStmt->error);
    }
    
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows === 0) {
        throw new Exception('Course not found or you do not have permission to delete it');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete course enrollments
    $deleteEnrollmentsStmt = $conn->prepare("DELETE FROM course_enrollments WHERE course_id = ?");
    if (!$deleteEnrollmentsStmt) {
        throw new Exception('Failed to prepare delete enrollments statement: ' . $conn->error);
    }
    
    $deleteEnrollmentsStmt->bind_param("i", $courseId);
    if (!$deleteEnrollmentsStmt->execute()) {
        throw new Exception('Failed to delete course enrollments: ' . $deleteEnrollmentsStmt->error);
    }
    
    // Delete attendance records
    $deleteAttendanceStmt = $conn->prepare("DELETE FROM attendance WHERE course_id = ?");
    if (!$deleteAttendanceStmt) {
        throw new Exception('Failed to prepare delete attendance statement: ' . $conn->error);
    }
    
    $deleteAttendanceStmt->bind_param("i", $courseId);
    if (!$deleteAttendanceStmt->execute()) {
        throw new Exception('Failed to delete attendance records: ' . $deleteAttendanceStmt->error);
    }
    
    // Delete course
    $deleteCourseStmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    if (!$deleteCourseStmt) {
        throw new Exception('Failed to prepare delete course statement: ' . $conn->error);
    }
    
    $deleteCourseStmt->bind_param("ii", $courseId, $teacherId);
    if (!$deleteCourseStmt->execute()) {
        throw new Exception('Failed to delete course: ' . $deleteCourseStmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Course deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction if an error occurred
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error deleting course: ' . $e->getMessage()
    ]);
} finally {
    // Close statements if they exist
    if (isset($checkStmt) && $checkStmt instanceof mysqli_stmt) {
        $checkStmt->close();
    }
    if (isset($deleteEnrollmentsStmt) && $deleteEnrollmentsStmt instanceof mysqli_stmt) {
        $deleteEnrollmentsStmt->close();
    }
    if (isset($deleteAttendanceStmt) && $deleteAttendanceStmt instanceof mysqli_stmt) {
        $deleteAttendanceStmt->close();
    }
    if (isset($deleteCourseStmt) && $deleteCourseStmt instanceof mysqli_stmt) {
        $deleteCourseStmt->close();
    }
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>