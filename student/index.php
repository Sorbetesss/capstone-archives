<?php
require_once 'db_conn1.php';

// Function to get the current semester ID
function getCurrentSemesterId($conn) {
    $current_date = date('Y-m-d');
    $query = "SELECT semester_id FROM semesters WHERE start_date <= ? AND end_date >= ? AND is_active = 1 ORDER BY start_date DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $current_date, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['semester_id'];
    }
    return null; // Return null if no current semester is found
}

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: studentlog.php");
    exit;
}

// Get student ID from session
$student_id = $_SESSION['student_id'];

// Get the current semester ID
$currentSemesterId = getCurrentSemesterId($conn);

// Check if a valid semester ID was found
if ($currentSemesterId === null) {
    echo "Error: No active semester found.";
    exit;
}

// Get all semesters where the student is enrolled
$semestersQuery = "SELECT DISTINCT s.semester_id, s.academic_year, s.semester 
                   FROM semesters s
                   JOIN student_grades sg ON s.semester_id = sg.semester_id
                   WHERE sg.student_id = ?
                   ORDER BY s.academic_year DESC, s.semester DESC";
$stmt = $conn->prepare($semestersQuery);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$semestersResult = $stmt->get_result();
$semesters = $semestersResult->fetch_all(MYSQLI_ASSOC);

// Set the selected semester (either from POST or default to current semester)
$selectedSemesterId = isset($_POST['semester_id']) ? $_POST['semester_id'] : $currentSemesterId;

// Query to get student grades for the selected semester
$query = "SELECT sg.*, c.description 
          FROM student_grades sg
          JOIN course c ON sg.offercode = c.offercode
          WHERE sg.student_id = ? AND sg.semester_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $student_id, $selectedSemesterId);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);

// Query to get student information
$query = "SELECT * FROM student WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student_info = $result->fetch_assoc();

if (!$student_info) {
    echo "Error: Unable to retrieve student information.";
    exit;
}

// Process grades
$allReleased = true;
$anyGradeEntered = false;
foreach ($grades as &$grade) {
    $allGradesEntered = ($grade['prelim_posted'] == 1 && $grade['prelim'] != '0.00') &&
                        ($grade['midterm_posted'] == 1 && $grade['midterm'] != '0.00') &&
                        ($grade['final_posted'] == 1 && $grade['final'] != '0.00') &&
                        ($grade['ffg_posted'] == 1 && $grade['ffg'] != '0.00');
    
    $grade['status'] = $allGradesEntered ? 'RELEASED' : 'UNRELEASED';
    
    if (!$allGradesEntered) {
        $allReleased = false;
    }
    
    if ($grade['prelim'] != '0.00' || $grade['midterm'] != '0.00' || $grade['final'] != '0.00 ' || $grade['ffg'] != '0.00') {
        $anyGradeEntered = true;
    }
}
?>


<html>
<head>
    <title>My Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #3366cc;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2em;
        }
        .container {
            margin-top: 20px;
        }
        .info {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #ffeb3b;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .btn-print {
            background-color: #3366cc;
            color: white;
            font-size: 1.2em;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-print i {
            margin-left: 10px;
        }
        .logout-btn {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-btn i {
            margin-left: 5px;
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .student-info .student-name {
            font-size: 1.5em;
            font-weight: bold;
        }
        .student-info .student-id {
            font-size: 1.2em;
            color: #666;
        }
        .student-info .student-course {
            font-size: 1.2em;
            color: #666;
        }
        .semester-selection {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .semester-selection select {
            min-width: 200px;
        }

        .semester-selection .btn-primary {
            transition: all 0.3s ease;
        }

        .semester-selection .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
        <body style="background-color: white;">
        <div class="header d-flex justify-content-between align-items-center">
            <h1>MY GRADES</h1>
            <a href="studentlog.php?logout=true" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log out
            </a>
        </div>
        <div class="container">
            <div class="table-container">
                <div class="student-info">
                    <div class="student-name">
                        Welcome, <?php echo $student_info['first_name'] . ' ' . $student_info['last_name']; ?>
                    </div>
                    <div class="student-id">
                        ID NUMBER: <?php echo $student_info['student_id']; ?>
                    </div>
                    <div class="student-course">
                        COURSE: <?php echo $student_info['course']; ?>
                    </div>
                </div>
                <div class="semester-selection mb-4">
                <form method="post" class="d-flex align-items-center">
                    <label for="semester_id" class="me-2">Select Semester:</label>
                    <select id="semester_id" name="semester_id" class="form-select me-2" style="width: auto;">
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo $semester['semester_id']; ?>" <?php echo $selectedSemesterId == $semester['semester_id'] ? 'selected' : ''; ?>>
                                <?php echo $semester['academic_year'] . ' - ' . $semester['semester']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-1"></i> Load Grades
                    </button>
                </form>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>SUBJECT CODE</th>
                        <th>DESCRIPTION</th>
                        <th>PRELIM</th>
                        <th>MIDTERM</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                    <tbody>
                    <?php if (empty($grades)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; font-weight: bold;">No Subjects Enrolled</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo $grade['offercode']; ?></td>
                            <td><?php echo $grade['description']; ?></td>
                            <td><?php echo ($grade['prelim_posted'] == 1 && $grade['prelim'] != '0.00') ? $grade['prelim'] : ''; ?></td>
                            <td><?php echo ($grade['midterm_posted'] == 1 && $grade['midterm'] != '0.00') ? $grade['midterm'] : ''; ?></td>
                            <td><?php echo $grade['status']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            <?php if ($allReleased && $anyGradeEntered): ?>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn-print">
                PRINT GRADES <i class="fas fa-download"></i>
            </button>
        </div>
    <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<script>
function printGrades() {
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    
    // Write the HTML content to the new window
    printWindow.document.write('<html><head><title>Grade Report</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    
    // Add student information
    printWindow.document.write('<h2>Grade Report</h2>');
    printWindow.document.write('<p><strong>Name:</strong> <?php echo $student_info['first_name'] . ' ' . $student_info['last_name']; ?></p>');
    printWindow.document.write('<p><strong>Student ID:</strong> <?php echo $student_info['student_id']; ?></p>');
    printWindow.document.write('<p><strong>Course:</strong> <?php echo $student_info['course']; ?></p>');
    
    // Add the grades table
    printWindow.document.write(document.querySelector('.table-container').innerHTML);
    
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    // Trigger the print dialog
    printWindow.print();
}
</script>