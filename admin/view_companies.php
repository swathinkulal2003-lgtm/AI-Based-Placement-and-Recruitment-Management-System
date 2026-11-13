<?php

session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$sql = "SELECT * FROM company";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>View Companies</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">Dashboard</a>

        <a href="view_students.php">Students</a>

        <a href="add_company.php">Add Company</a>

        <a href="view_companies.php" class="active">Companies</a>

        <a href="view_applications.php">Applications</a>

    </div>

    <div class="nav-right">

        <a href="logout.php"
        onclick="return confirm('Are you sure you want to logout?');">
            Logout
        </a>

    </div>

</div>

<br>

<h2>Company List</h2>

<table border="1" cellpadding="10">

<tr>
    <th>ID</th>
    <th>Logo</th>
    <th>Company Name</th>
    <th>Job Role</th>
    <th>Package (LPA)</th>
    <th>Minimum CGPA</th>
    <th>Location</th>
    <th>Experience</th>
    <th>Required Skills</th>
    <th>Total Rounds</th>
    <th>AI Ranking</th>
    <th>Actions</th>
</tr>

<?php
while($row = mysqli_fetch_assoc($result))
{
?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td>

<?php

$logo = !empty($row['logo'])
    ? "../uploads/company_logo/" . $row['logo']
    : "../uploads/company_logo/default.jpg";

?>

<img
    src="<?php echo $logo; ?>"
    alt="Company Logo"
    width="60"
    height="60"
    style="border-radius:8px; object-fit:contain;">

</td>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>
    <td><?php echo htmlspecialchars($row['job_role']); ?></td>
    <td><?php echo htmlspecialchars($row['package']); ?></td>
    <td><?php echo htmlspecialchars($row['min_cgpa']); ?></td>
    <td><?php echo htmlspecialchars($row['location']); ?></td>
    <td><?php echo htmlspecialchars($row['experience']); ?></td>
    <td><?php echo htmlspecialchars($row['required_skills']); ?></td>
    <td>
    <?php echo htmlspecialchars($row['total_rounds']); ?>
</td>

<td>

    <a
        href="calculate_skill_score.php?company_id=<?php echo $row['id']; ?>"
        class="btn"
    >
        🤖 AI Ranking
    </a>

</td>

<td>

    <a
        href="manage_rounds.php?company_id=<?php echo $row['id']; ?>"
        class="btn"
    >
        ⚙ Manage Rounds
    </a>

</td>

</tr>
<?php
}
?>

</table>
<br><br>

<a href="dashboard.php" class="back-btn">
    ⬅ Back to Dashboard
</a>

</body>
</html>