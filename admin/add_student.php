<?php
// Include the database connection file
include 'db_conn.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $course = $_POST['course'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check for empty fields
    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($course) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check for duplicate data
        $query = "SELECT * FROM student WHERE student_id = '$student_id' OR student_email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $error = "Student ID or Email already exists.";
        } else {
            // Create a query to insert the new student record
            $query = "INSERT INTO student (student_id, first_name, last_name, course, student_email, password) VALUES ('$student_id', '$first_name', '$last_name', '$course', '$email', '$password')";

            // Execute the query
            if (mysqli_query($conn, $query)) {
                // If the query is successful, redirect to the main page
                header('Location: index.php');
                exit;
            } else {
                // If the query fails, display an error message
                $error = 'Error: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: #d3d3d3;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .form-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container .form-control {
            margin-bottom: 10px;
        }
        .form-container .btn-back {
            background-color: #d3d3d3;
            color: black;
            border: 1px solid #ccc;
            width: 45%;
        }
        .form-container .btn-save {
            background-color: #007bff;
            color: white;
            width: 45%;
        }
        .form-container .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Add new Student</h3>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="studentId" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="studentId" name="student_id">
            </div>
            <div class="mb-3">
                <label for="firstName" class="form-label">First name</label>
                <input type="text" class="form-control" id="firstName" name="first_name">
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name">
            </div>
            <div class="mb-3">
                <label for="course" class="form-label">Course</label>
                <select class="form-select" id="course " name="course">
                    <option value="BSIT">BS Information Technology</option>
                    <option value="BSED">BS Secondary Education</option>
                    <option value="BSN">BS Nursing</option>
                    <option value="BSHRM">BS Human Resource Management</option>
                    <option value="BSCRIM">BS Criminology</option>
                    <option value="BST">BS Tourism</option>
                    <option value="BS-GE">BS Geodetic Engineering</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="btn-container">
                <button type="button" class="btn btn-back" onclick="location.href='index.php'">Back</button>
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>
</body>
</html>