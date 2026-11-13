<?php

session_start();
date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

// PHPMailer
require '../mail/src/Exception.php';
require '../mail/src/PHPMailer.php';
require '../mail/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
    Check placement ID
*/

if(!isset($_GET['id']))
{
    echo "<script>
            alert('Invalid application.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$placement_id = intval($_GET['id']);


/*
    Get student and company information
*/

$sql = "SELECT
            placement.id,
            placement.student_id,
            placement.company_id,
            placement.status,
            placement.completed_rounds,

            student.name AS student_name,
            student.email AS student_email,

            company.company_name,
            company.job_role,
            company.total_rounds

        FROM placement

        INNER JOIN student
        ON placement.student_id = student.id

        INNER JOIN company
        ON placement.company_id = company.id

        WHERE placement.id = '$placement_id'";


$result = mysqli_query($conn, $sql);


if(!$result || mysqli_num_rows($result) == 0)
{
    echo "<script>
            alert('Application not found.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}


$row = mysqli_fetch_assoc($result);


/*
    Only shortlisted students can be scheduled
*/

if($row['status'] != "Shortlisted")
{
    echo "<script>
            alert('Interview can be scheduled only for shortlisted candidates.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}


/*
    Get current completed rounds
*/

$completed_rounds = intval($row['completed_rounds']);

$total_rounds = intval($row['total_rounds']);

// Check whether all interview rounds are already completed
// Check how many interview rounds are already scheduled
$scheduled_count_sql = "SELECT COUNT(*) AS scheduled_count
                        FROM interview_schedule
                        WHERE student_id = '{$row['student_id']}'
                        AND company_id = '{$row['company_id']}'";

$scheduled_count_result = mysqli_query(
    $conn,
    $scheduled_count_sql
);

$scheduled_count_row = mysqli_fetch_assoc(
    $scheduled_count_result
);

$scheduled_rounds = intval(
    $scheduled_count_row['scheduled_count']
);

// Get already scheduled interview round names
$scheduled_round_names = [];

$scheduled_names_sql = "SELECT round_name
                        FROM interview_schedule
                        WHERE student_id = '{$row['student_id']}'
                        AND company_id = '{$row['company_id']}'";

$scheduled_names_result = mysqli_query(
    $conn,
    $scheduled_names_sql
);

while($scheduled_name_row = mysqli_fetch_assoc($scheduled_names_result))
{
    $scheduled_round_names[] = $scheduled_name_row['round_name'];
}

// Get company recruitment rounds
$recruitment_rounds = [];

$rounds_sql = "SELECT round_number, round_name
               FROM recruitment_rounds
               WHERE company_id = '{$row['company_id']}'
               ORDER BY round_number ASC";

$rounds_result = mysqli_query($conn, $rounds_sql);

while($round_row = mysqli_fetch_assoc($rounds_result))
{
    $recruitment_rounds[] = $round_row;
}

// Check whether recruitment rounds are configured
if(empty($recruitment_rounds))
{
    echo "<script>
            alert('No recruitment rounds have been configured for this company. Please configure the rounds first.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}
// Check whether any interview is still Upcoming

$upcoming_count_sql = "SELECT COUNT(*) AS upcoming_count
                       FROM interview_schedule
                       WHERE student_id = '{$row['student_id']}'
                       AND company_id = '{$row['company_id']}'
                       AND status = 'Upcoming'";

$upcoming_count_result = mysqli_query(
    $conn,
    $upcoming_count_sql
);

$upcoming_count_row = mysqli_fetch_assoc(
    $upcoming_count_result
);

$upcoming_count = intval(
    $upcoming_count_row['upcoming_count']
);


// Prevent scheduling another interview
// while a previous interview is still Upcoming

if ($upcoming_count > 0)
{
    echo "<script>
            alert('The previous interview round must be completed before scheduling the next round.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// Prevent scheduling more rounds than total rounds

if($scheduled_rounds >= $total_rounds)
{
    echo "<script>
            alert('All interview rounds have already been scheduled. No further interview can be scheduled.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// // Prevent scheduling more rounds than total rounds
// if ($total_taken_rounds >= $total_rounds)
// {
//     echo "<script>
//             alert('All interview rounds have already been completed or scheduled. No further interview can be scheduled.');
//             window.location.href='view_applications.php';
//           </script>";
//     exit();
// }

/*
    Process form
*/

if(isset($_POST['schedule_interview']))
{

// Verify that the candidate is still shortlisted

if($row['status'] != "Shortlisted")
{
    echo "<script>
            alert('Interview cannot be scheduled because the candidate is no longer shortlisted.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

    $round_name = trim($_POST['round_name']);
    // Check recruitment round sequence
if($round_name != "Custom Round" && !empty($recruitment_rounds))
{
    $next_round = null;

    foreach($recruitment_rounds as $round)
    {
        if(!in_array($round['round_name'], $scheduled_round_names))
        {
            $next_round = $round;
            break;
        }
    }

    if($next_round !== null &&
       $round_name != $next_round['round_name'])
    {
        echo "<script>
                alert('Please schedule the rounds in sequence. The next round is: " .
                addslashes($next_round['round_name']) . "');
                window.location.href='schedule_interview.php?id=$placement_id';
              </script>";
        exit();
    }
}

if($round_name == "Custom Round")
{
    $round_name = trim($_POST['custom_round_name']);

    if(empty($round_name))
    {
        echo "<script>
                alert('Please enter the custom interview round name.');
              </script>";
        exit();
    }
}

    // Check whether this round is already scheduled

$check_sql = "SELECT id
              FROM interview_schedule
              WHERE student_id = '{$row['student_id']}'
              AND company_id = '{$row['company_id']}'
              AND round_name = '$round_name'
              LIMIT 1";

$check_result = mysqli_query($conn, $check_sql);

if(mysqli_num_rows($check_result) > 0)
{
    echo "<script>
            alert('This interview round has already been scheduled for this student.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

// Prevent scheduling another round if all rounds are already completed

if($completed_rounds >= $total_rounds)
{
    echo "<script>
            alert('All interview rounds have already been completed. No further interview can be scheduled.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}
    $interview_date = $_POST['interview_date'];
    $interview_time = $_POST['interview_time'];
    $mode = trim($_POST['mode']);
    $venue_or_link = trim($_POST['venue_or_link']);
    $instructions = trim($_POST['instructions']);
// Online interviews must have a meeting link
if($mode == "Online" && empty($venue_or_link))
{
    echo "<script>
            alert('Please enter the meeting link for an online interview.');
            window.location.href='schedule_interview.php?id=$placement_id';
          </script>";
    exit();
}


    // Check whether interview date and time are in the future
$interview_datetime = $interview_date . ' ' . $interview_time;

if(strtotime($interview_datetime) <= time())
{
    echo "<script>
            alert('Interview date and time must be in the future.');
            window.location.href='schedule_interview.php?id=$placement_id';
          </script>";
    exit();
}
    /*
        Insert interview schedule
    */

    $insert_sql = "INSERT INTO interview_schedule
    (
        student_id,
        company_id,
        round_name,
        interview_date,
        interview_time,
        mode,
        venue_or_link,
        instructions,
        status
    )
    VALUES
    (
        '{$row['student_id']}',
        '{$row['company_id']}',
        '$round_name',
        '$interview_date',
        '$interview_time',
        '$mode',
        '$venue_or_link',
        '$instructions',
        'Upcoming'
    )";


   if(mysqli_query($conn, $insert_sql))
{
    // ----------------------------------------
    // SEND INTERVIEW EMAIL TO STUDENT
    // ----------------------------------------

    $mail = new PHPMailer(true);

    try
    {
        // SMTP configuration
        
$smtpConfig = require __DIR__ . '/../mail/smtp_config.php';

$mail->isSMTP();

$mail->CharSet = 'UTF-8';

$mail->Host = $smtpConfig['host'];

$mail->SMTPAuth = true;

$mail->Username = $smtpConfig['username'];

$mail->Password = $smtpConfig['password'];

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$mail->Port = $smtpConfig['port'];



        // Sender
        $mail->setFrom(
    $smtpConfig['username'],
    'Placement Management System'
);


        // Student email
        $mail->addAddress(
            $row['student_email'],
            $row['student_name']
        );


        // Email format
        $mail->isHTML(true);


        // Email subject
        $mail->Subject =
            '📅 Interview Scheduled - '
            . $row['company_name'];


        // Meeting information

        $meeting_information = '';

        if(!empty($venue_or_link))
        {
            $meeting_information =
                '<p>
                    <strong>📍 Venue / Meeting Link:</strong><br>'
                . htmlspecialchars($venue_or_link) .
                '</p>';
        }


        // Email body

        $mail->Body = '

        <div style="
            font-family:Arial;
            line-height:1.6;
        ">

            <h2>
                📅 Interview Scheduled
            </h2>

            <p>
                Dear <strong>'
                . htmlspecialchars($row['student_name']) .
                '</strong>,
            </p>

            <p>
                Your interview has been scheduled for the
                following position:
            </p>

            <hr>

            <p>
                <strong>🏢 Company:</strong>
                '
                . htmlspecialchars($row['company_name']) .
                '
            </p>

            <p>
                <strong>💼 Job Role:</strong>
                '
                . htmlspecialchars($row['job_role']) .
                '
            </p>

            <p>
                <strong>🎯 Interview Round:</strong>
                '
                . htmlspecialchars($round_name) .
                '
            </p>

            <p>
                <strong>📅 Date:</strong>
                '
                . date(
                    "d-m-Y",
                    strtotime($interview_date)
                ) .
                '
            </p>

            <p>
                <strong>⏰ Time:</strong>
                '
                . date(
                    "h:i A",
                    strtotime($interview_time)
                ) .
                '
            </p>

            <p>
                <strong>💻 Mode:</strong>
                '
                . htmlspecialchars($mode) .
                '
            </p>

            '
            . $meeting_information .
            '

            <p>
                <strong>📝 Instructions:</strong><br>
                '
                . nl2br(
                    htmlspecialchars($instructions)
                ) .
                '
            </p>

            <hr>

            <p>
                Please be prepared and attend the interview
                on time.
            </p>

            <p>
                <strong>Best wishes for your interview! 🎓</strong>
            </p>

            <p>
                Regards,<br>
                <strong>Placement Management System</strong>
            </p>

        </div>
        ';


        // Send email
        $mail->send();


        echo "<script>
                alert('Interview scheduled successfully and email notification sent to the student!');
                window.location.href='view_applications.php';
              </script>";

        exit();

    }
    catch(Exception $e)
    {
        // Interview is already saved in database.
        // Email failed, but scheduling should not be lost.

        echo "<script>
                alert('Interview scheduled successfully, but email notification could not be sent.');
                window.location.href='view_applications.php';
              </script>";

        exit();
    }
}
    else
    {

        echo "<script>
                alert('Error scheduling interview.');
              </script>";

    }

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Schedule Interview
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>


<!-- NAVBAR -->

<div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="view_students.php">
            Students
        </a>

        <a href="add_company.php">
            Add Company
        </a>

        <a href="view_companies.php">
            Companies
        </a>

        <a
            href="view_applications.php"
            class="active"
        >
            Applications
        </a>

    </div>


    <div class="nav-right">

        <a
            href="logout.php"
            onclick="return confirm('Are you sure you want to logout?');"
        >
            Logout
        </a>

    </div>

</div>


<br>


<!-- APPLICATION INFORMATION -->

<div class="card">

    <h2>
        📅 Schedule Interview
    </h2>


    <hr>


    <p>

        <strong>
            Student:
        </strong>

        <?php

        echo htmlspecialchars(
            $row['student_name']
        );

        ?>

    </p>


    <p>

        <strong>
            Email:
        </strong>

        <?php

        echo htmlspecialchars(
            $row['student_email']
        );

        ?>

    </p>


    <p>

        <strong>
            Company:
        </strong>

        <?php

        echo htmlspecialchars(
            $row['company_name']
        );

        ?>

    </p>


    <p>

        <strong>
            Job Role:
        </strong>

        <?php

        echo htmlspecialchars(
            $row['job_role']
        );

        ?>

    </p>


    <p>

        <strong>
            Placement Status:
        </strong>

        <span class="status-shortlisted">
            Shortlisted
        </span>

    </p>


    <p>

        <strong>
            Interview Progress:
        </strong>

        <?php

        echo $completed_rounds
             . " / "
             . $total_rounds;

        ?>

    </p>

</div>


<br>


<!-- SCHEDULE FORM -->

<div class="card">

    <h2>
        📝 Interview Details
    </h2>


    <form
        method="POST"
    >


        <!-- ROUND -->

        <label>
            Interview Round
        </label>

       <select
    name="round_name"
    id="round_name"
    required
    onchange="toggleCustomRound()"
>
    <option value="">
        Select Interview Round
    </option>

    <?php if(!empty($recruitment_rounds)) { ?>

        <?php foreach($recruitment_rounds as $round) { ?>

            <?php
            if(in_array($round['round_name'], $scheduled_round_names))
            {
                continue;
            }
            ?>

            
            <option value="<?php echo htmlspecialchars($round['round_name']); ?>">
                Round <?php echo htmlspecialchars($round['round_number']); ?>
                - <?php echo htmlspecialchars($round['round_name']); ?>
            </option>

        <?php } ?>

    <?php } else { ?>

        <option value="" disabled>
            No recruitment rounds configured
        </option>

    <?php } ?>

    <option value="Custom Round">
        Other / Custom Round
    </option>

</select>

<br><br>

<div id="custom_round_box" style="display:none;">

    <label>
        Enter Custom Interview Round
    </label>

    <input
        type="text"
        name="custom_round_name"
        id="custom_round_name"
        placeholder="Example: Group Discussion / Client Interview"
    >

</div>


        <br><br>


        <!-- DATE -->

        <label>
            Interview Date
        </label>

        <input
            type="date"
            name="interview_date"
            required
        >


        <br><br>


        <!-- TIME -->

        <label>
            Interview Time
        </label>

        <input
            type="time"
            name="interview_time"
            required
        >


        <br><br>


        <!-- MODE -->

        <label>
            Interview Mode
        </label>

        <select
            name="mode"
            required
        >

            <option value="">
                Select Mode
            </option>

            <option value="College">
                College
            </option>

            <option value="Online">
                Online
            </option>

            <option value="Company">
                Company
            </option>

        </select>


        <br><br>


        <!-- VENUE / LINK -->

        <label>
            Venue / Meeting Link
        </label>

        <input
            type="text"
            name="venue_or_link"
            placeholder="Example: Seminar Hall / Google Meet Link"
        >


        <br><br>


        <!-- INSTRUCTIONS -->

        <label>
            Instructions
        </label>

        <textarea
            name="instructions"
            rows="5"
            placeholder="Example: Bring your college ID card and resume."
        ></textarea>


        <br><br>


        <input
            type="submit"
            name="schedule_interview"
            value="📅 Schedule Interview"
            class="btn"
        >


    </form>


    <br>


    <a
        href="view_applications.php"
        class="back-btn"
    >

        ← Back to Applications

    </a>


</div>

<script>

function toggleCustomRound()
{
    var roundSelect = document.getElementById("round_name");
    var customBox = document.getElementById("custom_round_box");
    var customInput = document.getElementById("custom_round_name");

    if(roundSelect.value === "Custom Round")
    {
        customBox.style.display = "block";
        customInput.required = true;
    }
    else
    {
        customBox.style.display = "none";
        customInput.required = false;
        customInput.value = "";
    }
}

</script>


</body>

</html>