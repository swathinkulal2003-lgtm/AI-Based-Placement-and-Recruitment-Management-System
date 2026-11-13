<?php

session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

if (isset($_POST['add_company'])) {

    $company_name = $_POST['company_name'];
    $logo = "default.jpg";

if (!empty($_FILES['logo']['name'])) {

    $logo = time() . "_" . basename($_FILES['logo']['name']);

    $target = "../uploads/company_logo/" . $logo;

    move_uploaded_file($_FILES['logo']['tmp_name'], $target);
}
    $job_role = $_POST['job_role'];
    $required_skills = $_POST['required_skills'];
    $job_description = $_POST['job_description'];
    $package = $_POST['package'];
    $min_cgpa = $_POST['min_cgpa'];
    $total_rounds = $_POST['total_rounds'];
    $location = $_POST['location'];
    $experience = $_POST['experience'];

    $sql = "INSERT INTO company
(company_name, logo, job_role, required_skills, job_description, package, min_cgpa, total_rounds, location, experience)
VALUES
('$company_name', '$logo', '$job_role','$required_skills','$job_description','$package','$min_cgpa','$total_rounds','$location','$experience')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Company Added Successfully');</script>";
    } else {
        echo "<script>alert('Error Adding Company');</script>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Company</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <div class="navbar">

        <div class="nav-left">

            <a href="dashboard.php">Dashboard</a>

            <a href="view_students.php">Students</a>

            <a href="add_company.php" class="active">Add Company</a>

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

    <div class="card">

        <h2>Add Company</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Company Name</label>

            <input type="text"
                name="company_name"
                required>

            <br><br>

            <label>Company Logo</label>

            <input
                type="file"
                name="logo"
                accept=".jpg,.jpeg,.png">

            <br><br>

            <label>Job Role</label>

            <input type="text"
                name="job_role"
                required>

            <br><br>

            <label>Required Skills</label>

            <textarea
                name="required_skills"
                rows="4"
                placeholder="Example:
PHP, HTML, CSS, JavaScript, MySQL, Problem Solving"
                required></textarea>

            <br><br>

            <label>Job Description</label>

            <textarea
                name="job_description"
                rows="6"
                placeholder="Enter Job description"
                required></textarea>

            <br><br>

            <label>Package (LPA)</label>

            <input
                type="number"
                step="0.01"
                name="package"
                required>

            <br><br>

            <label>Minimum CGPA</label>

            <input
                type="number"
                step="0.01"
                name="min_cgpa"
                required>

            <br><br>

            <label>Total Interview Rounds</label>

            <input
                type="number"
                name="total_rounds"
                min="1"
                required>

            <br><br>

            <label>Location</label>

            <input
                type="text"
                name="location"
                placeholder="Example: Bangalore"
                required>

            <br><br>

            <label>Experience</label>

            <input
                type="text"
                name="experience"
                placeholder="Example: Fresher / 0-1 Years"
                required>

            <br><br>

            <input
                type="submit"
                name="add_company"
                value="Add Company"
                class="btn">

        </form>

        <br>

        <a href="dashboard.php" class="back-btn">
            ⬅ Back to Dashboard
        </a>

    </div>

</body>

</html>