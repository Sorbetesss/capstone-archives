<?php
include 'db_conn2.php';

$stmt = $conn->prepare("SELECT class_code FROM class_subjects ORDER BY class_code");
$stmt->execute();
$result = $stmt->get_result();

$class_codes = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($class_codes);