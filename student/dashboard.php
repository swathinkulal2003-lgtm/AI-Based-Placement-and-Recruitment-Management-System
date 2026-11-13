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

/* Profile Completion */

$completed = 0;

if(!empty($student['name']))
    $completed++;

if(!empty($student['email']))
    $completed++;

if(!empty($student['phone']))
    $completed++;

if(!empty($student['cgpa']))
    $completed++;

if(!empty($student['skills']))
    $completed++;

if(!empty($student['resume']))
    $completed++;

$percentage = round(($completed / 6) * 100);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="navbar">

    <div class="nav-left">
        <a href="dashboard.php" class="active">Dashboard</a>
<a href="profile.php">Profile</a>
<a href="companies.php">Companies</a>
<a href="prediction.php">AI Prediction</a>
<a href="placement_status.php">Status</a>

<a href="placement_results.php">
    Results
</a>
<a href="interview_schedule.php">
                📅 Interviews
            </a>
<a href="evaluations.php">
    📊 Evaluations
</a>
<a href="upload_resume.php">Upload Resume</a>
<a href="job_recommendations.php">Job Recommendations</a>
    </div>

    <div>
        <a href="logout.php"
        onclick="return confirm('Are you sure you want to logout?');">
        Logout
        </a>
    </div>

</div>

<!-- Welcome Card -->

<div class="card glow">

    <h2>Welcome <?php echo $_SESSION['student_name']; ?></h2>

    <p class="dashboard-text">
        This is your placement dashboard
    </p>

</div>

<!-- Profile Completion -->

<div class="card">

    <h2>Profile Completion</h2>

    <div class="progress-bar">

        <div class="progress-fill"
        style="width:<?php echo $percentage; ?>%;"></div>

    </div>

    <h3><?php echo $percentage; ?>% Completed</h3>

    <table>

        <tr>
            <td>Name</td>
            <td>
                <?php
                if(!empty($student['name']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>Email</td>
            <td>
                <?php
                if(!empty($student['email']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>Phone</td>
            <td>
                <?php
                if(!empty($student['phone']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>CGPA</td>
            <td>
                <?php
                if(!empty($student['cgpa']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>Skills</td>
            <td>
                <?php
                if(!empty($student['skills']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>Resume</td>
            <td>
                <?php
                if(!empty($student['resume']))
                {
                    echo "<span class='status-completed'>Completed</span>";
                }
                else
                {
                    echo "<span class='status-pending'>Pending</span>";
                }
                ?>
            </td>
        </tr>

    </table>

</div>

</body>
</html>