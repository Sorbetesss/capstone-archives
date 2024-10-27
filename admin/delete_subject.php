<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM class_subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: class_subjects.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>