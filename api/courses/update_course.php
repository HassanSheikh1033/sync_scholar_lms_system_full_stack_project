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
        'message' => 'Only teachers can update courses'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['id']) || !isset($data['course_code']) || !isset($data['course_name']) || !isset($data['semester']) || !isset($data['credits'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields'
    ]);
    exit();
}

$courseId = intval($data['id']);
$courseCode = $data['course_code'];
$courseName = $data['course_name'];
$description = $data['description'] ?? '';
$semester = intval($data['semester']);
$credits = intval($data['credits']);

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
        throw new Exception('Course not found or you do not have permission to update it');
    }
    
    // Check if course code already exists for another course
    $codeCheckStmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ? AND id != ?");
    if (!$codeCheckStmt) {
        throw new Exception('Failed to prepare code check statement: ' . $conn->error);
    }
    
    $codeCheckStmt->bind_param("si", $courseCode, $courseId);
    if (!$codeCheckStmt->execute()) {
        throw new Exception('Failed to execute code check query: ' . $codeCheckStmt->error);
    }
    
    $codeCheckResult = $codeCheckStmt->get_result();
    if ($codeCheckResult->num_rows > 0) {
        throw new Exception('Course code already exists for another course');
    }
    
    // Update course
    $updateStmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ?, semester = ?, credits = ? WHERE id = ? AND teacher_id = ?");
    if (!$updateStmt) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }
    
    $updateStmt->bind_param("sssiiii", $courseCode, $courseName, $description, $semester, $credits, $courseId, $teacherId);
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to execute update query: ' . $updateStmt->error);
    }
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Course updated successfully'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating course: ' . $e->getMessage()
    ]);
} finally {
    // Close statements if they exist
    if (isset($checkStmt) && $checkStmt instanceof mysqli_stmt) {
        $checkStmt->close();
    }
    if (isset($codeCheckStmt) && $codeCheckStmt instanceof mysqli_stmt) {
        $codeCheckStmt->close();
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