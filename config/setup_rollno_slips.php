<?php
require_once 'db_config.php';

try {
    // Create roll_number_slips table
    $sql = "CREATE TABLE IF NOT EXISTS roll_number_slips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        exam_type ENUM('midterm', 'final', 'special') NOT NULL,
        semester VARCHAR(50) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
        comments TEXT,
        uploaded_by INT,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        verified_by INT,
        verification_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
    )";

    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating roll_number_slips table: " . $conn->error);
    }

    // Add uploaded_by column if it doesn't exist (for existing tables)
    $checkColumn = "SHOW COLUMNS FROM roll_number_slips LIKE 'uploaded_by'";
    $result = $conn->query($checkColumn);
    
    if ($result->num_rows === 0) {
        $addColumn = "ALTER TABLE roll_number_slips ADD COLUMN uploaded_by INT AFTER comments";
        if ($conn->query($addColumn) === FALSE) {
            throw new Exception("Error adding uploaded_by column: " . $conn->error);
        }
        
        // Add foreign key constraint
        $addForeignKey = "ALTER TABLE roll_number_slips ADD CONSTRAINT fk_rollno_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL";
        if ($conn->query($addForeignKey) === FALSE) {
            // Foreign key might already exist, so we'll ignore this error
            error_log("Note: Foreign key constraint for uploaded_by might already exist");
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Roll number slips table created/updated successfully']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>