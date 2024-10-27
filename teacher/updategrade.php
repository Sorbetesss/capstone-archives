<?php
// Include the database connection file
include 'db_conn2.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the grade from the form
    $student_id = $_POST['student_id'];
    $offer_code = $_POST['offer_code'];
    $grade_type = $_POST['grade_type'];
    $grade = $_POST['grade'];
    $posted = $_POST['posted'] ?? 0;

    // Update the grade in the database
    $query = "UPDATE student_grades SET $grade_type = '$grade', $grade_type . '_posted' = '$posted' WHERE student_id = '$student_id' AND offercode = '$offer_code'";
    mysqli_query($conn, $query);

    // Return a success message
    echo 'Grade saved successfully!';
}
?>