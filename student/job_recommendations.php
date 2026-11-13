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

/* Get student data */
$sql = "SELECT * FROM student WHERE id='$student_id'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

$cgpa = $student['cgpa'];

$studentSkills = array_map(
    'trim',
    explode(",", strtolower($student['skills']))
);

$studentSkills = array_filter($studentSkills);

/* Get companies */
$sql2 = "SELECT * FROM company";
$result2 = mysqli_query($conn, $sql2);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Recommendations</title>
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
<a href="upload_resume.php">Upload Resume</a>
<a href="job_recommendations.php" class="active">Job Recommendations</a>

</div>

<div class="nav-right">

<a href="logout.php"
onclick="return confirm('Are you sure you want to logout?');">
Logout
</a>

</div>

</div>

<div class="card">
<h2>Recommended Jobs for You</h2>

<table>
<tr>
    <th>Company</th>
    <th>Role</th>
    <th>Match %</th>
    <th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result2)) { 

$status = "Not Recommended";

$companySkills = preg_split(
    "/[\n,]+/",
    strtolower($row['required_skills'])
);

$companySkills = array_map('trim', $companySkills);
$companySkills = array_filter($companySkills);

$matchedSkills = array_intersect($studentSkills, $companySkills);

$matchedCount = count($matchedSkills);
$totalSkills = count($companySkills);

$matchPercentage = 0;

if ($totalSkills > 0) {
    $matchPercentage = round(($matchedCount / $totalSkills) * 100);
}

if ($cgpa >= $row['min_cgpa'] && $matchPercentage >= 40) {
    $status = "Recommended";
}

?>

<tr>
    <td><?php echo $row['company_name']; ?></td>
    <td><?php echo htmlspecialchars($row['job_role']); ?></td>

<td><?php echo $matchPercentage; ?>%</td>

<td><?php echo $status; ?></td>
</tr>

<?php } ?>

</table>

<br>

<a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

</div>

</body>
</html>