<?php

session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */

$id = $_GET['id'] ?? '';

$status = $_GET['status'] ?? '';


// Allowed placement statuses
$allowed_statuses = [
    'Shortlisted',
    'Approved',
    'Rejected'
];

// Validate placement ID
if (!is_numeric($id) || $id <= 0) {
    echo "<script>
            alert('Invalid placement ID.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// Validate requested status
if (!in_array($status, $allowed_statuses, true)) {
    echo "<script>
            alert('Invalid placement status.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$id = (int)$id;

/* Get placement and company details */
$sql = "SELECT placement.completed_rounds,
               placement.status AS current_status,
               company.total_rounds
        FROM placement
        INNER JOIN company
        ON placement.company_id = company.id
        WHERE placement.id='$id'";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>
            alert('Placement application not found.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$row = mysqli_fetch_assoc($result);

/* Get student email, name, resume, company and job role */
$email_sql = "SELECT student.email,
                     student.name,
                     student.resume,
                     company.company_name,
                     company.job_role
              FROM placement
              INNER JOIN student
              ON placement.student_id = student.id
              INNER JOIN company
              ON placement.company_id = company.id
              WHERE placement.id='$id'";

$email_result = mysqli_query($conn, $email_sql);

if (!$email_result || mysqli_num_rows($email_result) == 0) {
    echo "<script>
            alert('Student or company details not found.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$email_data = mysqli_fetch_assoc($email_result);

$student_email = $email_data['email'];
$student_name = $email_data['name'];
$resume = $email_data['resume'];
$company_name = $email_data['company_name'];
$job_role = $email_data['job_role'];

/* Do not allow changes after Approved or Rejected */
if($row['current_status'] == "Approved" || $row['current_status'] == "Rejected")
{
    echo "<script>
            alert('This application is already finalized.');
            window.location='view_applications.php';
          </script>";
    exit();
}

/* Shortlist student for interview process */

if($status == "Shortlisted")
{
    /* Resume must be uploaded */

    if(empty($resume) || $resume == "NULL")
    {
        echo "<script>
                alert('Student has not uploaded a resume. Cannot shortlist.');
                window.location='view_applications.php';
              </script>";
        exit();
    }

    /*
        Shortlisting is allowed before all interview rounds
        are completed because shortlisted students need
        to go through the interview process.
    */
}

/* Approve only after student is shortlisted */
if($status == "Approved")
{
        /* Resume must be uploaded */
    if(empty($resume) || $resume == "NULL")
    {
        echo "<script>
                alert('Student has not uploaded a resume. Cannot approve.');
                window.location='view_applications.php';
              </script>";
        exit();
    }
    if($row['current_status'] != "Shortlisted")
    {
        echo "<script>
                alert('Student must be shortlisted before approval.');
                window.location='view_applications.php';
              </script>";
        exit();
    }

    if($row['completed_rounds'] < $row['total_rounds'])
    {
        echo "<script>
                alert('Student has not completed all interview rounds. Cannot approve.');
                window.location='view_applications.php';
              </script>";
        exit();
    }
}

/* Update status */
$update_sql = "UPDATE placement
               SET status='$status'
               WHERE id='$id'";

if(mysqli_query($conn, $update_sql))
{
    include("../send_mail.php");

    /* Email for Shortlisted */
    if($status == "Shortlisted")
{
    $subject = "Congratulations! You Have Been Shortlisted";

    $message = "
    <h2 style='color:blue;'>Congratulations, $student_name! 🎉</h2>

    <p>You have been shortlisted for the next round.</p>

    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
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
            <td style='color:blue;'><b>Shortlisted</b></td>
        </tr>
    </table>

    <br>

    <p>Please prepare for the next interview round.</p>

    <p><b>Best Regards,</b><br>
    Placement Cell</p>
    ";
}

    /* Email for Approved */
    elseif($status == "Approved")
{
    $subject = "Congratulations! You Have Been Selected";

    $message = "
    <h2 style='color:green;'>Congratulations, $student_name! 🎉</h2>

    <p>We are delighted to inform you that you have been selected.</p>

    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
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
            <td style='color:green;'><b>Approved</b></td>
        </tr>
    </table>

    <br>

    <p>We wish you a successful and rewarding career.</p>

    <p><b>Best Regards,</b><br>
    Placement Cell</p>
    ";
}

    /* Email for Rejected */
    elseif($status == "Rejected")
{
    $subject = "Placement Application Update";

    $message = "
    <h2 style='color:red;'>Placement Application Update</h2>

    <p>Dear $student_name,</p>

    <p>Thank you for participating in the recruitment process.</p>

    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
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
            <td style='color:red;'><b>Rejected</b></td>
        </tr>
    </table>

    <br>

    <p>Unfortunately, you were not selected this time. We encourage you to apply for future opportunities.</p>

    <p><b>Best Regards,</b><br>
    Placement Cell</p>
    ";
}

    /* Default email */
    else
    {
        $subject = "Placement Application Status Updated";

        $message = "
Dear $student_name,

Your application status has been updated.

Company : $company_name
Job Role : $job_role

New Status : $status

Thank you.

Placement Cell
";
    }

    /* Send Email */
   $mailSent = sendEmail($student_email, $subject, $message);

if($mailSent)
{
    echo "<script>
            alert('Status updated successfully and email notification sent.');
            window.location='view_applications.php';
          </script>";
    exit();
}
else
{
    echo "<script>
            alert('Status updated successfully, but email notification could not be sent.');
            window.location='view_applications.php';
          </script>";
    exit();
}
}
else
{
    echo "<script>
            alert('Error updating placement status.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

?>