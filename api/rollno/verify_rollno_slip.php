<?php
// Prevent any output before headers
ob_start();

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');
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

// Check if user is logged in and is a teacher or admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Only teachers or admins can verify roll number slips',
        'debug' => [
            'user_id' => $_SESSION['user_id'] ?? 'not set',
            'role' => $_SESSION['role'] ?? 'not set'
        ]
    ]);
    exit();
}

$verifierId = $_SESSION['user_id'];

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['slipId']) || !isset($data['status'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required parameters',
        'debug' => ['data' => $data]
    ]);
    exit();
}

$slipId = intval($data['slipId']);
$status = $data['status'];
$comments = $data['comments'] ?? null;

// Validate status
if (!in_array($status, ['verified', 'rejected', 'pending'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid status value',
        'debug' => ['status' => $status]
    ]);
    exit();
}

try {
    // Update roll number slip status
    $stmt = $conn->prepare("
        UPDATE roll_number_slips 
        SET status = ?, 
            comments = ?, 
            verified_by = ?, 
            verification_date = NOW() 
        WHERE id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("ssii", $status, $comments, $verifierId, $slipId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update record: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Roll number slip not found or no changes made");
    }

    $stmt->close();

    // Return success response
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Roll number slip status updated successfully'
    ]);

} catch (Exception $e) {
    // Return error response
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating roll number slip status',
        'debug' => [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>