<?php
include 'db_conn2.php';

// Start the session
session_start();

// Add this function at the top of your file
function getCurrentSemesterId($conn) {
    $current_date = date('Y-m-d');
    $stmt = $conn->prepare("SELECT semester_id, academic_year, semester FROM semesters WHERE start_date <= ? AND end_date >= ? ORDER BY start_date DESC LIMIT 1");
    $stmt->bind_param("ss", $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['semester_id'];
    }
    return null; // Return null if no current semester is found
}

if (isset($_POST['enroll'])) {
    $student_id = $_POST['studentId'];
    $student_name = $_POST['studentName'];
    $course = $_POST['course'];
    $class_code = $_POST['classCode'];
    $semester_id = $_POST['semester'];

    // Validate the form data
    if (empty($student_id) || empty($student_name) || empty($course) || empty($class_code) || empty($semester_id)) {
        $error = "Please fill in all fields.";
    } else {
        // Get the prof_id of the currently logged in teacher
        $prof_id = $_SESSION['prof_id'];

        // Check if the class code exists in class_subjects
        $stmt = $conn->prepare("SELECT class_code FROM class_subjects WHERE class_code = ?");
        $stmt->bind_param("s", $class_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $error = "Invalid class code. Please select a valid class code.";
        } else {
            // Check if the student is already enrolled in this class for the selected semester
            $stmt = $conn->prepare("SELECT * FROM course WHERE offercode = ? AND student_id = ? AND semester_id = ?");
            $stmt->bind_param("ssi", $class_code, $student_id, $semester_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "This student is already enrolled in this class for the selected semester.";
            } else {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Insert the new enrollment into the course table
                    $stmt = $conn->prepare("INSERT INTO course (offercode, prof_id, student_id, semester_id) 
                    VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $class_code, $prof_id, $student_id, $semester_id);
                    $stmt->execute();

                    // Insert the new student into the student_grades table
                    $stmt = $conn->prepare("INSERT INTO student_grades (offercode, student_id, semester_id, prelim, midterm, final, ffg, prelim_posted, midterm_posted, final_posted, ffg_posted) 
                    VALUES (?, ?, ?, 0.0, 0.0, 0.0, 0.0, 0, 0, 0, 0)");
                    $stmt->bind_param("ssi", $class_code, $student_id, $semester_id);
                    $stmt->execute();

                    // Commit transaction
                    $conn->commit();

                    $success = "Student enrolled successfully.";
                } catch (mysqli_sql_exception $e) {
                    // Rollback transaction
                    $conn->rollback();

                    $error = "Error enrolling student: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch semesters for the dropdown
$stmt = $conn->prepare("SELECT semester_id, academic_year, semester FROM semesters ORDER BY start_date DESC");
$stmt->execute();
$semesters_result = $stmt->get_result();
$semesters = $semesters_result->fetch_all(MYSQLI_ASSOC);

// Fetch class codes for the dropdown
$stmt = $conn->prepare("SELECT class_code FROM class_subjects ORDER BY class_code ASC");
$stmt->execute();
$class_codes_result = $stmt->get_result();
$class_codes = $class_codes_result->fetch_all(MYSQLI_ASSOC);

// Fetch semesters for the dropdown
$stmt = $conn->prepare("SELECT semester_id, academic_year, semester FROM semesters ORDER BY start_date DESC");
$stmt->execute();
$semesters_result = $stmt->get_result();
$semesters = $semesters_result->fetch_all(MYSQLI_ASSOC);

?>

<html>
<head>
<title>Enroll Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .enroll-container {
            width: 400px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .enroll-header {
            background-color: #3b5bdb;
            color: white;
            text-align: center;
            padding: 10px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .enroll-body {
            padding: 20px;
        }
        .form-control::placeholder {
            color: #b0b0b0;
            font-style: italic;
        }
        .form-control {
            font-style: italic;
        }
        .btn-back {
            background-color: #e0e0e0;
            color: black;
        }
        .btn-enroll {
            background-color: #3b5bdb;
            color: white;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="enroll-container">
        <div class="enroll-header">
            <h5>ENROLL STUDENT</h5>
        </div>
        <div class="enroll-body">
            <?php if (isset($error)) { ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php } ?>
            <form method="post">
                <div class="mb-3">
                    <label for="studentId" class="form-label">Student ID :</label>
                    <input type="text" class="form-control" id="studentId" name="studentId" placeholder="input ID..." oninput="getStudentInfo()">
                </div>
                <div class="mb-3">
                    <label for="studentName" class="form-label">Student Name :</label>
                    <input type="text" class="form-control" id="studentName" name="studentName" readonly>
                </div>
                <div class="mb-3">
                    <label for="course" class="form-label">Course :</label>
                    <input type="text" class="form-control" id="course" name="course" readonly>
                </div>
                <div class="mb-3">
                    <label for="classCode" class="form-label">Class Code :</label>
                    <select class="form-select" id="classCode" name="classCode" required>
                        <option value="">Class Code:</option>
                        <?php foreach ($class_codes as $code): ?>
                            <option value="<?php echo htmlspecialchars($code['class_code']); ?>">
                                <?php echo htmlspecialchars($code['class_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="semester" class="form-label">Semester:</label>
                    <select class="form-select" id="semester" name="semester" required>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo $semester['semester_id']; ?>">
                            <?php echo $semester['academic_year'] . ' - ' . $semester['semester']; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-back" onclick="window.location .href='teacher_dashboard.php'">BACK</button>
                    <button type="submit" class="btn btn-enroll" name="enroll">ENROLL</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getStudentInfo() {
            var studentId = document.getElementById("studentId").value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "get_student_info.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    try {
                        var response = JSON.parse(this.responseText);
                        if (response.error) {
                            console.error(response.error);
                            document.getElementById("studentName").value = "";
                            document.getElementById("course").value = "";
                        } else {
                            document.getElementById("studentName").value = response.first_name + " " + response.last_name;
                            document.getElementById("course").value = response.course;
                        }
                    } catch (e) {
                        console.error("Error parsing JSON:", e);
                    }
                }
            };
            xhr.send("studentId=" + encodeURIComponent(studentId));
        }
    </script>
</body>
</html>