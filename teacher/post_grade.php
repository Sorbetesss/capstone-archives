<?php
include 'db_conn2.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $offer_code = $_POST['offer_code'];
    $grade_type = $_POST['grade_type'];
    $grade = $_POST['grade'];
    $semester_id = $_POST['semester_id']; // Add this line

    // Update the grade and mark it as posted
    $update_query = "UPDATE student_grades SET {$grade_type} = ?, {$grade_type}_posted = TRUE WHERE student_id = ? AND offercode = ? AND semester_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("disi", $grade, $student_id, $offer_code, $semester_id);
    $result = $update_stmt->execute();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Grade posted successfully']);
     } else {
        echo json_encode(['success' => false, 'message' => 'Error posting grade: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>