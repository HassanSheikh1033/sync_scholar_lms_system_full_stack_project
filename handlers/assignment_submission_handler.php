<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/db_config.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input data
    $assignmentId = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
    $submissionText = isset($_POST['submission_text']) ? $conn->real_escape_string($_POST['submission_text']) : '';
    $studentId = $_SESSION['user_id'];

    // Validate assignment ID
    if (empty($assignmentId)) {
        throw new Exception('Assignment ID is required');
    }

    // Check if assignment exists and is not past due date
    $stmt = $conn->prepare("
        SELECT a.*, sa.id as submission_id, sa.status 
        FROM assignments a 
        LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.student_id = ?
        WHERE a.id = ?
    ");
    $stmt->bind_param("ii", $studentId, $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Assignment not found');
    }

    $assignment = $result->fetch_assoc();
    $stmt->close();

    // Check if assignment is past due date
    $dueDate = new DateTime($assignment['due_date']);
    $now = new DateTime();
    $status = 'submitted';
    
    if ($now > $dueDate) {
        $status = 'late';
    }

    // Handle file upload if present
    $filePath = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/submissions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileExtension = strtolower(pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed types: PDF, DOC, DOCX, TXT');
        }

        $fileName = uniqid('submission_') . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file');
        }

        $filePath = 'uploads/submissions/' . $fileName;
    }

    // Start transaction
    $conn->begin_transaction();

    if (isset($assignment['submission_id'])) {
        // Update existing submission
        $stmt = $conn->prepare("
            UPDATE student_assignments 
            SET submission_file = COALESCE(?, submission_file),
                submission_text = COALESCE(?, submission_text),
                submission_date = CURRENT_TIMESTAMP,
                status = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $filePath, $submissionText, $status, $assignment['submission_id']);
    } else {
        // Create new submission
        $stmt = $conn->prepare("
            INSERT INTO student_assignments 
            (assignment_id, student_id, submission_file, submission_text, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $assignmentId, $studentId, $filePath, $submissionText, $status);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to submit assignment: ' . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Assignment submitted successfully',
        'submission_status' => $status
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?> 