<?php
include 'db_conn.php';

if (isset($_GET['student_id']) && isset($_GET['offercode']) && isset($_GET['semester_id'])) {
    $student_id = $_GET['student_id'];
    $offercode = $_GET['offercode'];
    $semester_id = $_GET['semester_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete the grade
        $query = "DELETE FROM student_grades WHERE student_id = ? AND offercode = ? AND semester_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $student_id, $offercode, $semester_id);
        $stmt->execute();

        // Delete the course enrollment
        $query = "DELETE FROM course WHERE student_id = ? AND offercode = ? AND semester_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $student_id, $offercode, $semester_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        header("Location: index3.php");
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error deleting record: " . $e->getMessage();
    }

    $stmt->close();
} else {
    echo "Invalid parameters";
}

$conn->close();
?>