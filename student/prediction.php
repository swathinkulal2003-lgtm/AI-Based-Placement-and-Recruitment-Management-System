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

/* Get Student Details */
$sql = "SELECT * FROM student WHERE id='$student_id'";
$result = mysqli_query($conn,$sql);
$student = mysqli_fetch_assoc($result);

$cgpa = $student['cgpa'];

/* Student Skills */
$studentSkills = array_map(
    'trim',
    explode(",", strtolower($student['skills']))
);

$studentSkills = array_filter($studentSkills);

/* Get Companies */
$company_sql = "SELECT * FROM company";
$company_result = mysqli_query($conn,$company_sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>AI Placement Prediction</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="navbar">

<div class="nav-left">

<a href="dashboard.php">Dashboard</a>

<a href="profile.php">Profile</a>

<a href="companies.php">Companies</a>

<a href="prediction.php" class="active">AI Prediction</a>

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

<div class="card prediction-box">

<h2>AI Placement Prediction</h2>

<p><b>Name :</b> <?php echo $student['name']; ?></p>

<p><b>CGPA :</b> <?php echo $cgpa; ?></p>

<p><b>Your Skills :</b> <?php echo $student['skills']; ?></p>

<br>

<table>

<tr>

<th>Company</th>

<th>Job Role</th>

<th>Skill Match</th>

<th>Matched Skills</th>

<th>Prediction</th>

</tr>

<?php

while($company=mysqli_fetch_assoc($company_result))
{

$companySkills = preg_split("/[\n,]+/",
strtolower($company['required_skills']));

$companySkills = array_map('trim',$companySkills);

$companySkills = array_filter($companySkills);

$matchedSkills = array_intersect($studentSkills,$companySkills);

$totalSkills = count($companySkills);

$matchedCount = count($matchedSkills);

$matchPercentage = 0;

if($totalSkills>0)
{
$matchPercentage = round(($matchedCount/$totalSkills)*100);
}

/* Prediction */

if($cgpa>=8 && $matchPercentage>=70)
{
$prediction="🟢 High Chance";
}
elseif($cgpa>=6 && $matchPercentage>=40)
{
$prediction="🟡 Medium Chance";
}
else
{
$prediction="🔴 Low Chance";
}

?>

<tr>

<td><?php echo $company['company_name']; ?></td>

<td><?php echo $company['job_role']; ?></td>

<td>

<?php echo $matchPercentage; ?>%

</td>

<td>

<?php

if($matchedCount>0)
{
echo implode(", ",$matchedSkills);
}
else
{
echo "No Matching Skills";
}

?>

</td>

<td>

<?php echo $prediction; ?>

</td>

</tr>

<?php

}

?>

</table>

<br>

<a href="dashboard.php" class="back-btn">

⬅ Back to Dashboard

</a>

</div>

</body>

</html>