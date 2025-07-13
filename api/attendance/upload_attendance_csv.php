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

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Only teachers can mark attendance',
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
if (!isset($_FILES['file'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'No file uploaded',
        'debug' => ['files' => $_FILES]
    ]);
    exit();
}

$file = $_FILES['file'];
error_log("File data: " . print_r($file, true));

// Validate file type
if ($file['type'] !== 'text/csv') {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid file type. Please upload a CSV file',
        'debug' => ['file_type' => $file['type']]
    ]);
    exit();
}

// Open the file
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error reading file',
        'debug' => ['error' => error_get_last()]
    ]);
    exit();
}

// Read headers
$headers = fgetcsv($handle);
if (!$headers) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error reading CSV headers',
        'debug' => ['error' => error_get_last()]
    ]);
    exit();
}

error_log("CSV Headers: " . print_r($headers, true));

// Validate headers
$requiredHeaders = ['course_id', 'student_id', 'status', 'remarks'];
$missingHeaders = array_diff($requiredHeaders, $headers);
if (!empty($missingHeaders)) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required columns: ' . implode(', ', $missingHeaders),
        'debug' => [
            'found_headers' => $headers,
            'missing_headers' => $missingHeaders
        ]
    ]);
    exit();
}

// Get header indices
$courseIdIndex = array_search('course_id', $headers);
$studentIdIndex = array_search('student_id', $headers);
$statusIndex = array_search('status', $headers);
$remarksIndex = array_search('remarks', $headers);

error_log("Header indices: " . print_r([
    'course_id' => $courseIdIndex,
    'student_id' => $studentIdIndex,
    'status' => $statusIndex,
    'remarks' => $remarksIndex
], true));

// Start transaction
$conn->begin_transaction();

try {
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $rowNumber = 1; // For tracking which row has errors

    // Process each row
    while (($data = fgetcsv($handle)) !== FALSE) {
        $rowNumber++;
        
        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }

        error_log("Processing row $rowNumber: " . print_r($data, true));

        // Get values
        $courseId = $data[$courseIdIndex];
        $studentId = $data[$studentIdIndex];
        $status = strtolower($data[$statusIndex]);
        $remarks = $data[$remarksIndex] ?? '';

        error_log("Row $rowNumber values: " . print_r([
            'course_id' => $courseId,
            'student_id' => $studentId,
            'status' => $status,
            'remarks' => $remarks
        ], true));

        // Validate status
        if (!in_array($status, ['present', 'absent', 'late'])) {
            $errors[] = "Row $rowNumber: Invalid status '$status' for student ID $studentId";
            $errorCount++;
            continue;
        }

        // Check if teacher is assigned to this course
        $teacherCheckStmt = $conn->prepare("
            SELECT id FROM courses 
            WHERE id = ? AND teacher_id = ?
        ");
        if (!$teacherCheckStmt) {
            throw new Exception("Row $rowNumber: Failed to prepare teacher check statement: " . $conn->error);
        }

        $teacherCheckStmt->bind_param("ii", $courseId, $teacherId);
        if (!$teacherCheckStmt->execute()) {
            throw new Exception("Row $rowNumber: Failed to execute teacher check: " . $teacherCheckStmt->error);
        }

        $teacherResult = $teacherCheckStmt->get_result();
        if ($teacherResult->num_rows === 0) {
            $errors[] = "Row $rowNumber: You are not assigned to course ID $courseId";
            $errorCount++;
            continue;
        }

        // Check if student exists and is enrolled in the course
        $stmt = $conn->prepare("
            SELECT u.id 
            FROM users u
            JOIN course_enrollments e ON u.id = e.student_id
            WHERE u.id = ? AND e.course_id = ? AND u.user_type = 'student'
        ");
        if (!$stmt) {
            throw new Exception("Row $rowNumber: Failed to prepare student check statement: " . $conn->error);
        }

        $stmt->bind_param("ii", $studentId, $courseId);
        if (!$stmt->execute()) {
            throw new Exception("Row $rowNumber: Failed to execute student check: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Row $rowNumber: Student ID $studentId is not enrolled in course ID $courseId";
            $errorCount++;
            continue;
        }

        // Insert attendance record
        $insertStmt = $conn->prepare("
            INSERT INTO attendance (course_id, student_id, status, remarks, marked_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if (!$insertStmt) {
            throw new Exception("Row $rowNumber: Failed to prepare insert statement: " . $conn->error);
        }

        $insertStmt->bind_param("iissi", $courseId, $studentId, $status, $remarks, $teacherId);
        
        if ($insertStmt->execute()) {
            $successCount++;
            error_log("Row $rowNumber: Successfully inserted attendance record");
        } else {
            $errors[] = "Row $rowNumber: Error inserting record for student ID $studentId in course ID $courseId: " . $insertStmt->error;
            $errorCount++;
        }
    }

    // If there were any errors, rollback
    if ($errorCount > 0) {
        throw new Exception("Errors occurred during upload");
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => "Successfully uploaded $successCount attendance records"
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();

    // Return error response
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error uploading attendance data',
        'details' => $errors,
        'debug' => [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    // Close file and statements
    if (isset($handle)) {
        fclose($handle);
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($insertStmt) && $insertStmt instanceof mysqli_stmt) {
        $insertStmt->close();
    }
    if (isset($teacherCheckStmt) && $teacherCheckStmt instanceof mysqli_stmt) {
        $teacherCheckStmt->close();
    }
    // Close connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>

