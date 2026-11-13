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

$sql = "SELECT * FROM student WHERE id='$student_id'";
$result = mysqli_query($conn, $sql);

$student = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="companies.php">Companies</a>
        <a href="prediction.php">AI Prediction</a>
        <a href="placement_status.php">Status</a>

        <a href="interview_schedule.php">
                📅 Interviews
            </a>
        <a href="upload_resume.php">Upload Resume</a>
        <a href="job_recommendations.php">Job Recommendations</a>

    </div>

    <div class="nav-right">

        <a href="logout.php"
        onclick="return confirm('Are you sure you want to logout?');">
        Logout
        </a>

    </div>

</div>
<div class="card profile-card">

<h2>👤 Student Profile</h2>

<table class="profile-table">

<tr>
    <td><b>Name</b></td>
    <td><?php echo $student['name']; ?></td>
</tr>

<tr>
    <td><b>Reg No</b></td>
    <td><?php echo $student['regno']; ?></td>
</tr>

<tr>
    <td><b>Email</b></td>
    <td><?php echo $student['email']; ?></td>
</tr>

<tr>
    <td><b>Phone</b></td>
    <td><?php echo $student['phone']; ?></td>
</tr>

<tr>
    <td><b>CGPA</b></td>
    <td><?php echo $student['cgpa']; ?></td>
</tr>

<tr>
    <td><b>Skills</b></td>
    <td><?php echo $student['skills']; ?></td>
</tr>

</table>
<div class="button-group">

<a href="edit_profile.php" class="btn">
✏ Edit Profile
</a>

<a href="dashboard.php" class="btn">
⬅ Back to Dashboard
</a>

</div>

</div>

</body>
</html>