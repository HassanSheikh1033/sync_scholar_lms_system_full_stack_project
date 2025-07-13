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

// Check if enrollment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Enrollment ID is required'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];
$enrollmentId = $_GET['id'];

try {
    // Get enrollment details
    $query = "SELECT e.* 
              FROM course_enrollments e 
              JOIN courses c ON e.course_id = c.id 
              WHERE e.id = ? AND c.teacher_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare enrollment statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $enrollmentId, $teacherId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute enrollment query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get enrollment result: ' . $stmt->error);
    }

    if ($result->num_rows === 0) {
        throw new Exception('Enrollment not found or you do not have permission to view it');
    }

    $enrollment = $result->fetch_assoc();
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'enrollment' => $enrollment
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching enrollment: ' . $e->getMessage()
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