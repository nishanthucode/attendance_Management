<?php
session_start();

if(isset($_SESSION['staff_name'])) {
    header("Location: index.php"); 
    exit;
}

if(isset($_POST['submit'])) {
    include '../connection.php';

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mypassword = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM staff WHERE email = '$email' AND password = '$mypassword'";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_num_rows($result);

    if($count == 1) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $_SESSION['staff_name'] = $row['staff_name'];
        header("location: index.php");
        exit;
    } else {
        $error = "Your Login Email or Password is invalid";
        echo '<script>
        window.location.href="login.php";
        alert("Your Login Email or Password is invalid")
        </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link href="login.css" rel="stylesheet">
</head>
<body>
<div class="box">
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <div class="inputBox">
            <input type="email" name="email" required placeholder="Email">
            <label>Email</label>
        </div>
        <div class="inputBox">
            <input type="password" name="password" required placeholder="Password">
            <label>Password</label>
        </div>
        <input type="submit" name="submit" value="Submit"><br>
        <a style="color:white;">Don't have an account?</a>
        <a style="color:red;text-decoration:none;" href="register.php">Register here</a>
    </form>
</div>

</body>
</html>
