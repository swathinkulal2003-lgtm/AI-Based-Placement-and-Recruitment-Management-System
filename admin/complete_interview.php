<?php

session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/**
 * @var mysqli $conn
 */

// Check interview ID
if (!isset($_GET['id']))
{
    echo "<script>
            alert('Invalid interview.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$interview_id = intval($_GET['id']);

// Check whether interview exists
$sql = "SELECT id, status, interview_date, interview_time
        FROM interview_schedule
        WHERE id = '$interview_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0)
{
    echo "<script>
            alert('Interview not found.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$row = mysqli_fetch_assoc($result);

// Only Upcoming interviews can be evaluated
if ($row['status'] != "Upcoming")
{
    echo "<script>
            alert('This interview has already been completed.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// Combine interview date and time
$scheduled_datetime = $row['interview_date'] . ' ' . $row['interview_time'];

// Check whether the scheduled interview time has arrived
$current_datetime = date('Y-m-d H:i:s');

if (strtotime($scheduled_datetime) > strtotime($current_datetime))
{
    echo "<script>
            alert('This interview cannot be completed yet. The scheduled interview time has not arrived.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// Redirect to interview evaluation page
header("Location: interview_evaluation.php?id=" . $interview_id);
exit();

?>