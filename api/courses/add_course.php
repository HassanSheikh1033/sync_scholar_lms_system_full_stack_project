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
        'message' => 'Only teachers can add courses'
    ]);
    exit();
}

$teacherId = $_SESSION['user_id'];

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['course_code']) || !isset($data['course_name']) || !isset($data['semester']) || !isset($data['credits'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields'
    ]);
    exit();
}

$courseCode = $data['course_code'];
$courseName = $data['course_name'];
$description = $data['description'] ?? '';
$semester = intval($data['semester']);
$credits = intval($data['credits']);

try {
    // Check if course code already exists
    $checkStmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
    if (!$checkStmt) {
        throw new Exception('Failed to prepare check statement: ' . $conn->error);
    }
    
    $checkStmt->bind_param("s", $courseCode);
    if (!$checkStmt->execute()) {
        throw new Exception('Failed to execute check query: ' . $checkStmt->error);
    }
    
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        throw new Exception('Course code already exists');
    }
    
    // Insert new course
    $insertStmt = $conn->prepare("INSERT INTO courses (course_code, course_name, description, teacher_id, semester, credits) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$insertStmt) {
        throw new Exception('Failed to prepare insert statement: ' . $conn->error);
    }
    
    $insertStmt->bind_param("sssiii", $courseCode, $courseName, $description, $teacherId, $semester, $credits);
    if (!$insertStmt->execute()) {
        throw new Exception('Failed to execute insert query: ' . $insertStmt->error);
    }
    
    $courseId = $conn->insert_id;
    
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Course added successfully',
        'course_id' => $courseId
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error adding course: ' . $e->getMessage()
    ]);
} finally {
    // Close statements if they exist
    if (isset($checkStmt) && $checkStmt instanceof mysqli_stmt) {
        $checkStmt->close();
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


<!-- ob_start():	Starts output buffering so nothing is sent before headers -->
 <!-- ini_set('display_errors', 0);	Don’t show raw errors to the client (security). -->
<!-- ini_set('log_errors', 1);	Enable error logging to a file. -->
<!-- ini_set('error_log', __DIR__ . '/../../error.log');	Set the path for the error log file. -->
 <!-- error_reporting(E_ALL);	Still capture every level of PHP error in the log. -->

<!-- header('Content-Type: application/json; charset=utf-8');	Set the content type to JSON. -->
<!-- header('Access-Control-Allow-Origin: *');	Allow requests from any origin. -->
<!-- header('Access-Control-Allow-Methods: POST');	Allow only POST requests. -->
<!-- header('Access-Control-Allow-Headers: Content-Type');	Allow Content-Type header in requests. -->
<!-- if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')	Handle preflight requests for CORS.PHP’s $_SERVER['REQUEST_METHOD'] tells you which HTTP method is being used (GET, POST, OPTIONS, etc.). -->
<!-- ob_end_clean();	Clear the output buffer to prevent any output before headers. -->
<!-- http_response_code(200);	Return a 200 OK response for OPTIONS requests. -->
<!-- exit();	Exit the script after handling OPTIONS request. -->
<!-- require_once '../../config/db_config.php';	Include the database configuration file. -->
<!-- session_start();	Start the session to access session variables. -->
<!-- if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher')	Ensure the user is logged in and is a teacher. -->
<!-- ob_end_clean();	Clear the output buffer to prevent any output before headers. -->
<!-- echo json_encode(['status' => 'error', 'message' => 'Only teachers can add courses']);	Return an error response if not a teacher. -->
<!-- exit();	Exit the script after sending the error response. -->
<!-- $teacherId = $_SESSION['user_id'];	Get the teacher's user ID from the session. -->
<!-- $json = file_get_contents('php://input');	Get the raw JSON input from the request body. -->
<!-- $data = json_decode($json, true);	Decode the JSON input into an associative array. -->
<!-- if (!isset($data['course_code']) || !isset($data['course_name']) || !isset($data['semester']) || !isset($data['credits']))	Validate required fields. -->
<!-- ob_end_clean();	Clear the output buffer to prevent any output before headers. -->
<!-- echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);	Return an error response if validation fails. -->
<!-- exit();	Exit the script after sending the error response. -->
<!-- $courseCode = $data['course_code'];	Get the course code from the input data. -->
<!-- $checkStmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");	Prepare a statement to check if the course code already exists. -->
<!-- $checkStmt->bind_param("s", $courseCode);	Bind the course code parameter to the prepared statement.bind_param() returns true (success) / false (failure)`. -->

<!-- if (!$checkStmt->execute())	Execute the check statement and handle errors. -->
 