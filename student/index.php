<?php
session_start();

if(!isset($_SESSION['student_name'])) {
    header("Location: login.php");
    exit;
}

$studentname = $_SESSION['student_name'];

date_default_timezone_set('Asia/Kolkata'); 
$currentDay = date("l");
$currentTime = date("h:i:s A");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal </title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <span>Welcome, <?php echo $_SESSION['student_name']; ?></span>
    <span><?php echo $currentDay . ', ' . $currentTime ?></span>
    <button class="logout-btn" onclick="location.href='logout.php';">Logout</button>
</div>

<div class="content">
    <header>
        <h1>Attendance Management System</h1>
    </header>
    <main>
        <h2>Time Table</h2>
        <table>
            <tr>
                <th>Day</th>
                <th>9:00 AM - 9:55 AM</th>
                <th>9:55 AM - 10:50 AM</th>
                <th>11:10 AM - 12:05 PM</th>
                <th>12:05 PM - 1:00 PM</th>
                <th>2:00 PM - 3:00 PM</th>
                <th>3:00 PM - 4:00 PM</th>
                <th>4:00 PM - 5:00 PM</th>
            </tr>
            <?php
            include '../connection.php';
            $sqlDays = "SELECT day_name FROM days";
            $resultDays = $conn->query($sqlDays);
            
            $sqlTimetable = "SELECT d.day_name, s.subject_name, ts.start_time, ts.end_time
                            FROM timetable t
                            INNER JOIN days d ON t.day_id = d.id
                            INNER JOIN subjects s ON t.subject_id = s.id
                            INNER JOIN time_slots ts ON t.time_slot_id = ts.id
                            ORDER BY d.id, ts.start_time";
            $resultTimetable = $conn->query($sqlTimetable);
            
            $timetableData = [];
            
            if ($resultTimetable->num_rows > 0) {
                while ($row = $resultTimetable->fetch_assoc()) {
                    $day = $row["day_name"];
                    $startTime = $row["start_time"];
                    $endTime = $row["end_time"];
                    $subject = $row["subject_name"];
                    
                    if (!isset($timetableData[$day])) {
                        $timetableData[$day] = [];
                    }
                    
                    $timetableData[$day][] = [
                        "start_time" => $startTime,
                        "end_time" => $endTime,
                        "subject" => $subject
                    ];
                }
            }
            
            foreach ($timetableData as $day => $timeSlots) {
                echo "<tr><td>$day</td>";
                
                $timeSlotMap = [
                    "09:00:00" => "9:00 AM - 9:55 AM",
                    "09:55:00" => "9:55 AM - 10:50 AM",
                    "11:10:00" => "11:10 AM - 12:05 PM",
                    "12:05:00" => "12:05 PM - 1:00 PM",
                    "02:00:00" => "2:00 PM - 3:00 PM",
                    "03:00:00" => "3:00 PM - 4:00 PM",
                    "04:00:00" => "4:00 PM - 5:00 PM"
                ];
                
                foreach ($timeSlotMap as $startTime => $timeLabel) {
                    $subject = "";
                    foreach ($timeSlots as $timeSlot) {
                        if ($timeSlot["start_time"] == $startTime) {
                            $subject = $timeSlot["subject"];
                            break;
                        }
                    }
                    echo "<td>$subject</td>";
                }
                
                echo "</tr>";
            }
            ?>
        </table>
        <br>

        <h2>Mark Attendance</h2><br>
        <?php
$studentIdQuery = "SELECT student_id FROM student WHERE student_name = '$studentname'";
$resultStudentId = $conn->query($studentIdQuery);

if ($resultStudentId->num_rows > 0) {
    $rowStudentId = $resultStudentId->fetch_assoc();
    $studentId = $rowStudentId["student_id"];

    $currentDay = date('l');
    $currentTime = date('h:i:s ');

    $subjectQuery = "SELECT s.id, s.subject_name FROM subjects s INNER JOIN timetable t ON s.id = t.subject_id INNER JOIN days d ON t.day_id = d.id INNER JOIN time_slots ts ON t.time_slot_id = ts.id WHERE d.day_name = '$currentDay' AND '$currentTime' BETWEEN ts.start_time AND ts.end_time LIMIT 1";

    $resultSubject = $conn->query($subjectQuery);

    if ($resultSubject->num_rows > 0) {
        $rowSubject = $resultSubject->fetch_assoc();
        $subjectId = $rowSubject["id"];
        $subjectName = $rowSubject["subject_name"];

        if (isset($_POST['update_attendance'])) {
            $updateQuery = "UPDATE attendance_records 
                            SET attendance_status = 'Present' 
                            WHERE student_id = '$studentId' 
                            AND subject_id = '$subjectId' 
                            AND date = CURDATE()";

            if ($conn->query($updateQuery) === TRUE) {
                $disableButton = true;
            }
        }

        $attendanceStatusQuery = "SELECT attendance_status 
                                  FROM attendance_records 
                                  WHERE student_id = '$studentId' 
                                  AND subject_id = '$subjectId' 
                                  AND date = CURDATE()";

        $resultStatus = $conn->query($attendanceStatusQuery);
        if ($resultStatus->num_rows > 0) {
            $rowStatus = $resultStatus->fetch_assoc();
            $attendanceStatus = $rowStatus["attendance_status"];
?>
            <div class="subject-details">
                <h3>Subject Name: <?php echo $subjectName; ?></h3>
                <h3>Attendance Status: <?php echo $attendanceStatus; ?></h3>
                <?php if (!isset($disableButton) || $attendanceStatus !== 'Present') { ?>
                    <form method='POST' class="attendance-form">
                        <input type='hidden' name='subject_id' value='<?php echo $subjectId; ?>'>
                        <button type='submit' name='update_attendance'>Update Attendance</button>
                    </form>
                <?php } ?>
            </div>
<?php
        } else {
            $insertAttendanceQuery = "INSERT INTO attendance_records (student_id, subject_id, date, attendance_status) 
                                      VALUES ('$studentId', '$subjectId', CURDATE(), 'Absent')";

            if ($conn->query($insertAttendanceQuery) === TRUE) {
?>
                <div class="subject-details">
                    <p>Subject Name: <?php echo $subjectName; ?></p>
                    <p>Attendance Status: Absent</p>
                    <form method='POST' class="attendance-form">
                        <input type='hidden' name='subject_id' value='<?php echo $subjectId; ?>'>
                        <button type='submit' name='update_attendance'>Update Attendance</button>
                    </form>
                </div>
<?php
            } else {
                echo "<p>Error inserting attendance record: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p>No subject found for the current day and time.</p>";
    }
} else {
    echo "<p>Student not found.</p>";
}
?>
        
        <br>

        <h2>Attendance Percentage</h2>
        <?php
        $subjectQuery = "SELECT id, subject_name FROM subjects";
        $resultSubjects = $conn->query($subjectQuery);

        if ($resultSubjects->num_rows > 0) {
            $subjectCounter = 0;

            echo "<div class='subjects'>";

            while ($rowSubject = $resultSubjects->fetch_assoc()) {
                $subjectId = $rowSubject['id'];
                $subjectName = $rowSubject['subject_name'];

                $presentQuery = "SELECT COUNT(*) AS present_count 
                     FROM attendance_records 
                     WHERE subject_id = $subjectId 
                     AND attendance_status = 'Present'";
                $resultPresent = $conn->query($presentQuery);
                $rowPresent = $resultPresent->fetch_assoc();
                $presentCount = $rowPresent['present_count'];

                $totalQuery = "SELECT COUNT(*) AS total_count 
                   FROM attendance_records 
                   WHERE subject_id = $subjectId";
                $resultTotal = $conn->query($totalQuery);
                $rowTotal = $resultTotal->fetch_assoc();
                $totalCount = $rowTotal['total_count'];

                $attendancePercentage = ($totalCount > 0) ? ($presentCount / $totalCount) * 100 : 0;
                $progressBarColor = ($attendancePercentage >= 85) ? 'green' : 'red';

                if ($subjectCounter % 6 == 0) {
                    echo "<div class='left-subjects'>";
                }

                echo "<div class='subject'>";
                echo "<h3>$subjectName</h3>";
                echo "<div class='progress-bar' style='width: $attendancePercentage%;background-color: $progressBarColor;'></div>";
                echo "<span class='progress-label'>$attendancePercentage%</span>";
                echo "</div>";

                if (($subjectCounter + 1) % 6 == 0 || $subjectCounter + 1 == $resultSubjects->num_rows) {
                    echo "</div>";
                }

                $subjectCounter++;
            }

            echo "</div>";
        } else {
            echo "No subjects found.";
        }
        $conn->close();
        ?>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>
