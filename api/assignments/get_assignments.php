<?php
// Prevent any output before headers
ob_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include database configuration
require_once '../../config/db_config.php';

// Start session
session_start();

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse('error', 'User not logged in');
}

try {
    // Get user role
    $userId = $_SESSION['user_id'];
    $roleQuery = "SELECT r.name as role_name FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.id = ?";
    
    if (!$stmt = $conn->prepare($roleQuery)) {
        throw new Exception("Error preparing role query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Error executing role query: " . $stmt->error);
    }
    
    $roleResult = $stmt->get_result();
    if (!$roleData = $roleResult->fetch_assoc()) {
        throw new Exception("User role not found");
    }
    
    $userRole = $roleData['role_name'];

    // Base query for assignments
    $query = "SELECT a.*, 
              CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
              CASE 
                  WHEN a.due_date < NOW() THEN 'overdue'
                  ELSE 'pending'
              END as status
              FROM assignments a
              JOIN users u ON a.teacher_id = u.id";

    // Modify query based on user role
    if ($userRole === 'teacher') {
        $query .= " WHERE a.teacher_id = ?";
    } elseif ($userRole === 'student') {
        $query .= " WHERE 1=1"; // Show all assignments to students
    }

    $query .= " ORDER BY a.due_date DESC";

    if (!$stmt = $conn->prepare($query)) {
        throw new Exception("Error preparing assignments query: " . $conn->error);
    }
    
    if ($userRole === 'teacher') {
        $stmt->bind_param("i", $userId);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error executing assignments query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        // Format file path to be relative to the web root
        $filePath = $row['file_path'];
        if ($filePath) {
            // Remove any existing sync_scholar prefix to avoid duplication
            $filePath = str_replace('sync_scholar/', '', $filePath);
        }

        // Get submission status if it exists
        $submissionQuery = "SELECT status FROM assignment_submissions 
                          WHERE assignment_id = ? AND student_id = ?";
        $submissionStmt = $conn->prepare($submissionQuery);
        if ($submissionStmt) {
            $submissionStmt->bind_param("ii", $row['id'], $userId);
            $submissionStmt->execute();
            $submissionResult = $submissionStmt->get_result();
            if ($submission = $submissionResult->fetch_assoc()) {
                $row['status'] = $submission['status'];
            }
            $submissionStmt->close();
        }

        $assignments[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'due_date' => $row['due_date'],
            'teacher_name' => $row['teacher_name'],
            'status' => $row['status'],
            'file_path' => $filePath,
            'created_at' => $row['created_at']
        ];
    }

    sendJsonResponse('success', 'Assignments retrieved successfully', ['assignments' => $assignments]);

} catch (Exception $e) {
    error_log("Error in get_assignments.php: " . $e->getMessage());
    sendJsonResponse('error', 'Error fetching assignments: ' . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// Clear any output buffer
ob_end_flush();
?> 