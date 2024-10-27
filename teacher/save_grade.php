<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_conn2.php';

// Log function
function logError($message) {
    file_put_contents('error.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $offer_code = $_POST['offer_code'] ?? '';
    $grade_type = $_POST['grade_type'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $semester_id = $_POST['semester_id'] ?? '';

    // Log received data
    logError("Received data: " . print_r($_POST, true));

    if (empty($student_id) || empty($offer_code) || empty($grade_type) || empty($grade) || empty($semester_id)) {
        logError("Missing required fields");
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Check if a grade entry already exists
    $check_query = "SELECT * FROM student_grades WHERE student_id = ? AND offercode = ? AND semester_id = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        logError("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    $check_stmt->bind_param("sis", $student_id, $offer_code, $semester_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing grade
        $query = "UPDATE student_grades SET `$grade_type` = ? WHERE student_id = ? AND offercode = ? AND semester_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            logError("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("dsis", $grade, $student_id, $offer_code, $semester_id);
    } else {
        // Insert new grade
        $query = "INSERT INTO student_grades (student_id, offercode, `$grade_type`, semester_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            logError("Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("ssds", $student_id, $offer_code, $grade, $semester_id);
    }

    if (!$stmt->execute()) {
        logError("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error saving grade: ' . $stmt->error]);
    } else {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Grade saved successfully']);
        } else {
            logError("No rows affected");
            echo json_encode(['success' => false, 'message' => 'Grade saved successfully']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}