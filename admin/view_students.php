<?php

session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$sql = "SELECT * FROM student";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">Dashboard</a>

        <a href="view_students.php" class="active">Students</a>

        <a href="add_company.php">Add Company</a>

        <a href="view_companies.php">Companies</a>

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

<h2>Registered Students</h2>

<table border="1" cellpadding="10">

<tr>
    <th>ID</th>
    <th>Reg No</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>CGPA</th>
    <th>Skills</th>
    <th>Resume Actions</th>
</tr>

<?php

while($row = mysqli_fetch_assoc($result))
{
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['regno']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['email']; ?></td>
    <td><?php echo $row['phone']; ?></td>
    <td><?php echo $row['cgpa']; ?></td>
    <td><?php echo $row['skills']; ?></td>
   <td>

<?php
if($row['resume'] != "")
{
?>

<a href="../uploads/resumes/<?php echo $row['resume']; ?>" target="_blank">
    View Resume
</a>

|

<a href="../uploads/resumes/<?php echo $row['resume']; ?>" download>
    Download Resume
</a>

<?php
}
else
{
    echo "No Resume";
}
?>

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