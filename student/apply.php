<?php

session_start();

if(!isset($_SESSION['student_id']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

include("../send_mail.php");

/** @var mysqli $conn */

$student_id = $_SESSION['student_id'];

$student_sql = "SELECT name, email
FROM student
WHERE id='$student_id'";

$student_result = mysqli_query($conn, $student_sql);
$student = mysqli_fetch_assoc($student_result);

$student_name = $student['name'];
$student_email = $student['email'];

$company_id = $_GET['company_id'];

$company_sql = "SELECT company_name, job_role
FROM company
WHERE id='$company_id'";

$company_result = mysqli_query($conn, $company_sql);
$company = mysqli_fetch_assoc($company_result);

$company_name = $company['company_name'];
$job_role = $company['job_role'];

/* Check if already applied */
$check_sql = "SELECT * FROM placement
              WHERE student_id='$student_id'
              AND company_id='$company_id'";

$check_result = mysqli_query($conn, $check_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply Status</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="card">

<?php

if(mysqli_num_rows($check_result) > 0)
{
    echo "<h2>Application Status</h2>";
    echo "<p class='btn' style='background:red;'>Already Applied</p>";
}
else
{
    $sql = "INSERT INTO placement
        (student_id, company_id, status, completed_rounds)
        VALUES
        ('$student_id', '$company_id', 'Applied', 0)";

   if(mysqli_query($conn, $sql))
{
    $subject = "Application Submitted Successfully";

    $message = "
    <h2>Hello $student_name,</h2>

    <p>Your application has been submitted successfully.</p>

    <table border='1' cellpadding='8'>
        <tr>
            <td><b>Company</b></td>
            <td>$company_name</td>
        </tr>

        <tr>
            <td><b>Job Role</b></td>
            <td>$job_role</td>
        </tr>

        <tr>
            <td><b>Status</b></td>
            <td>Applied</td>
        </tr>
    </table>

    <br>

    <p>Thank you for applying.</p>

    <p><b>AI Placement & Recruitment Management System</b></p>
    ";

    sendEmail($student_email, $subject, $message);

    echo "<h2>Application Status</h2>";
    echo "<p class='btn' style='background:green;'>Application Submitted Successfully</p>";
}
    else
    {
        echo "<h2>Error</h2>";
        echo "<p class='btn' style='background:red;'>Something went wrong</p>";
    }
}

?>

<br><br>

<a class="btn" href="companies.php">Back to Companies</a>

</div>

</body>
</html>