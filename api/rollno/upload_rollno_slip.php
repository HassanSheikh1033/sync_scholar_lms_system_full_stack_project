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

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Only teachers can upload roll number slips',
        'debug' => [
            'user_id' => $_SESSION['user_id'] ?? 'not set',
            'role' => $_SESSION['role'] ?? 'not set'
        ]
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];
error_log("Teacher ID: " . $teacherId);

// Check if file was uploaded
if (!isset($_FILES['rollNoSlip'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'No file uploaded',
        'debug' => ['files' => $_FILES]
    ]);
    exit();
}

// Get form data
$roll_number= $_POST['roll_number'] ?? '';
$examType = $_POST['examType'] ?? '';
$semester = $_POST['semester'] ?? '';
$comments = $_POST['comments'] ?? '';

// Debug logging
error_log("POST data received: " . print_r($_POST, true));
error_log("FILES data received: " . print_r($_FILES, true));
error_log("Roll number: '$roll_number', Exam type: '$examType', Semester: '$semester'");

// Validate form data
if (empty($roll_number) || empty($examType) || empty($semester)) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Roll number, exam type and semester are required',
        'debug' => [
            'post' => $_POST,
            'roll_number' => $roll_number,
            'examType' => $examType,
            'semester' => $semester
        ]
    ]);
    exit();
}

$file = $_FILES['rollNoSlip'];
error_log("File data: " . print_r($file, true));

// Validate file type
if ($file['type'] !== 'application/pdf') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid file type. Please upload a PDF file',
        'debug' => ['file_type' => $file['type']]
    ]);
    exit();
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'File size exceeds the limit of 5MB',
        'debug' => ['file_size' => $file['size']]
    ]);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = '../../uploads/rollno_slips/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$fileName = 'rollno_' . uniqid() . '.pdf';
$filePath = $uploadDir . $fileName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to upload file',
        'debug' => ['error' => error_get_last()]
    ]);
    exit();
}

// Store relative path in database
$dbFilePath = 'uploads/rollno_slips/' . $fileName;

try {
    // Insert roll number slip record with roll_number directly
    $stmt = $conn->prepare("
        INSERT INTO roll_number_slips 
        (roll_number, exam_type, semester, file_path, comments, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("sssss", $roll_number, $examType, $semester, $dbFilePath, $comments);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert record: " . $stmt->error);
    }

    $slipId = $stmt->insert_id;
    $stmt->close();

    // Return success response
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Roll number slip uploaded successfully for roll number: ' . $roll_number,
        'data' => [
            'id' => $slipId,
            'file_path' => $dbFilePath,
            'roll_number' => $roll_number
        ]
    ]);

} catch (Exception $e) {
    // Delete uploaded file if database insertion fails
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Return error response
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error uploading roll number slip',
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