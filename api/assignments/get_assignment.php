<?php
header('Content-Type: application/json');
require_once '../../config/db_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Check if assignment ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Assignment ID not provided']);
    exit();
}

try {
    $assignmentId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    // Get assignment details with teacher information
    $query = "SELECT a.*, 
              CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
              s.id as submission_id,
              s.submission_file,
              s.submission_date,
              s.grade,
              s.feedback,
              s.status as submission_status
              FROM assignments a
              JOIN users u ON a.teacher_id = u.id
              LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
              WHERE a.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Format file path to be relative to the web root
        $filePath = $row['file_path'];
        if ($filePath) {
            // Remove any existing sync_scholar prefix to avoid duplication
            $filePath = str_replace('sync_scholar/', '', $filePath);
            $filePath = 'sync_scholar/' . $filePath;
        }

        $assignment = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'due_date' => $row['due_date'],
            'teacher_name' => $row['teacher_name'],
            'file_path' => $filePath,
            'created_at' => $row['created_at']
        ];

        // Add submission details if they exist
        if ($row['submission_id']) {
            // Format submission file path
            $submissionFilePath = $row['submission_file'];
            if ($submissionFilePath) {
                // Remove any existing sync_scholar prefix to avoid duplication
                $submissionFilePath = str_replace('sync_scholar/', '', $submissionFilePath);
                $submissionFilePath = 'sync_scholar/' . $submissionFilePath;
            }

            $assignment['submission'] = [
                'id' => $row['submission_id'],
                'submission_file' => $submissionFilePath,
                'submission_date' => $row['submission_date'],
                'grade' => $row['grade'],
                'feedback' => $row['feedback'],
                'status' => $row['submission_status']
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'assignment' => $assignment
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Assignment not found'
        ]);
    }

} catch (Exception $e) {
    error_log("Error in get_assignment.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching assignment: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 


<!-- $assignmentId = $_GET['id'];          // from the URL: /api/assignment?id=123
$userId       = $_SESSION['user_id']; // current student -->
