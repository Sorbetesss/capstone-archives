<?php

include 'db_conn.php';

$student_id = $_GET['student_id'];

$stmt = $conn->prepare("SELECT * FROM student_grades WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}

$grades_data = $result->fetch_assoc();

if (!$grades_data) {
    echo "No grades data found for student ID $student_id.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $prelim = $_POST['prelim'];
    $midterm = $_POST['midterm'];
    $ffg = $_POST['ffg'];

    if (empty($prelim) || $prelim == 0) {
        $prelim = 0.0;
    }
    if (empty($midterm) || $midterm == 0) {
        $midterm = 0.0;
    }
    if (empty($ffg) || $ffg == 0) {
        $ffg = 0.0;
    }

    $stmt = $conn->prepare("UPDATE student_grades SET prelim = ?, midterm = ?, ffg = ? WHERE student_id = ? AND offercode = ?");
    $stmt->bind_param("dddsi", $prelim, $midterm, $ffg, $_GET['student_id'], $_GET['offercode']);

    if ($stmt->execute()) {
        header('Location: index3.php');
        exit;
    } else {
        echo 'Error: ' . $conn->error;
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
    <script src="script.js"></script>
</head>
<body>
    <div class="form-container">
        <h3>Edit Grades</h3>
        <form action="" method="post">
            <div class="mb-3">
                <label for="studentId" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="studentId" name="student_id" value="<?php echo $student_id; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="offercode" class="form-label">Offer Code</label>
                <input type="text" class="form-control" id="offercode" name="offercode" value="<?php echo $grades_data['offercode']; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="prelim" class="form-label">Prelim</label>
                <input type="number" class="form-control" id="prelim" name="prelim" value="<?php echo $grades_data['prelim']; ?>" step="0.01">
            </div>
            <div class="mb-3">
                <label for="midterm" class="form-label">Midterm</label>
                <input type="number" class="form-control" id="midterm" name="midterm" value="<?php echo $grades_data['midterm']; ?>" step="0.01">
            </div>
            <div class="mb-3">
                <label for="ffg" class="form-label">FFG</label>
                <input type="number" class="form-control" id="ffg" name="ffg" value="<?php echo $grades_data['ffg']; ?>" step="0.01">
            </div>
            <div class="btn-container">
                <button type ="button" class="btn btn-back" onclick="location.href='index3.php'">Back</button>
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>
</body>
</html>