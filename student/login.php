<?php
session_start();

include("../database/config.php");
/** @var mysqli $conn */

if(isset($_POST['login']))
{
    $regno = $_POST['regno'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM student
            WHERE regno='$regno'
            AND password='$password'";

    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0)
    {
        $student = mysqli_fetch_assoc($result);

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];

        header("Location: dashboard.php");
        exit();
    }
    else
    {
        echo "Invalid Reg No or Password";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-card">

<h2>Student Login</h2>

<form method="POST">

    Reg No:
    <input type="text" name="regno" placeholder="Enter Register Number" required>
    <br><br>

   Password:

<input type="password" id="password" name="password" placeholder="Enter Password">

<span
    onclick="togglePassword()"
    style="cursor:pointer; margin-left:8px;font-size:20px;">
    👁
</span>

<br><br>

<br><br>

    <input type="submit" name="login" value="Login">

</form>
    </div>

<script>

function togglePassword()
{
    var passwordField = document.getElementById("password");

    if(passwordField.type === "password")
    {
        passwordField.type = "text";
    }
    else
    {
        passwordField.type = "password";
    }
}

</script>

</body>
</html>