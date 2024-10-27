<?php
include 'db_conn2.php';

if (!isset($_SESSION)) {
    session_start();
}
if (isset($_POST['login'])) {
    $prof_id = $_POST['prof_id'];
    $password = $_POST['password'];

    if (empty($prof_id) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $query = "SELECT * FROM teacher WHERE prof_id = '$prof_id' AND password = '$password'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = $result->fetch_assoc();
            $prof_id = $row['prof_id'];
            $_SESSION['prof_id'] = $prof_id; // Set the session variable
            header("Location: teacher_dashboard.php");
            exit;
        } 
    }
}
?>

<html>
<head>
    <title>
        Sign-In
    </title>
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" rel="stylesheet"/>
    <style>
        body {
            background: url("../assets/jpaul.jpg") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            width: 300px;
            border: 1px solid #ccc;
        }
        .card-header {
            background-color: #3b6ef5;
            color: white;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .card-body {
            text-align: center;
        }
        .card-body img {
            width: 100px;
            margin-bottom: 20px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #3b6ef5;
            border: none;
        }
        .alert {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px;
            background-color: #f44336;
            color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <?php if (isset($error)) { ?>
        <div class="alert" id="error-message">
            <?php echo $error; ?>
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('error-message').style.display = 'none';
            }, 5000);
        </script>
    <?php } ?>
    <div class="card">
        <div class="card-header">
            FACULTY
        </div>
        <div class="card-body">
            <img alt="jpcean" height="100" src="../assets/jp.png" width="100"/>
            <form action="" method="post">
                <div class="mb-3">
                    <input class="form-control" placeholder="Instructor ID" type="text" name="prof_id" required>
                </div>
                <div class="mb-3">
                    <input class="form-control" placeholder="Password" type="password" name="password" required>
                </div>
                <button class="btn btn-primary" type="submit" name="login">
                    Log in
                </button>
            </form>
        </div>
    </div>
</body>
</html>