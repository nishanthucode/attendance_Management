<?php
include '../connection.php';

if(isset($_POST['register']))
{
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);

    $sql_check = "SELECT * FROM staff WHERE email = '$email'";
    $result_check = mysqli_query($conn, $sql_check);
    $count_check = mysqli_num_rows($result_check);

    if($count_check > 0){
        echo "<script>alert('Email already exists'); window.location='register.php';</script>";
        exit; 
    }

    $sql_insert = "INSERT INTO staff (staff_name, email, password, subjects) VALUES ('$staff_name', '$email', '$password', '$subject')";
    $result_insert = mysqli_query($conn, $sql_insert);

    if($result_insert){
        echo "<script>alert('Registered successfully'); window.location='login.php';</script>";
        exit; 
    } else {
        echo "<script>alert('Registration failed'); window.location='register.php';</script>";
        exit; 
    }
}
?>
  

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Register</title>
    <link href="login.css" rel="stylesheet">
</head>
<body>
    <div class="box">
        <h2>Staff Register</h2>
        <form action="register.php" method="POST">
            <div class="inputBox">
                <input type="text" name="staff_name" required placeholder="Staff Name">
                <label>Name</label>
            </div>
            <div class="inputBox">
    <select name="subject" required>
        <option value="">Select Subject</option>
        <?php
        include '../connection.php';

        $sql_subjects = "SELECT * FROM subjects";
        $result_subjects = mysqli_query($conn, $sql_subjects);

        if(mysqli_num_rows($result_subjects) > 0) {
            while($row = mysqli_fetch_assoc($result_subjects)) {
                echo "<option value='" . $row['subject_name'] . "'>" . $row['subject_name'] . "</option>";
            }
        }
        ?>
    </select>
    <label>Subject</label>
</div>

            <div class="inputBox">
                <input type="email" name="email" required placeholder="Email">
                <label>Email</label>
            </div>
            <div class="inputBox">
                <input type="password" name="password" required placeholder="Password">
                <label>Password</label>
            </div>
            <input type="submit" name="register" value="Register">
            <a  href="login.php" class="back">Back</a>
        </form>
    </div>
</body>
</html>
