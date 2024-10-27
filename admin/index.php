<?php
include 'db_conn.php';

// Handle form submission for adding new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $course = $_POST['course'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $insert_query = "INSERT INTO student (student_id, first_name, last_name, course, student_email, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssss", $student_id, $first_name, $last_name, $course, $email, $password);
    
    if ($stmt->execute()) {
        $success_message = "New student added successfully.";
    } else {
        $error_message = "Error adding student: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle search
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
    $query = "SELECT * FROM student WHERE student_id LIKE '%$search_term%' OR first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%' OR course LIKE '%$search_term%' OR student_email LIKE '%$search_term%'";
} else {
    $query = "SELECT * FROM student";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<html>
<head>
    <title>E-Grades Admin - Student Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #d3d3d3;
            height: 100vh;
            padding: 20px;
            width: 250px;
        }
        .sidebar h5 {
            margin-bottom: 20px;
        }
        .sidebar hr {
            border-top: 1px solid #000;
            margin: 10px 0;
        }
        .sidebar .nav-link {
            color: black;
            font-weight: bold;
            padding: 10px 0;
            text-decoration: none;
            display: block;
        }
        .sidebar .nav-link:hover {
            background-color: #b0b0b0;
        }
        .sidebar i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
            flex-grow: 1;
        }
        .form-container {
            background-color: #d3d3d3;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-container h5 {
            margin-bottom: 20px;
        }
        .form-container .btn {
            background-color: #007bff;
            color: white;
         }
        .table-container {
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h5>E-GRADES ADMIN</h5>
            <hr>
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php"><i class="fas fa-user-graduate"></i> Student Records</a>
                <a class="nav-link" href="index2.php"><i class="fas fa-chalkboard-teacher"></i> Instructor Records</a>
                <a class="nav-link" href="index3.php"><i class="fas fa-book"></i> Student Grades</a>
                <a class="nav-link" href="class_subjects.php"><i class="fas fa-book"></i> Class Subjects</a>
            </nav>
        </div>
        <div class="content">
            <div class="form-container">
                <h5>ADD STUDENT</h5>
                <?php
                if (isset($success_message)) {
                    echo "<div class='alert alert-success'>" . $success_message . "</div>";
                }
                if (isset($error_message)) {
                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                }
                ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID:</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="course" class="form-label">Course:</label>
                        <input type="text" class="form-control" id="course" name="course" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn" name="add_student">SAVE</button>
                </form>
            </div>
            <div class="search-bar">
                <form action="" method="post" class="d-flex w-100">
                    <input type="text" class="form-control" placeholder="Search records..." name="search_term">
                    <button class="btn btn-primary" name="search">SEARCH</button>
                </form>
            </div>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Course</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['student_id']; ?></td>
                                    <td><?php echo $row['first_name']; ?></td>
                                    <td><?php echo $row['last_name']; ?></td>
                                    <td><?php echo $row['course']; ?></td>
                                    <td><?php echo $row['student_email']; ?></td>
                                    <td><?php echo $row['password']; ?></td>
                                    <td>
                                        <a href="edit_student.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <a href="delete_student.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-danger" onclick ="return confirm('Are you sure you want to delete this student?')"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" style="text-align: center; vertical-align: middle; height: 100px; font-size: 24px; font-weight: bold;">No Records Found</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>