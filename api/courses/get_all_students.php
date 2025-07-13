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
        'message' => 'Only teachers can access student data'
    ]);
    exit();
}

try {
    // Get all students
    $query = "SELECT u.id, u.username, u.first_name, u.last_name, u.email 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE r.name = 'student' 
             ORDER BY u.first_name, u.last_name";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare students statement: ' . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute students query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get students result: ' . $stmt->error);
    }

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'students' => $students
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching students: ' . $e->getMessage()
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