<?php

session_start();

if(!isset($_SESSION['student_id']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$student_id = $_SESSION['student_id'];

/* Fetch existing data */
$sql = "SELECT * FROM student WHERE id='$student_id'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

/* Update logic */
if(isset($_POST['update']))
{
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $skills = $_POST['skills'];

    $update_sql = "UPDATE student SET 
                    email='$email',
                    phone='$phone',
                    skills='$skills'
                    WHERE id='$student_id'";

    if(mysqli_query($conn, $update_sql))
    {
        header("Location: profile.php");
        exit();
    }
    else
    {
        echo "Update Failed";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Edit Profile</h2>

<form method="POST">

Email:
<br>
<input type="email" name="email" value="<?php echo $student['email']; ?>" required>
<br><br>

Phone:
<br>
<input type="text" name="phone" value="<?php echo $student['phone']; ?>" required>
<br><br>

Skills:
<br>
<input type="text" name="skills" value="<?php echo $student['skills']; ?>" required>
<br><br>

<button type="submit" name="update">Update Profile</button>

</form>

<br>

<a href="profile.php">⬅ Back to Profile</a>

</body>
</html>