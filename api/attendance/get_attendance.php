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
    
    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: No connection object");
        sendJsonResponse('error', 'Database connection failed');
    }
    if ($conn->connect_error) {
        error_log("Database connection error: " . $conn->connect_error);
        sendJsonResponse('error', 'Database connection error');
    }

    // Get query parameters
    $courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : null;
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : null;

    // Validate date format if provided
    if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        sendJsonResponse('error', 'Invalid date format. Use YYYY-MM-DD');
    }

    // Build the query based on provided parameters
    $query = "SELECT a.*, u.first_name, u.last_name, sd.roll_number 
              FROM attendance a 
              JOIN users u ON a.student_id = u.id 
              JOIN student_details sd ON u.id = sd.user_id 
              WHERE 1=1";
    $params = [];
    $types = "";

    if ($courseId) {
        $query .= " AND a.course_id = ?";
        $params[] = $courseId;
        $types .= "i";
    }

    if ($date) {
        $query .= " AND a.date = ?";
        $params[] = $date;
        $types .= "s";
    }

    if ($studentId) {
        $query .= " AND a.student_id = ?";
        $params[] = $studentId;
        $types .= "i";
    }

    $query .= " ORDER BY a.date DESC, sd.roll_number ASC";

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
    $attendance = [];

    while ($row = $result->fetch_assoc()) {
        $attendance[] = [
            'id' => $row['id'],
            'studentId' => $row['student_id'],
            'studentName' => $row['first_name'] . ' ' . $row['last_name'],
            'rollNumber' => $row['roll_number'],
            'courseId' => $row['course_id'],
            'date' => $row['date'],
            'status' => $row['status'],
            'remarks' => $row['remarks'],
            'markedBy' => $row['marked_by']
        ];
    }

    $stmt->close();

    // Send success response with attendance data
    sendJsonResponse('success', 'Attendance records retrieved successfully', [
        'attendance' => $attendance
    ]);

} catch (Exception $e) {
    error_log("Error retrieving attendance: " . $e->getMessage());
    sendJsonResponse('error', $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 