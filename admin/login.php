<?php

session_start();

include("../database/config.php");

/** @var mysqli $conn */

if(isset($_POST['login']))
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin
            WHERE username='$username'
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0)
    {
        $_SESSION['admin_username'] = $username;

        header("Location: dashboard.php");
        exit();
    }
    else
    {
        echo "Invalid Username or Password";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-card">

<h2>Admin Login</h2>

<form method="POST">

    Username:
    <input type="text" name="username" required>
    <br><br>

    Password:

<input type="password"
       id="password"
       name="password"
       required>

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