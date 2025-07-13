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
        'message' => 'Only teachers can enroll students'
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
if (!isset($data['course_id']) || !isset($data['student_id']) || !isset($data['status'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];
$courseId = $data['course_id'];
$studentId = $data['student_id'];
$status = $data['status'];

try {
    // Check if the course belongs to the teacher
    $checkCourseStmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    // $sql = "SELECT id FROM courses WHERE id = $courseId AND teacher_id = $teacherId";
    if (!$checkCourseStmt) {
        throw new Exception('Failed to prepare course check statement: ' . $conn->error);
    }
    
    $checkCourseStmt->bind_param("ii", $courseId, $teacherId);
    if (!$checkCourseStmt->execute()) {
        throw new Exception('Failed to execute course check query: ' . $checkCourseStmt->error);
    }
    
    $courseResult = $checkCourseStmt->get_result();
    if ($courseResult->num_rows === 0) { //the query returned zero rows
        throw new Exception('You do not have permission to enroll students in this course');
    }
    
    // Check if the student exists
    $checkStudentStmt = $conn->prepare("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND r.name = 'student'");
    if (!$checkStudentStmt) {
        throw new Exception('Failed to prepare student check statement: ' . $conn->error);
    }
    
    $checkStudentStmt->bind_param("i", $studentId);
    if (!$checkStudentStmt->execute()) {
        throw new Exception('Failed to execute student check query: ' . $checkStudentStmt->error);
    }
    
    $studentResult = $checkStudentStmt->get_result();
    if ($studentResult->num_rows === 0) {
        throw new Exception('Student not found');
    }
    
    // Check if the student is already enrolled in the course
    $checkEnrollmentStmt = $conn->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ?");
    if (!$checkEnrollmentStmt) {
        throw new Exception('Failed to prepare enrollment check statement: ' . $conn->error);
    }
    
    $checkEnrollmentStmt->bind_param("ii", $courseId, $studentId);
    if (!$checkEnrollmentStmt->execute()) {
        throw new Exception('Failed to execute enrollment check query: ' . $checkEnrollmentStmt->error);
    }
    
    $enrollmentResult = $checkEnrollmentStmt->get_result();
    if ($enrollmentResult->num_rows > 0) {
        throw new Exception('Student is already enrolled in this course');
    }
    
    // Insert enrollment
    $insertStmt = $conn->prepare("INSERT INTO course_enrollments (course_id, student_id, status) VALUES (?, ?, ?)");
    if (!$insertStmt) {
        throw new Exception('Failed to prepare enrollment insert statement: ' . $conn->error);
    }
    
    $insertStmt->bind_param("iis", $courseId, $studentId, $status);
    if (!$insertStmt->execute()) {
        throw new Exception('Failed to execute enrollment insert query: ' . $insertStmt->error);
    }
    
    $enrollmentId = $conn->insert_id;
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Student enrolled successfully',
        'enrollment_id' => $enrollmentId
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error enrolling student: ' . $e->getMessage()
    ]);
} finally {
    // Close statements if they exist
    if (isset($checkCourseStmt) && $checkCourseStmt instanceof mysqli_stmt) {
        $checkCourseStmt->close();
    }
    if (isset($checkStudentStmt) && $checkStudentStmt instanceof mysqli_stmt) {
        $checkStudentStmt->close();
    }
    if (isset($checkEnrollmentStmt) && $checkEnrollmentStmt instanceof mysqli_stmt) {
        $checkEnrollmentStmt->close();
    }
    if (isset($insertStmt) && $insertStmt instanceof mysqli_stmt) {
        $insertStmt->close();
    }
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>