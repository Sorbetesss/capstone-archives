<?php

include 'db_conn.php';


$student_id = $_GET['student_id'];
$query = "SELECT * FROM student WHERE student_id = '$student_id'";
$result = mysqli_query($conn, $query);


if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$student_data = mysqli_fetch_assoc($result);


if (!$student_data) {
    die("Student data not found.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $course = $_POST['course'];
    $email = $_POST['email'];
    $password = $_POST['password'];


    $query = "UPDATE student SET first_name = '$first_name', last_name = '$last_name', course = '$course', student_email = '$email', password = '$password' WHERE student_id = '$student_id'";

    if (mysqli_query($conn, $query)) {
        
        header('Location: index.php');
        exit;
    } else {
        
        echo 'Error: ' . mysqli_error($conn);
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
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Edit Student Profile</h3>
        <form action="" method="post">
            <div class="mb-3">
                <label for="studentId" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="studentId" name="student_id" value="<?php echo $student_data['student_id']; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="firstName" class="form-label">First name</label>
                <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo $student_data['first_name']; ?>">
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo $student_data['last_name']; ?>">
            </div>
            <div class="mb-3">
                <label for="course" class="form-label">Course</label>
                <select class="form-select" id="course" name="course">
                    <option value="BSIT" <?php if ($student_data['course'] ?? '' == 'BSIT') echo 'selected'; ?>>BS Information Technology</option>
                    <option value="BSED" <?php if ($student_data['course'] ?? '' == 'BSED') echo 'selected'; ?>>BS Secondary Education</option>
                    <option value="BSN" <?php if ($student_data['course'] ?? '' == 'BSN') echo 'selected'; ?>>BS Nursing</option>
                    <option value="BSHRM" <?php if ($student_data['course'] ?? '' == 'BSHRM') echo 'selected'; ?>>BS Human Resource Management</option>
                    <option value="BSCRIM" <?php if ($student_data['course'] ?? '' == 'BSCRIM') echo 'selected'; ?>>BS Criminology</option>
                    <option value="BST" <?php if ($student_data['course'] ?? '' == 'BST') echo 'selected'; ?>>BS Tourism</option>
                    <option value="BS-GE" <?php if ($student_data['course'] ?? '' == 'BS-GE') echo 'selected'; ?>>BS Geodetic Engineering</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $student_data['student_email']; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo $student_data['password']; ?>">
            </div>
            <div class="btn-container">
                <button type="button" class="btn btn-back" onclick="location.href='index.php'">Back</button>
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>
</body>
</html>