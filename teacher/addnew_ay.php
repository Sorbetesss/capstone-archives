<?php
include 'db_conn2.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form submission
    $academic_year = $_POST['academic_year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $date_range = isset($_POST['date_range']) ? explode(' to ', $_POST['date_range']) : [];
    $start_date = isset($date_range[0]) ? date('Y-m-d', strtotime($date_range[0])) : '';
    $end_date = isset($date_range[1]) ? date('Y-m-d', strtotime($date_range[1])) : '';

    // Check if the semester already exists for this academic year
    $check_sql = "SELECT COUNT(*) as count FROM semesters WHERE academic_year = ? AND semester = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $academic_year, $semester);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $error = "Error: This semester already exists for the selected academic year.";
    } else {
        // Check if there are already three semesters for this academic year
        $count_sql = "SELECT COUNT(*) as count FROM semesters WHERE academic_year = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("s", $academic_year);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();

        if ($count_row['count'] >= 3) {
            $error = "Error: There are already three semesters for this academic year.";
        } else {
            $sql = "INSERT INTO semesters (academic_year, semester, start_date, end_date, is_active) 
                    VALUES (?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $academic_year, $semester, $start_date, $end_date);

            if ($stmt->execute()) {
                $message = "New semester added successfully.";
            } else {
                $error = "Error: " . $stmt-> error;
            }

            $stmt->close();
        }
        $count_stmt->close();
    }
    $check_stmt->close();
}

$current_year = date('Y');
$next_year = $current_year + 1;
$default_academic_year = $current_year . '-' . $next_year;

// Fetch existing semesters for the dropdown
$existing_semesters_sql = "SELECT academic_year, semester FROM semesters ORDER BY academic_year DESC, semester ASC";
$existing_semesters_result = $conn->query($existing_semesters_sql);
$existing_semesters = [];
while ($row = $existing_semesters_result->fetch_assoc()) {
    $existing_semesters[$row['academic_year']][] = $row['semester'];
}

$semesters = ['1st', '2nd', 'Summer'];
$semester_display = ['1st' => '1st Semester', '2nd' => '2nd Semester', 'Summer' => 'Summer'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Academic Year</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 500px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
        <h2>Add New Academic Year</h2>
        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php } ?>
        <form method="post">
            <div class="mb-3">
                <label for="academic_year" class="form-label">Academic Year</label>
                <select class="form-select" id="academic_year" name="academic_year" required>
                    <?php for ($i = 0; $i < 10; $i++) { ?>
                        <?php $year = $current_year + $i; ?>
                        <?php $next_year = $year + 1; ?>
                        <?php $academic_year = "$year-$next_year"; ?>
                        <?php $selected = ($academic_year == $default_academic_year) ? 'selected' : ''; ?>
                        <option value="<?= $academic_year ?>" <?= $selected ?>><?= $academic_year ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select" id="semester" name="semester" required>
                    <?php foreach ($semesters as $sem) { ?>
                        <?php $disabled = ''; ?>
                        <?php if (isset($existing_semesters[$_POST['academic_year']]) && in_array($sem, $existing_semesters[$_POST['academic_year']])) { ?>
                            <?php $disabled = 'disabled'; ?>
                        <?php } ?>
                        <option value="<?= $sem ?>" <?= $disabled ?>><?= $semester_display[$sem] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="date_range" class="form-label">Date Range</label>
                <input type="text" class="form-control" id="date_range" name="date_range" required>
            </div>
            <div class="btn-container">
                <a href="teacher_dashboard.php" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Add Semester</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "Y-m-d"
        });

        document.getElementById('academic_year').addEventListener('change', function() {
            var selectedYear = this.value;
            var semesterSelect = document.getElementById('semester');
            var existingSemesters = <?= json_encode($existing_semesters); ?>;

            // Enable all options first
            for (var i = 0; i < semesterSelect.options.length; i++) {
                semesterSelect.options[i].disabled = false;
            }

            // Disable options for existing semesters
            if (existingSemesters[selectedYear]) {
                existingSemesters[selectedYear].forEach(function(semester) {
                    var option = semesterSelect.querySelector('option[value="' + semester + '"]');
                    option.disabled = true;
                });
            }
        });
    </script>
</body>
</html>