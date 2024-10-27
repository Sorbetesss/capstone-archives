<?php

include 'db_conn.php';

if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
    $query = "SELECT c.offercode, t.prof_id, t.prof_fname, t.prof_lname, s.student_id, s.first_name, s.last_name, sg.prelim, sg.midterm, sg.final, sg.ffg 
              FROM course c 
              LEFT JOIN student s ON c.student_id = s.student_id
              LEFT JOIN teacher t ON c.prof_id = t.prof_id
              LEFT JOIN student_grades sg ON c.offercode = sg.offercode
              WHERE s.student_id LIKE '%$search_term%' OR c.offercode LIKE '%$search_term%' OR sg.prelim LIKE '%$search_term%' OR sg.midterm LIKE '%$search_term%' OR sg.ffg LIKE '%$search_term%'";
} else {
    $query = "SELECT c.offercode, c.semester_id, t.prof_id, t.prof_fname, t.prof_lname, s.student_id, s.first_name, s.last_name, sg.prelim, sg.midterm, sg.final, sg.ffg 
          FROM course c 
          LEFT JOIN student s ON c.student_id = s.student_id
          LEFT JOIN teacher t ON c.prof_id = t.prof_id
          LEFT JOIN student_grades sg ON c.offercode = sg.offercode AND c.semester_id = sg.semester_id
          WHERE c.semester_id = sg.semester_id";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<html>
<head>
    <title>E-Grades Admin - Student Grades</title>
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
        .search-bar {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex-grow: 1;
            margin-right: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
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
            <div class="search-bar">
                <form action="" method="post" class="d-flex w-100">
                    <input type="text" class="form-control" placeholder="Search records..." name="search_term">
                    <button class="btn btn-primary" name="search">SEARCH</button>
                </form>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Offer Code</th>
                        <th>Professor ID</th>
                        <th>Professor Name</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>FFG</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?php echo $row['offercode']; ?></td>
                                <td><?php echo $row['prof_id']; ?></td>
                                <td><?php echo $row['prof_fname'] . ' ' . $row['prof_lname']; ?></td>
                                <td><?php echo $row['student_id']; ?></td>
                                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                <td><?php echo round($row['prelim'], 2); ?></td>
                                <td><?php echo round($row['midterm'], 2); ?></td>
                                <td><?php echo round($row['final'], 2); ?></td>
                                <td><?php echo round($row['ffg'], 2); ?></td>
                                <td>
                                    <a href="edit_grade.php?student_id=<?php echo $row['student_id']; ?>&offercode=<?php echo $row['offercode']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <a href="delete_grade.php?student_id=<?php echo $row['student_id']; ?>&offercode=<?php echo $row['offercode']; ?>&semester_id=<?php echo $row['semester_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="9" style="text-align: center; vertical-align : middle; height: 100px; font-size: 24px; font-weight: bold;">No Records Found</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>