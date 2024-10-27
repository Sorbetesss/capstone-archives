<?php
include 'db_conn.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_code = $_POST['class_code'];
    $description = $_POST['description'];

    $sql = "INSERT INTO class_subjects (class_code, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $class_code, $description);

    if ($stmt->execute()) {
        $success_message = "Subject added successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all subjects
$sql = "SELECT * FROM class_subjects ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<html>
<head>
    <title>E-Grades Admin - Class Subjects</title>
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
            <h5>ADD SUBJECT</h5>
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
                        <label for="class_code" class="form-label">Class Code:</label>
                        <input type="text" class="form-control" id="class_code" name="class_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn">SAVE</button>
                </form>
            </div>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="3">ALL REGISTERED SUBJECTS</th>
                        </tr>
                        <tr>
                            <th>Class Code</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['class_code']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td><a href='delete_subject.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this subject?\")'><i class='fas fa-trash-alt'></i></a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No subjects found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>