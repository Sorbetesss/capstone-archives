<?php
include 'db_conn2.php';

if (isset($_POST['semester_id']) && isset($_POST['prof_id'])) {
    $semester_id = $_POST['semester_id'];
    $prof_id = $_POST['prof_id'];

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
    
    $stmt->bind_param("iii", $semester_id, $semester_id, $prof_id);
    $stmt->execute();
    $grades_result = $stmt->get_result();

    if ($grades_result->num_rows > 0) {
        $output = '';
        while ($row = $grades_result->fetch_assoc()) {
            $student_id = $row['student_id'];
            $offercode = $row['offercode'];
            $output .= "<tr>";
            $output .= "<td>" . htmlspecialchars($offercode) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['student_id']) . "</td>";

            $grade_types = ['prelim', 'midterm', 'final', 'ffg'];
            foreach ($grade_types as $grade_type) {
                $grade_value = isset($row[$grade_type]) ? $row[$grade_type] : '';
                $posted = isset($row[$grade_type . '_posted']) ? $row[$grade_type . '_posted'] : 0;

                $output .= "<td>";
                $output .= "<input type='text' class='grade-input' id='" . $grade_type . "-" . $student_id . "-" . $offercode . "' value='" . htmlspecialchars($grade_value) . "'>";
                $output .= "<div class='button-container' style='margin-top: 10px;'>";
                if ($posted == 1) {
                    $output .= "<button class='btn btn-secondary btn-post-grade' disabled style='width: 100%;'>POSTED</button>";
                } else {
                    $output .= "<button class='btn btn-primary btn-save-grade' data-student-id='" . $student_id . "' data-offer-code='" . $offercode . "' data-grade='" . $grade_type . "' data-semester-id='" . $semester_id . "' onclick='saveGrade(" . $student_id . ", \"" . $offercode . "\", \"" . $grade_type . "\", \"" . $semester_id . "\")' style='width: 49%; margin-right: 2%;'>Save</button>";
                    $output .= "<button class='btn btn-success btn-post-grade' data-student-id='" . $student_id . "' data-offer-code='" . $offercode . "' data-grade='" . $grade_type . "' data-semester-id='" . $semester_id . "' onclick='postGrade(" . $student_id . ", \"" . $offercode . "\", \"" . $grade_type . "\", \"" . $semester_id . "\")' style='width: 49%;'> Post</button>";
                }
                $output .= "</div>";
                $output .= "</td>";
            }
            $output .= "</tr>";
        }
        echo $output;
    } else {
        echo "<p>No students found for this semester.</p>";
    }
}
?>