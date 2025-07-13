<?php
// Prevent any output before headers
ob_start();

// Disable error display and enable error logging
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
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    try {
        ob_end_clean(); // Clear any output buffer
        $response = [
            'status' => $status,
            'message' => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        // Ensure proper JSON encoding
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            error_log("JSON encoding error: " . json_last_error_msg());
            $json = json_encode([
                'status' => 'error',
                'message' => 'Error encoding response'
            ]);
        }
        
        echo $json;
        exit();
    } catch (Exception $e) {
        error_log("Error in sendJsonResponse: " . $e->getMessage());
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

try {
    // Include database configuration
    require_once __DIR__ . '/../../config/db_config.php';
    session_start();
    
    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: No connection object");
        sendJsonResponse('error', 'Database connection failed');
    }
    if ($conn->connect_error) {
        error_log("Database connection error: " . $conn->connect_error);
        sendJsonResponse('error', 'Database connection error');
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        sendJsonResponse('error', 'User not logged in');
    }

    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];

    // Get query parameters
    $slipId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : null;
    $examType = isset($_GET['examType']) ? $_GET['examType'] : null;
    $semester = isset($_GET['semester']) ? $_GET['semester'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;

    // Build the query based on provided parameters and user role
    $query = "SELECT rs.*, 
                     rs.roll_number,
                     rs.exam_type,
                     rs.semester,
                     rs.file_path,
                     rs.status,
                     rs.comments,
                     rs.upload_date,
                     rs.verification_date
              FROM roll_number_slips rs 
              WHERE 1=1";
    $params = [];
    $types = "";

    // Apply role-based restrictions
    if ($userRole === 'student') {
        // Students can only see their own slips (by roll number)
        // We need to get the student's roll number from student_details table
        $studentRollQuery = "SELECT sd.roll_number FROM student_details sd WHERE sd.user_id = ?";
        $stmt = $conn->prepare($studentRollQuery);
        if (!$stmt) {
            sendJsonResponse('error', 'Database error');
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendJsonResponse('error', 'Student roll number not found');
        }
        
        $studentData = $result->fetch_assoc();
        $studentRollNumber = $studentData['roll_number'];
        $stmt->close();
        
        $query .= " AND rs.roll_number = ?";
        $params[] = $studentRollNumber;
        $types .= "s";
    } elseif ($userRole === 'teacher') {
        // Teachers can see all slips
        // No additional restrictions
    } elseif ($userRole !== 'admin') {
        // Other roles not allowed
        sendJsonResponse('error', 'Unauthorized access');
    }

    // Apply filters
    if ($slipId) {
        $query .= " AND rs.id = ?";
        $params[] = $slipId;
        $types .= "i";
    }

    if ($studentId && ($userRole === 'teacher' || $userRole === 'admin')) {
        // For teachers/admins filtering by student ID, we need to get the roll number
        $studentRollQuery = "SELECT sd.roll_number FROM student_details sd WHERE sd.user_id = ?";
        $stmt = $conn->prepare($studentRollQuery);
        if (!$stmt) {
            sendJsonResponse('error', 'Database error');
        }
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $studentData = $result->fetch_assoc();
            $studentRollNumber = $studentData['roll_number'];
            $stmt->close();
            
            $query .= " AND rs.roll_number = ?";
            $params[] = $studentRollNumber;
            $types .= "s";
        }
        $stmt->close();
    }

    if ($examType) {
        $query .= " AND rs.exam_type = ?";
        $params[] = $examType;
        $types .= "s";
    }

    if ($semester) {
        $query .= " AND rs.semester = ?";
        $params[] = $semester;
        $types .= "s";
    }

    if ($status) {
        $query .= " AND rs.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY rs.upload_date DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare query: " . $conn->error);
        sendJsonResponse('error', 'Database error');
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Failed to execute query: " . $stmt->error);
        sendJsonResponse('error', 'Database error');
    }

    $result = $stmt->get_result();
    $rollNoSlips = [];

    while ($row = $result->fetch_assoc()) {
        $rollNoSlips[] = [
            'id' => $row['id'],
            'rollNumber' => $row['roll_number'],
            'examType' => $row['exam_type'],
            'semester' => $row['semester'],
            'filePath' => $row['file_path'],
            'status' => $row['status'],
            'comments' => $row['comments'],
            'uploadDate' => $row['upload_date'],
            'verificationDate' => $row['verification_date']
        ];
    }

    $stmt->close();

    // Send success response with roll number slip data
    sendJsonResponse('success', 'Roll number slips retrieved successfully', [
        'rollNoSlips' => $rollNoSlips
    ]);

} catch (Exception $e) {
    error_log("Error retrieving roll number slips: " . $e->getMessage());
    sendJsonResponse('error', $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>