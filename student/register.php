<?php
include '../connection.php';

if(isset($_POST['register']))
{
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql_check = "SELECT * FROM student WHERE email = '$email'";
    $result_check = mysqli_query($conn, $sql_check);
    $count_check = mysqli_num_rows($result_check);

    if($count_check > 0){
        echo "<script>alert('Email already exists'); window.location='register.php';</script>";
        exit; 
    }

    $sql_insert = "INSERT INTO student (student_name, email, password) VALUES ('$student_name', '$email', '$password')";
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
    <title>Student Register</title>
    <link href="login.css" rel="stylesheet">
</head>
<body>
    <div class="box">   
        <h2>Student Register</h2>
        <form action="register.php" method="POST">
            <div class="inputBox">
                <input type="text" name="student_name" required placeholder="Student Name">
                <label>Name</label>
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
            <a href="login.php" class="back">Back</a>
        </form>
    </div>
</body>
</html>
