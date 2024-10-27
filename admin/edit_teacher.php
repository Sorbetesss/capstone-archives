<?php
// Include the database connection file
include 'db_conn.php';

// Get the prof_id from the URL
$prof_id = $_GET['prof_id'];

// Query to retrieve the teacher record
$query = "SELECT * FROM teacher WHERE prof_id = '$prof_id'";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Get the teacher record
$row = mysqli_fetch_assoc($result);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $prof_id = $_POST['prof_id' ];
    $prof_fname = $_POST['prof_fname'];
    $prof_lname = $_POST['prof_lname'];
    $prof_email = $_POST['prof_email'];
    $password = $_POST['password'];

    // Check for empty fields
    if (empty($prof_id) || empty($prof_fname) || empty($prof_lname) || empty($prof_email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Create a query to update the teacher record
        $query = "UPDATE teacher SET prof_fname = '$prof_fname', prof_lname = '$prof_lname', prof_email = '$prof_email', password = '$password' WHERE prof_id = '$prof_id'";

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
        <h3>Edit Instructor Records</h3>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="profId" class="form-label">Professor ID</label>
                <input type="text" class="form-control" id="profId" name="prof_id" value="<?php echo $row['prof_id']; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="profFname" class="form-label">First name</label>
                <input type="text" class="form-control" id="profFname" name="prof_fname" value="<?php echo $row['prof_fname']; ?>">
            </div>
            <div class="mb-3">
                <label for="profLname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="profLname" name="prof_lname" value="<?php echo $row['prof_lname']; ?>">
            </div>
            <div class="mb-3">
                <label for="profEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="profEmail" name="prof_email" value="<?php echo $row['prof_email']; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo $row['password']; ?>">
            </div>
            <div class="btn-container">
                <button type="button" class="btn btn-back" onclick="location.href='index2.php'">Back >
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>
</body>
</html>