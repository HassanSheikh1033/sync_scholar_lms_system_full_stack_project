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
        'message' => 'Only teachers can access enrollment data'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];

try {
    // Build query based on filters
    $query = "SELECT e.*, c.course_code, c.course_name, CONCAT(u.first_name, ' ', u.last_name) as student_name 
              FROM course_enrollments e 
              JOIN courses c ON e.course_id = c.id 
              JOIN users u ON e.student_id = u.id 
              WHERE c.teacher_id = ?";
    
    $params = [$teacherId];
    $types = "i";
    
    // Add course filter if provided
    if (isset($_GET['courseId']) && !empty($_GET['courseId'])) {
        $query .= " AND e.course_id = ?";
        $params[] = $_GET['courseId'];
        $types .= "i";
    }
    
    // Add status filter if provided
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $query .= " AND e.status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }
    
    $query .= " ORDER BY c.course_name, u.first_name, u.last_name";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare enrollments statement: ' . $conn->error);
    }
    
    // Bind parameters dynamically
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute enrollments query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get enrollments result: ' . $stmt->error);
    }

    $enrollments = [];
    while ($row = $result->fetch_assoc()) {
        $enrollments[] = $row;
    }
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'enrollments' => $enrollments
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching enrollments: ' . $e->getMessage()
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