<?php
include 'db_conn2.php';

if (isset($_POST['studentId'])) {
    $studentId = $_POST['studentId'];
    $query = "SELECT first_name, last_name, course FROM student WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(array("error" => "Student not found"));
    }
}
?>