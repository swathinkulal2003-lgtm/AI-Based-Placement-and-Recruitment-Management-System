<?php

include("../database/config.php");
/** @var mysqli $conn */

$message = "";

if (isset($_POST['register']))
{
    $regno = trim($_POST['regno']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $cgpa = trim($_POST['cgpa']);
    $skills = trim($_POST['skills']);

    // Convert email to lowercase
    $email = strtolower($email);

    // Check whether email already exists
    $check_email = "SELECT id FROM student WHERE email = ?";

    $stmt = mysqli_prepare($conn, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0)
    {
        $message = "This email is already registered. Please use another email.";
    }
    else
    {
        // Check whether registration number already exists
        $check_regno = "SELECT id FROM student WHERE regno = ?";

        $stmt = mysqli_prepare($conn, $check_regno);
        mysqli_stmt_bind_param($stmt, "s", $regno);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0)
        {
            $message = "This registration number is already registered.";
        }
        else
        {
            // Insert student
            $sql = "INSERT INTO student
                    (regno, name, email, phone, password, cgpa, skills)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $regno,
                $name,
                $email,
                $phone,
                $password,
                $cgpa,
                $skills
            );

            if (mysqli_stmt_execute($stmt))
            {
                $message = "Student Registered Successfully!";
            }
            else
            {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="card">
    <h2>Student Registration</h2>

    <?php if (!empty($message)) { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST">

        <input type="text" name="regno" placeholder="Registration Number" required><br>

        <input type="text" name="name" placeholder="Name" required><br>

        <input type="email" name="email" placeholder="Email" required><br>

        <input type="text" name="phone" placeholder="Phone" required><br>

        <input type="password" name="password" placeholder="Password" required><br>

        <input type="text" name="cgpa" placeholder="CGPA" required><br>

        <input type="text" name="skills" placeholder="Skills (HTML, PHP...)" required><br>

        <button type="submit" name="register" class="btn">Register</button>

    </form>

    <br>
    <a href="login.php">Already have account? Login</a>
</div>

</body>
</html>