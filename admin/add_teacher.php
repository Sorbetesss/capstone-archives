<?php
// Include the database connection file
include 'db_conn.php';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $prof_id = $_POST['prof_id'];
    $prof_fname = $_POST['prof_fname'];
    $prof_lname = $_POST['prof_lname'];
    $prof_email = $_POST['prof_email'];
    $password = $_POST['password'];

    // Validate the prof_id input
    if (!is_numeric($prof_id) || $prof_id <= 0) {
        $error = "Invalid professor ID. Please enter a positive integer.";
    } else {
        // Check for empty fields
        if (empty($prof_fname) || empty($prof_lname) || empty($prof_email) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            // Check for duplicate data
            $query = "SELECT * FROM teacher WHERE prof_id = '$prof_id' OR prof_email = '$prof_email'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $error = "Professor ID or Email already exists.";
            } else {
                // Create a query to insert the new teacher record
                $query = "INSERT INTO teacher (prof_id, prof_fname, prof_lname, prof_email, password) VALUES ('$prof_id', '$prof_fname', '$prof_lname', '$prof_email', '$password')";

                // Execute the query
                if (mysqli_query($conn, $query)) {
                    // If the query is successful, redirect to the main page
                    header('Location: index2.php');
                    exit;
                } else {
                    // If the query fails, display an error message
                    $error = 'Error: ' . mysqli_error($conn);
                }
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
        <h3>Add new Teacher</h3>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="profId" class="form-label">Professor ID</label>
                <input type="number" class="form-control" id="profId" name="prof_id" required>
            </div>
            <div class="mb-3">
                <label for="profFname" class="form-label">First name</label>
                <input type="text" class="form-control" id="profFname" name="prof_fname" required>
            </div>
            <div class="mb-3">
                <label for="profLname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="profLname" name="prof_lname" required>
            </div>
            <div class="mb-3">
                <label for="profEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="profEmail" name="prof_email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="btn-container">
                <button type="button" class="btn btn-back" onclick="location.href='index2.php'">Back</button>
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>
</body>
</html>