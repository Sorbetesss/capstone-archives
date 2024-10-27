<?php
include 'db_conn2.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['prof_id'])) {
    header("Location: facultylog.php");
    exit;
}
if (isset($_POST['post_grade'])) {
    // Update the posted status
    $stmt = $conn->prepare("UPDATE student_grades SET posted = 1 WHERE student_id = ? AND offercode = ?");
    $stmt->bind_param("is", $student_id, $offercode);
    $stmt->execute();

    header("Location: teacher_dashboard.php");
    exit;
}

$prof_id = $_SESSION['prof_id'];

$stmt = $conn->prepare("SELECT * FROM teacher WHERE prof_id = ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    $prof_name = $row['prof_fname'] . ' ' . $row['prof_lname'];
} else {
    header("Location: facultylog.php");
    exit;
}

// Get the course information
$stmt = $conn->prepare("SELECT * FROM course WHERE prof_id = ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    $course = $row['offercode']; // Define $course here
} else {
    $course = ''; // Set a default value if no course is found
}

$student_id = $_GET['student_id'];
$offercode = $_GET['offercode'];
$grade_type = $_GET['grade_type'];

$stmt = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    $student_name = $row['first_name'] . ' ' . $row['last_name'];
} else {
    header("Location: facultylog.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM student_grades WHERE student_id = ? AND offercode = ?");
$stmt->bind_param("is", $student_id, $offercode);
$stmt->execute();
$result = $stmt->get_result();

 if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    $prelim = $row['prelim'] ?? '';
    $midterm = $row['midterm'] ?? '';
    $final = $row['final'] ?? '';
    $ffg = $row['ffg'] ?? '';
    $posted = $row['posted'] ?? 0; // Define $posted here
    $permitted = $row['permitted'] ?? 0;
} else {
    $prelim = '';
    $midterm = '';
    $final = '';
    $ffg = '';
    $posted = 0; // Define $posted here
    $permitted = 0;
}

// Check if the grade is posted
if ($posted == 1) {
    $disabled = 'disabled';
} else {
    $disabled = '';
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
        .card {
            width: 400px;
            border: 1px solid #000;
        }
        .card-header {
            background-color: #3f51b5;
            color: white;
            text-align: center;
            font-weight: bold;
        }
        .form-control::placeholder {
            color: #b0b0b0;
        }
        .btn-back, .btn-save, .btn-post {
            width: 100px;
        }
        .btn-back, .btn-save {
            background-color: #e0e0e0;
            color: black;
        }
        .btn-post {
            background-color: #3f51b5;
            color: white;
        }
        .form-check-input {
            transform: scale(1.5);
        }
        .form-check {
            margin-left : 10px;
        }
    </style >
</head>
<body>
    <div class="card">
        <div class="card-header">
            EDIT STUDENT GRADE
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <p class="mb-0">Name: <?php echo $student_name; ?></p>
                    <p>Student ID: <?php echo $student_id; ?></p>
                </div>
                <div class="col text-end">
                    <p class="mb-0">Course: <?php echo $course; ?></p>
                </div>
            </div>
            <form action="save_grade.php" method="post">
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                <input type="hidden" name="offercode" value="<?php echo $offercode; ?>">
                <input type="hidden" name="grade_type" value="<?php echo $grade_type; ?>">
                <input type="hidden" name="posted" value="<?php echo $posted; ?>">
                <input type="hidden" name="permitted" value="<?php echo $permitted; ?>">

                <div class="mb-3">
                    <label for="grades" class="form-label">Grades:</label>
                    <?php if ($grade_type == 'prelim') { ?>
                        <input type="text" class="form-control" id="prelim-grade" name="prelim" value="<?php echo $prelim; ?>" placeholder="input grade here...">
                    <?php } elseif ($grade_type == 'midterm') { ?>
                        <input type="text" class="form-control" id="midterm-grade" name="midterm" value="<?php echo $midterm; ?>" placeholder="input grade here...">
                    <?php } elseif ($grade_type == 'final') { ?>
                        <input type="text" class="form-control" id="final-grade" name="final" value="<?php echo $final; ?>" placeholder="input grade here...">
                    <?php } elseif ($grade_type == 'ffg') { ?>
                        <input type="text" class="form-control" id="ffg-grade" name="ffg" value="<?php echo $ffg; ?>" placeholder="input grade here...">
                    <?php } ?>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="permit" class="form -label mb-0">Permit:</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="permit" name="permit_grade" <?php if ($permitted == 1) { echo 'checked'; } ?>>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                <button class="btn btn-back">BACK</button>
                    <?php if ($posted == 1) { ?>
                        <button class="btn btn-post" name="post_grade" disabled>POST</button>
                    <?php } else { ?>
                        <button class="btn btn-post" name="post_grade" <?php echo $disabled; ?>>POST</button>
                    <?php } ?>
                    <button class="btn btn-save" name="save_grade">SAVE</button>
                </div>
            </form>
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const permitCheckbox = document.querySelector("#permit");
        const postButton = document.querySelector("[name='post_grade']");

        // Disable the post button by default
        postButton.disabled = true;

        permitCheckbox.addEventListener("change", function() {
            if (this.checked) {
                postButton.disabled = false;
            } else {
                postButton.disabled = true;
            }
        });
    });
</script>
<script>
        // Add event listener to the save button
document.querySelector("[name='save_grade']").addEventListener("click", function(event) {
    // Get the grade value
    let gradeValue;
    if (<?php echo $grade_type; ?> === 'prelim') {
        gradeValue = document.querySelector("#prelim-grade").value;
    } elseif (<?php echo $grade_type; ?> === 'midterm') {
        gradeValue = document.querySelector("#midterm-grade").value;
    } elseif (<?php echo $grade_type; ?> === 'final') {
        gradeValue = document.querySelector("#final-grade").value;
    } elseif (<?php echo $grade_type; ?> === 'ffg') {
        gradeValue = document.querySelector("#ffg-grade").value;
    }
    
    // Check if the grade value is valid
    if (gradeValue !== '' && gradeValue !== '0.0') {
        // Enable the next grade input field
        if (<?php echo $grade_type; ?> === 'prelim') {
            document.querySelector("[name='midterm']").disabled = false;
        } elseif (<?php echo $grade_type; ?> === 'midterm') {
            document.querySelector("[name='final']").disabled = false;
        } elseif (<?php echo $grade_type; ?> === 'final') {
            document.querySelector("[name='ffg']").disabled = false;
        }
        
    }
    // JavaScript code to update the permitted value
    document.querySelector("[name='permit_grade']").addEventListener("change", function() {
        if (this.checked) {
            document.querySelector("[name='permitted']").value = 1;
        } else {
            document.querySelector("[name='permitted']").value = 0;
        }
    });
});
// Add event listener to the back button
document.querySelector(".btn-back").addEventListener("click", function() {
    window.history.back();
});

</script>
</body>
</html>