<?php
session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */
/*
    Dashboard Statistics
*/

// Total Students
$student_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM student"
);
$student_data = mysqli_fetch_assoc($student_result);
$total_students = $student_data['total'];


// Total Companies
$company_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM company"
);
$company_data = mysqli_fetch_assoc($company_result);
$total_companies = $company_data['total'];


// Total Applications
$application_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM placement"
);
$application_data = mysqli_fetch_assoc($application_result);
$total_applications = $application_data['total'];


// Approved Students
$approved_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM placement
     WHERE status = 'Approved'"
);
$approved_data = mysqli_fetch_assoc($approved_result);
$total_approved = $approved_data['total'];

// Shortlisted Students
$shortlisted_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM placement
     WHERE status = 'Shortlisted'"
);

$shortlisted_data = mysqli_fetch_assoc(
    $shortlisted_result
);

$total_shortlisted = $shortlisted_data['total'];


// Applied Students
$applied_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM placement
     WHERE status = 'Applied'"
);

$applied_data = mysqli_fetch_assoc(
    $applied_result
);

$total_applied = $applied_data['total'];


// Rejected Students
$rejected_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM placement
     WHERE status = 'Rejected'"
);

$rejected_data = mysqli_fetch_assoc(
    $rejected_result
);

$total_rejected = $rejected_data['total'];

// Total Interviews
$interview_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM interview_schedule"
);

$interview_data = mysqli_fetch_assoc(
    $interview_result
);

$total_interviews = $interview_data['total'];



date_default_timezone_set('Asia/Kolkata');

// Keep MySQL time comparisons in Indian Standard Time.
mysqli_query($conn, "SET time_zone = '+05:30'");

// Upcoming interviews: scheduled time is still in the future.
$upcoming_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM interview_schedule
     WHERE status = 'Upcoming'
       AND TIMESTAMP(interview_date, interview_time) > NOW()"
);

$upcoming_data = mysqli_fetch_assoc($upcoming_result);
$total_upcoming = $upcoming_data['total'];

// Interviews whose scheduled time has arrived or passed.
$time_reached_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM interview_schedule
     WHERE status = 'Upcoming'
       AND TIMESTAMP(interview_date, interview_time) <= NOW()"
);

$time_reached_data = mysqli_fetch_assoc($time_reached_result);
$total_time_reached = $time_reached_data['total'];

// Completed interviews remain separate.
$completed_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM interview_schedule
     WHERE status = 'Completed'"
);

$completed_data = mysqli_fetch_assoc($completed_result);
$total_completed = $completed_data['total'];



// Completed Interviews
$completed_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM interview_schedule
     WHERE status = 'Completed'"
);

$completed_data = mysqli_fetch_assoc(
    $completed_result
);

$total_completed = $completed_data['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <div class="nav-left">
        <a href="dashboard.php" class="active">Dashboard</a>
<a href="view_students.php">Students</a>
<a href="add_company.php">Add Company</a>
<a href="view_companies.php">Companies</a>
<a href="view_applications.php">Applications</a>
    </div>

    <div class="nav-right">
        <a href="logout.php"
        onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
</div>

<div class="card">

    <h2>Welcome Admin</h2>

    <p class="dashboard-text">
        Placement Management Control Panel
    </p>

</div>


<!-- DASHBOARD STATISTICS -->

<div class="card">

    <h2>📊 Placement Statistics</h2>

    <div class="dashboard-stats">

        <div class="stat-card">
            <h3>👨‍🎓</h3>
            <h2><?php echo $total_students; ?></h2>
            <p>Total Students</p>
        </div>


        <div class="stat-card">
            <h3>🏢</h3>
            <h2><?php echo $total_companies; ?></h2>
            <p>Total Companies</p>
        </div>


        <div class="stat-card">
            <h3>📝</h3>
            <h2><?php echo $total_applications; ?></h2>
            <p>Total Applications</p>
        </div>


        <div class="stat-card">
            <h3>🎉</h3>
            <h2><?php echo $total_approved; ?></h2>
            <p>Approved Students</p>
        </div>

    </div>

</div>

<!-- PLACEMENT STATUS -->

<div class="card">

    <h2>📈 Application Status</h2>

    <div class="dashboard-stats">

        <div class="stat-card">
            <h3>🟢</h3>
            <h2><?php echo $total_approved; ?></h2>
            <p>Approved</p>
        </div>


        <div class="stat-card">
            <h3>🔵</h3>
            <h2><?php echo $total_shortlisted; ?></h2>
            <p>Shortlisted</p>
        </div>


        <div class="stat-card">
            <h3>🟡</h3>
            <h2><?php echo $total_applied; ?></h2>
            <p>Applied</p>
        </div>


        <div class="stat-card">
            <h3>🔴</h3>
            <h2><?php echo $total_rejected; ?></h2>
            <p>Rejected</p>
        </div>

    </div>

</div>


<!-- INTERVIEW STATISTICS -->

<div class="card">

    <h2>📅 Interview Statistics</h2>

    <div class="dashboard-stats">

        <div class="stat-card">
            <h3>📋</h3>
            <h2><?php echo $total_interviews; ?></h2>
            <p>Total Interviews</p>
        </div>


        <div class="stat-card">
            <h3>⏳</h3>
            <h2><?php echo $total_upcoming; ?></h2>
            <p>Upcoming Interviews</p>
        </div>


        <div class="stat-card">
            <h3>✅</h3>
            <h2><?php echo $total_completed; ?></h2>
            <p>Completed Interviews</p>
        </div>

    </div>

</div>
</body>
</html>