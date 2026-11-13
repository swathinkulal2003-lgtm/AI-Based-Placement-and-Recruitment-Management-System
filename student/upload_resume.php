<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$message = "";
if (isset($_POST['upload'])) {
    $student_id = $_SESSION['student_id'];
    $old_resume = "";

    $get_resume_sql = "SELECT resume
                   FROM student
                   WHERE id='$student_id'";

    $get_resume_result = mysqli_query($conn, $get_resume_sql);

    if (mysqli_num_rows($get_resume_result) > 0) {
        $resume_row = mysqli_fetch_assoc($get_resume_result);

        $old_resume = $resume_row['resume'];
    }

    $resume_name = $_FILES['resume']['name'];
    $resume_name = $student_id . "_" . $resume_name;
    $file_extension = strtolower(
        pathinfo($resume_name, PATHINFO_EXTENSION)
    );

    $temp_name = $_FILES['resume']['tmp_name'];
    $file_size = $_FILES['resume']['size'];

    if ($file_extension != "pdf") {
        $message = "Only PDF files are allowed";
    } elseif ($file_size > 2097152) {
        $message = "File size must be less than 2 MB";
    } else {
        $target_path = "../uploads/resumes/" . $resume_name;

        if (move_uploaded_file($temp_name, $target_path)) {
            if ($old_resume != "") {
                $old_file_path = "../uploads/resumes/" . $old_resume;

                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $sql = "UPDATE student
                SET resume='$resume_name'
                WHERE id='$student_id'";

            if (mysqli_query($conn, $sql)) {
                $message = "Resume Uploaded Successfully";
            } else {
                $message = "Database Update Failed";
            }
        } else {
            $message = "File Upload Failed";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Resume</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div class="navbar">

<div class="nav-left">

<a href="dashboard.php">Dashboard</a>
<a href="profile.php">Profile</a>
<a href="companies.php">Companies</a>
<a href="prediction.php">AI Prediction</a>
<a href="placement_status.php">Status</a>
<a href="interview_schedule.php">
                📅 Interviews
            </a>
<a href="upload_resume.php" class="active">Upload Resume</a>
<a href="job_recommendations.php">Job Recommendations</a>

</div>

<div class="nav-right">

<a href="logout.php"
onclick="return confirm('Are you sure you want to logout?');">
Logout
</a>

</div>

</div>

    <div class="card">

        <h2>Upload Resume</h2>
        <?php
        if ($message != "") {
            echo "<p>$message</p>";
        }
        ?>

        <form method="POST" enctype="multipart/form-data">

            Select Resume (PDF):

            <input type="file"
                name="resume"
                accept=".pdf"
                required>

            <br><br>

            <input type="submit"
                name="upload"
                value="Upload Resume">

        </form>
        <br><br>

        <a href="dashboard.php" class="btn">
            ⬅ Back to Dashboard
        </a>
    </div>


</body>

</html>