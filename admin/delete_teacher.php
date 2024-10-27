<?php
include 'db_conn.php';

if (isset($_GET['prof_id'])) {
    $prof_id = $_GET['prof_id'];

    // Delete the teacher record
    $stmt = $conn->prepare("DELETE FROM teacher WHERE prof_id = ?");
    $stmt->bind_param("i", $prof_id);
    $stmt->execute();

    if ($stmt->affected_rows == 0) {
        die("Query failed: " . $conn->error);
    } else {
        header("Location: index2.php");
        exit;
    }
}
?>