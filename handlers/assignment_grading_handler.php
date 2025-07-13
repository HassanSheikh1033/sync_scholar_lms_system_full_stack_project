<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
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
    $submissionId = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
    $marks = isset($_POST['marks']) ? floatval($_POST['marks']) : -1;
    $feedback = isset($_POST['feedback']) ? $conn->real_escape_string($_POST['feedback']) : '';
    $teacherId = $_SESSION['user_id'];

    // Validate required fields
    if (empty($submissionId) || $marks < 0) {
        throw new Exception('Submission ID and marks are required');
    }

    // Start transaction
    $conn->begin_transaction();

    // Verify teacher owns this assignment
    $stmt = $conn->prepare("
        SELECT a.total_marks 
        FROM student_assignments sa
        JOIN assignments a ON sa.assignment_id = a.id
        WHERE sa.id = ? AND a.teacher_id = ?
    ");
    $stmt->bind_param("ii", $submissionId, $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid submission or unauthorized access');
    }

    $assignment = $result->fetch_assoc();
    $stmt->close();

    // Validate marks are within range
    if ($marks > $assignment['total_marks']) {
        throw new Exception('Marks cannot exceed total marks for the assignment');
    }

    // Update submission with grades
    $stmt = $conn->prepare("
        UPDATE student_assignments 
        SET marks_obtained = ?,
            feedback = ?,
            status = 'graded'
        WHERE id = ?
    ");
    $stmt->bind_param("dsi", $marks, $feedback, $submissionId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update grades: ' . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Assignment graded successfully'
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