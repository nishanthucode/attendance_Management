<?php
session_start();

if (!isset($_SESSION['staff_name'])) {
    header("Location: login.php");
    exit;
}

include '../connection.php';

$staff_name = $_SESSION['staff_name'];

$sql = "SELECT * FROM staff WHERE staff_name = '$staff_name'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $subjects = $row['subjects'];
} else {
    $subjects = "No subjects found";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'present_') === 0 || strpos($key, 'absent_') === 0) {
            $recordId = substr($key, strpos($key, '_') + 1);
            $attendanceStatus = (strpos($key, 'present_') === 0) ? 'Present' : 'Absent';

            $sql_update_direct = "UPDATE attendance_records SET attendance_status = '$attendanceStatus' WHERE record_id = $recordId";
            $result_update_direct = mysqli_query($conn, $sql_update_direct);
        }
    }
}
$sql_attendance = "SELECT ar.record_id, ar.student_id, st.student_name, su.subject_name, ar.attendance_status, ar.date 
                   FROM attendance_records ar
                   INNER JOIN student st ON ar.student_id = st.student_id
                   INNER JOIN subjects su ON ar.subject_id = su.id
                   WHERE su.subject_name = '$subjects'
                   ORDER BY st.student_id ASC";
$result_attendance = mysqli_query($conn, $sql_attendance);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal</title>
    <link rel="stylesheet" href="styles.css">
    <title>Welcome, <?php echo $staffname; ?></title>
</head>
<body>
    <div class="container">
        <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
        <span>Subject: <?php echo $subjects; ?></span>
        <button class="logout-btn" onclick="location.href='logout.php';">Logout</button>
    </div>

    <div class="content">
        <header>
            <h1>Attendance Management System</h1>
        </header>
        <main>
            <h2>Timetable</h2>
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
            </table><br>
            <h2>Attendance</h2>
            <?php if ($result_attendance && mysqli_num_rows($result_attendance) > 0) : ?>
                <form method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th>SL. No</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Date</th>
                                <th>Actions</th>
                                <th>Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial_number = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result_attendance)) : ?>
                                <tr>
                                    <td><?php echo $serial_number; ?></td>
                                    <td><?php echo $row['student_id']; ?></td>
                                    <td><?php echo $row['student_name']; ?></td>
                                    <td><?php echo $row['date']; ?></td>
                                    <td>
                                        <button type="submit" name="present_<?php echo $row['record_id']; ?>" class="checkmark-button"></button>
                                        <button type="submit" name="absent_<?php echo $row['record_id']; ?>" class="crossmark-button"></button>
                                    </td>
                                    <td><?php echo $row['attendance_status']; ?></td>
                                </tr>
                                <?php $serial_number++; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php else : ?>
                <p>No records found</p>
            <?php endif; ?>
        </main>
    </div>
    <script src="script.js"></script>
</body>
</html>
