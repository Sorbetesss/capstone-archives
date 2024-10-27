<?php
include 'db_conn2.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['prof_id'])) {
    header("Location: facultylog.php");
    exit;
}

$prof_id = $_SESSION['prof_id'];

function getCurrentSemesterId($conn) {
    $current_date = date('Y-m-d');
    $stmt = $conn->prepare("SELECT semester_id FROM semesters WHERE start_date <= ? AND end_date >= ? AND is_active = 1 ORDER BY start_date DESC LIMIT 1");
    $stmt->bind_param("ss", $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['semester_id'];
    }
    return null;
}

if (isset($_POST['semester_id'])) {
    $currentSemesterId = $_POST['semester_id'];
} elseif (isset($_GET['semester_id'])) {
    $currentSemesterId = $_GET['semester_id'];
} else {
    $currentSemesterId = getCurrentSemesterId($conn);
}

if ($currentSemesterId === null) {
    $noSemesterFound = true;
    $error = "No active semester found. Please add a semester.";
} else {
    $noSemesterFound = false;
}

// Fetch semesters for the dropdown
$stmt = $conn->prepare("SELECT semester_id, academic_year, semester FROM semesters ORDER BY start_date DESC");
$stmt->execute();
$semesters_result = $stmt->get_result();
$semesters = $semesters_result->fetch_all(MYSQLI_ASSOC);

// Fetch class codes for the dropdown
$stmt = $conn->prepare("SELECT class_code FROM class_subjects ORDER BY class_code");
$stmt->execute();
$class_codes_result = $stmt->get_result();
$class_codes = $class_codes_result->fetch_all(MYSQLI_ASSOC);

// Set default class code if not selected
$selected_class_code = $_POST['class_code'] ?? ($_GET['class_code'] ?? null);

// When handling the enrollment:
if (isset($_POST['enroll'])) {
    $student_id = $_POST['studentId'] ?? '';
    $class_code = $_POST['classCode'] ?? '';
    $semester_id = $_POST['semester'] ?? $currentSemesterId; // Use current semester if not specified

    if (empty($student_id) || empty($class_code) || empty($semester_id)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM course WHERE offercode = ? AND student_id = ? AND semester_id = ?");
        $stmt->bind_param("sis", $class_code, $student_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "This student is already enrolled in this class for the selected semester.";
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO course (offercode, student_id, semester_id, prof_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $class_code, $student_id, $semester_id, $prof_id);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO student_grades (student_id, offercode, semester_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $student_id, $class_code, $semester_id);
                $stmt->execute();

                $conn->commit();
                $success = "Student enrolled successfully.";
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $error = "Failed to enroll student. Please try again.";
            }
        }
    }
}

$query = "SELECT * FROM teacher WHERE prof_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $prof_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $prof_name = $row['prof_fname'] . ' ' . $row['prof_lname'];
} else {
    $prof_name = 'Unknown';
}

// Fetch student grades for the table
$stmt = $conn->prepare("
    SELECT 
        s.student_id, 
        s.first_name, 
        s.last_name, 
        c.offercode,
        g.semester_id,
        g.prelim, 
        g.midterm, 
        g.final, 
        g.ffg,
        g.prelim_posted, 
        g.midterm_posted,
        g.final_posted, 
        g.ffg_posted
    FROM student s 
    JOIN course c ON s.student_id = c.student_id AND c.semester_id = ?
    JOIN student_grades g ON s.student_id = g.student_id AND c.offercode = g.offercode AND g.semester_id = ?
    WHERE c.prof_id = ?
    ORDER BY c.offercode, s.last_name, s.first_name
");

$stmt->bind_param("iii", $currentSemesterId, $currentSemesterId, $prof_id);
$stmt->execute();
$grades_result = $stmt->get_result();

$has_students = ($grades_result->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #3366cc;
            color: white;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header .title {
            display: flex;
            align-items: center;
        }
        .header .title i {
            font-size: 2 rem;
            margin-right: 10px;
        }
        .header .title h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .header .logout {
            background-color: white;
            color: black;
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .container-form {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .instructor-info {
            margin-bottom: 20px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            font-size: 1.25rem;
        }
        .content .btn-add-student {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .content .btn-add-student button {
            margin-left: 20px ;
        }
        .table thead th {
            background-color: #ffcc00;
            text-align: center;
        }
        .table tbody td {
            text-align: center;
        }
        .subject-class-code {
            text-align: left;
            margin-left: 20px;
        }
        .grade-input {
            width: 50px;
            height: 30px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-save-grade, .btn-post-grade {
        padding: 5px 10px;
        font-size: 12px;
        margin: 2px;
        width: 45%;
    }

    .btn-save-grade {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-post-grade {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-save-grade:hover, .btn-post-grade:hover {
        opacity: 0.8;
    }

    .btn-save-grade:disabled, .btn-post-grade:disabled {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .semester-management {
    background-color: #f8f 9fa;
    padding: 20px;
    border-radius: 8 px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .semester-management h4 {
        margin-bottom: 15px;
        color: #333;
    }

    #manageSemesterDropdown {
        width: 100%;
    }
    .header {
    background-color: #3366cc;
    color: white;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.header .title {
    display: flex;
    align-items: center;
}
.header .title i {
    font-size: 2rem;
    margin-right: 10px;
}
.header .title h1 {
    margin: 0;
    font-size: 1.5rem;
}
.header .form-select,
.header .btn-secondary,
.header .logout {
    font-size: 0.9rem;
    padding: 0.25rem 0.5rem;
}
.header .logout {
    background-color: white;
    color: black;
    border: 1px solid #ccc;
    padding: 5px 10px;
    border-radius: 5px;
}
.dropdown-menu {
    display: block;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-toggle::after {
    transition: transform 0.3s ease;
}

.dropdown.show .dropdown-toggle::after {
    transform: rotate(180deg);
}
.form-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}
.form-container .form-group {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title">
        <i class="fas fa-user-circle"></i>
        <h1>Instructor</h1>
    </div>
    <div class="d-flex align-items-center">
            <select class="form-select me-2" id="semesterSelect" name="semester_id" style="width: auto;">
            <?php foreach ($semesters as $semester): ?>
                <option value="<?php echo $semester['semester_id']; ?>" <?php echo ($semester['semester_id'] == $currentSemesterId) ? 'selected' : ''; ?>>
                    <?php echo $semester['academic_year'] . ' - ' . $semester['semester']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="dropdown me-2">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="manageSemesterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Manage
            </button>
            <ul class="dropdown-menu" aria-labelledby="manageSemesterDropdown">
            <li><a class="dropdown-item" href ="addnew_ay.php" id="addAcademicYear">Add new Academic Year</a></li>
                <li><a class="dropdown-item" href="#" id="editAcademicYear">Edit Academic Year</a></li>
            </ul>
        </div>
        <button class="logout" onclick="location.href='facultylog.php'">Log out <i class="fas fa-sign-out-alt"></i></button>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="instructor-info">
                <h2>Instructor Dashboard</h2>
                <hr class="my-4 bg-secondary">
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><strong>Semester:</strong> 
                    <?php 
                    if ($noSemesterFound) {
                        echo "No active semester";
                    } else {
                        $currentSemester = null;
                        foreach ($semesters as $semester) {
                            if ($semester['semester_id'] == $currentSemesterId) {
                                $currentSemester = $semester;
                                break;
                            }
                        }
                        echo $currentSemester ? $currentSemester['academic_year'] . ' - ' . $currentSemester['semester'] : "No semester selected";
                    }
                    ?>
                </h5>
            </div>

            <?php if (!$noSemesterFound): ?>
                <div class="form-container mb-4">
    <h5>ENROLL STUDENT</h5>
    <?php
    if (isset($success)) {
        echo "<div class='alert alert-success'>" . $success . "</div>";
    }
    if (isset($error)) {
        echo "<div class='alert alert-danger'>" . $error . "</div>";
    }
    ?>
    <form method="post" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="studentId" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="studentId" name="studentId" required>
        </div>
        <div class="col-md-3">
            <label for="classCode" class="form-label">Class Code</label>
            <select class="form-select" id="classCode" name="classCode" required>
                <option value="">Select Class Code</option>
                <?php foreach ($class_codes as $code): ?>
                    <option value="<?php echo htmlspecialchars($code['class_code']); ?>">
                        <?php echo htmlspecialchars($code['class_code']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="semester" class="form-label">Semester</label>
            <select class="form-select" id="semester" name="semester" required>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?php echo $semester['semester_id']; ?>" <?php echo ($semester['semester_id'] == $currentSemesterId) ? 'selected' : ''; ?>>
                        <?php echo $semester['academic_year'] . ' - ' . $semester['semester']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary" name="enroll">Enroll</button>
        </div>
        <input type="hidden" name="currentSemesterId" value="<?php echo $currentSemesterId; ?>">
    </form>
</div>

<?php endif; ?>

<?php if ($noSemesterFound): ?>
    <p>Please add a semester to start entering grades.</p>
<?php elseif ($has_students): ?>
    <table class="table table-striped">
    <thead>
        <tr>
            <th>Class Code</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <th>Prelim</th>
            <th>Midterm</th>
            <th>Final</th>
            <th>FFG</th>
        </tr>
    </thead>
    <tbody id="gradesTable">
        <?php
        while ($row = $grades_result->fetch_assoc()) {
            $student_id = $row['student_id'];
            $offercode = $row['offercode'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($offercode) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($student_id) . "</td>";

            $grade_types = ['prelim', 'midterm', 'final', 'ffg'];
            foreach ($grade_types as $grade_type) {
                $grade_value = isset($row[$grade_type]) ? $row[$grade_type] : '';
                $posted = isset($row[$grade_type . '_posted']) ? $row[$grade_type . '_posted'] : 0;

                echo "<td>";
                echo "<input type='text' class='grade-input' id='" . $grade_type . "-" . $student_id . "-" . $offercode . "' value='" . htmlspecialchars($grade_value) . "'>";
                echo "<div class='button-container' style='margin-top: 10px;'>";
                if ($posted == 1) {
                    echo "<button class='btn btn-secondary btn-post-grade' disabled style='width: 100%;'>POSTED</button>";
                } else {
                    echo "<button class='btn btn-primary btn-save-grade' data-student-id='" . $student_id . "' data-offer-code='" . $offercode . "' data-grade='" . $grade_type . "' data-semester-id='" . $currentSemesterId . "' onclick='saveGrade(" . $student_id . ", \"" . $offercode . "\", \"" . $grade_type . "\", \"" . $currentSemesterId . "\")' style='width: 49%; margin-right: 2%;'>Save</button>";
                    echo "<button class='btn btn-success btn-post-grade' data-student-id='" . $student_id . "' data-offer-code='" . $offercode . "' data-grade='" . $grade_type . "' data-semester-id='" . $currentSemesterId . "' onclick='postGrade(" . $student_id . ", \"" . $offercode . "\", \"" . $grade_type . "\", \"" . $currentSemesterId . "\")' style='width: 49%;'> Post</button>";
                }
                echo "</div>";
                echo "</td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>
    </table>
<?php else: ?>
    <p>No students found for this semester.</p>
<?php endif; ?>
    </div>
</div>
</div>

<script>
document.getElementById('semester').addEventListener('change', function() {
    var classCodeSelect = document.getElementById('classCode');
    
    // Clear existing options
    classCodeSelect.innerHTML = '<option value="">Select Class Code</option>';
    
    // Fetch new class codes
    fetch('get_class_codes.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(code => {
                var option = document.createElement('option');
                option.value = code.class_code;
                option.textContent = code.class_code;
                classCodeSelect.appendChild(option);
            });
        });
});


    document.getElementById('semesterSelect').addEventListener('change', function() {
    var semesterId = this.value;
    refreshGrades(semesterId);
});

function refreshGrades(semesterId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'get_student_grades.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status == 200) {
            document.querySelector('#gradesTable').innerHTML = this.responseText;
            attachGradeEventListeners();
        }
    };
    xhr.send('semester_id=' + semesterId + '&prof_id=' + <?php echo json_encode($prof_id); ?>);
}

document.getElementById('semesterSelect').addEventListener('change', function() {
    var semesterId = this.value;
    refreshGrades(semesterId);
});
    
    function saveGrade(student_id, offercode, grade_type, semester_id) {
        if (!semester_id) {
            alert('No active semester. Unable to save grade.');
            return Promise.reject('No active semester');
        }
        return new Promise((resolve, reject) => {
            var inputField = document.getElementById(grade_type + '-' + student_id + '-' + offercode);
            var value = inputField.value;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_grade.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('student_id=' + student_id + '&offer_code=' + offercode + '&grade_type=' + grade_type + '&grade=' + value + '&semester_id=' + semester_id);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        resolve(response.message);
                    } else {
                        reject(response.message);
                    }
                } else {
                    reject('Error saving grade: ' + xhr.statusText);
                }
            };
        });
    }

    function postGrade(student_id, offercode, grade_type, semester_id) {
        if (!semester_id) {
            alert('No active semester. Unable to post grade.');
            return Promise.reject('No active semester');
        }
        return new Promise((resolve, reject) => {
            var inputField = document.getElementById(grade_type + '-' + student_id + '-' + offercode);
            var value = inputField.value;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'post_grade.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('student_id=' + encodeURIComponent(student_id) + '&offer_code=' + encodeURIComponent(offercode) + '&grade_type=' + encodeURIComponent(grade_type) + '&grade=' + encodeURIComponent(value) + '&semester_id=' + encodeURIComponent(semester_id));

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resolve('Grade posted successfully');
                        } else {
                            reject('Error: ' + response.message);
                        }
                    } catch (e) {
                        reject('Error parsing server response');
                    }
                } else {
                    reject('Error posting grade: ' + xhr.statusText );
                }
            };

            xhr.onerror = function() {
                reject('Network error occurred');
            };
        });
    }

    function attachGradeEventListeners() {
        var saveButtons = document.querySelectorAll('.btn-save-grade');
        var postButtons = document.querySelectorAll('.btn-post-grade');
        var gradeInputs = document.querySelectorAll('.grade-input');

        saveButtons.forEach(function(button) {
            var student_id = button.dataset.studentId;
            var offercode = button.dataset.offerCode;
            var grade = button.dataset.grade;
            var semester_id = button.dataset.semesterId;

            if (localStorage.getItem(grade + 'Saved-' + student_id + '-' + offercode) === 'true') {
                button.disabled = true;
            }

            button.addEventListener('click', function(event) {
                event.preventDefault();

                // Show confirmation panel
                var confirmSave = confirm('Save this grade? It will save data but not serve as final output.');
                if (confirmSave) {
                    saveGrade(student_id, offercode, grade, semester_id)
                        .then(message => {
                            alert(message);
                            // Store the state of the button in local storage
                            localStorage.setItem(grade + 'Saved-' + student_id + '-' + offercode, 'true');
                            // Enable the post button
                            var postButton = document.querySelector('.btn-post-grade[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
                            postButton.disabled = false;
                        })
                        .catch(error => {
                            alert(error);
                        });
                }
            });
        });

        postButtons.forEach(function(button) {
            var student_id = button.dataset.studentId;
            var offercode = button.dataset.offerCode;
            var grade = button.dataset.grade;
            var semester_id = button.dataset.semesterId;

            button.addEventListener('click', function(event) {
                event.preventDefault();

                // Show confirmation panel
                var confirmPost = confirm('Post this grade? Once posted, you cannot make changes to the grades');
                if (confirmPost) {
                    postGrade(student_id, offercode, grade, semester_id)
                        .then(message => {
                            alert(message);
                            // Disable the input field and both buttons
                            var inputField = document.getElementById(grade + '-' + student_id + '-' + offercode);
                            inputField.disabled = true;
                            var saveButton = document.querySelector('.btn-save-grade[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"][data-grade="' + grade + '"]');
                            if (saveButton) saveButton.disabled = true;
                            button.disabled = true;
                            // Store the state of the button in local storage
                            localStorage.setItem(grade + 'Posted-' + student_id + '-' + offercode, 'true');
                            // Refresh the page
                            location.reload();
                        })
                        .catch(error => {
                            alert(error);
                        });
                }
            });
        });

        gradeInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                var student_id = input.id.split('-')[1];
                var offercode = input.id.split('-')[2];

                var postButton = document.querySelector('[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
                postButton.disabled = false;
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachGradeEventListeners();
    });

    var midtermInputs = document.querySelectorAll('input[id^="midterm-"]');
    midtermInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var student_id = input.id.split('-')[1];
            var offercode = input.id.split('-')[2];

            var postButton = document.querySelector('[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
            postButton.disabled = false;
        });
    });

    var finalInputs = document.querySelectorAll('input[id^="final-"]');
    finalInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var student_id = input.id.split('-')[1];
            var offercode = input.id.split('-')[2];

            var postButton = document.querySelector('[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
            postButton.disabled = false;
        });
    });

    var ffgInputs = document.querySelectorAll('input[id^="ffg-"]');
    ffgInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var student_id = input.id.split('-')[1];
            var offercode = input.id.split('-')[2];

            var postButton = document.querySelector('[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
            postButton.disabled = false;
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.grade-input:not([disabled])').forEach(function(input) {
            input.addEventListener ('click', function() {
                this.readOnly = false;
            });
        });

        document.querySelectorAll('.grade-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var student_id = input.id.split('-')[1];
                var offercode = input.id.split('-')[2];

                var postButton = document.querySelector('[data-student-id="' + student_id + '"][data-offer-code="' + offercode + '"]');
                postButton.disabled = false;
            });
        });
    });

    document.getElementById('addAcademicYear').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'addnew_ay.php';
    });

    document.getElementById('editAcademicYear').addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Edit Academic Year clicked');
        // You might want to open a modal or redirect to a new page
    });

    document.getElementById('newSemester').addEventListener('click', function(e) {
        e.preventDefault();
        console.log('New Semester clicked');
        // You might want to open a modal or redirect to a new page
    });

    document.getElementById('semesterSelect').addEventListener('change', function() {
        refreshGrades();
    });

    function refreshGrades(semesterId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'get_student_grades.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status == 200) {
            document.querySelector('#gradesTable').innerHTML = this.responseText;
            attachGradeEventListeners();
        }
    };
    xhr.send('semester_id=' + semesterId + '&prof_id=' + <?php echo json_encode($prof_id); ?>);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>