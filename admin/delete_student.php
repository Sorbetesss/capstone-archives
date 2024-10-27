<?php
include 'db_conn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Delete grades associated with the student
    $query = "DELETE FROM student_grades WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    echo "Deleting grades for student_id = $student_id<br>";
    if (!$stmt->execute()) {
        die("Query failed: " . $conn->error);
    }

    // Delete course records associated with the student
    $query = "DELETE FROM course WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    echo "Deleting course records for student_id = $student_id<br>";
    if (!$stmt->execute()) {
        die("Query failed: " . $conn->error);
    }

    // Delete the student record
    $query = "DELETE FROM student WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    echo "Deleting student record for student_id = $student_id<br>";
    if (!$stmt->execute()) {
        die("Query failed: " . $conn->error);
    }

    header("Location: index.php");
    exit;
}
?>