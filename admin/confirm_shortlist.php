<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */


/*
    PHPMailer
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../mail/src/Exception.php';
require '../mail/src/PHPMailer.php';
require '../mail/src/SMTP.php';


/*
    Check submitted data
*/

if (
    !isset($_POST['company_id']) ||
    !isset($_POST['selected_students'])
) {

    echo "<script>
            alert('Invalid shortlist request.');
            window.location.href =
                'view_companies.php';
          </script>";

    exit();
}


$company_id =
    intval($_POST['company_id']);


$selected_students =
    $_POST['selected_students'];


/*
    Make sure candidates were selected
*/

if (
    !is_array($selected_students) ||
    count($selected_students) == 0
) {

    echo "<script>
            alert('No candidates selected.');
            history.back();
          </script>";

    exit();
}


/*
    Clean student IDs
*/

$student_ids = [];

foreach (
    $selected_students
    as $student_id
) {

    $student_id =
        intval($student_id);

    if ($student_id > 0) {

        $student_ids[] =
            $student_id;
    }
}


/*
    Remove duplicate IDs
*/

$student_ids =
    array_unique(
        $student_ids
    );


if (
    count($student_ids) == 0
) {

    echo "<script>
            alert('Invalid candidate selection.');
            history.back();
          </script>";

    exit();
}


/*
    Get company details
*/

$company_sql =
    "SELECT
        company_name,
        job_role,
        location

     FROM company

     WHERE id = '$company_id'";


$company_result =
    mysqli_query(
        $conn,
        $company_sql
    );


if (
    !$company_result ||
    mysqli_num_rows($company_result) == 0
) {

    echo "Company not found.";
    exit();
}


$company =
    mysqli_fetch_assoc(
        $company_result
    );


$company_name =
    $company['company_name'];

$job_role =
    $company['job_role'];

$location =
    $company['location'];


/*
    Counters
*/

$success_count = 0;

$email_failed_count = 0;


/*
    Process each selected student
*/

foreach (
    $student_ids
    as $student_id
) {


    /*
        Get student details
    */

    $student_sql =
        "SELECT
            id,
            name,
            email

         FROM student

         WHERE id = '$student_id'";


    $student_result =
        mysqli_query(
            $conn,
            $student_sql
        );


    if (
        !$student_result ||
        mysqli_num_rows($student_result) == 0
    ) {

        continue;
    }


    $student =
        mysqli_fetch_assoc(
            $student_result
        );


    $student_name =
        $student['name'];

    $student_email =
        $student['email'];


    /*
        Update placement status
    */

    $update_sql =
        "UPDATE placement

         SET status = 'Shortlisted'

         WHERE student_id =
               '$student_id'

         AND company_id =
             '$company_id'";


    $update_result =
        mysqli_query(
            $conn,
            $update_sql
        );


    if (!$update_result) {

        continue;
    }


    /*
        Send email
    */

    $mail =
        new PHPMailer(true);


    try {


        /*
            SMTP configuration
        */

        $mail->isSMTP();


        $smtpConfig = require __DIR__ . '/../mail/smtp_config.php';

        $mail->isSMTP();

        $mail->Host = $smtpConfig['host'];

        $mail->SMTPAuth = true;

        $mail->Username = $smtpConfig['username'];

        $mail->Password = $smtpConfig['password'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = $smtpConfig['port'];



        /*
            Sender
        */

        $mail->setFrom(
    $smtpConfig['username'],
    'Placement Management System'
);

        /*
            Receiver
        */

        $mail->addAddress(
            $student_email,
            $student_name
        );


        /*
            Email format
        */

        $mail->isHTML(true);


        $mail->Subject =
            "Congratulations! You Have Been Shortlisted - "
            . $company_name;


        $mail->Body =

            "
            <div style='
                font-family: Arial, sans-serif;
                line-height: 1.6;
            '>

                <h2>
                    🎉 Congratulations, "
            . htmlspecialchars(
                $student_name
            )
            . "!
                </h2>

                <p>
                    We are pleased to inform you that
                    you have been <strong>shortlisted</strong>
                    for the following placement opportunity.
                </p>

                <hr>

                <p>
                    <strong>Company:</strong>
                    "
            . htmlspecialchars(
                $company_name
            )
            . "
                </p>

                <p>
                    <strong>Job Role:</strong>
                    "
            . htmlspecialchars(
                $job_role
            )
            . "
                </p>

                <p>
                    <strong>Location:</strong>
                    "
            . htmlspecialchars(
                $location
            )
            . "
                </p>

                <p>
                    <strong>Status:</strong>
                    Shortlisted
                </p>

                <hr>

                <p>
                    Please log in to the Placement
                    Management System regularly for
                    further updates regarding the
                    upcoming selection rounds.
                </p>

                <p>
                    Best wishes for the next stage
                    of the selection process!
                </p>

                <br>

                <p>
                    Regards,<br>
                    <strong>
                        Placement Management System
                    </strong>
                </p>

            </div>
            ";


        /*
            Send email
        */

        $mail->send();


        $success_count++;
    } catch (Exception $e) {

        /*
            Status has already been updated,
            but email could not be sent.
        */

        $email_failed_count++;
    }
}


/*
    Final message
*/

echo "<script>";

echo "alert(";

echo "'Shortlist confirmed successfully!\\\\n\\\\n"
    . $success_count
    . " candidate(s) shortlisted.\\\\n"
    . $email_failed_count
    . " email(s) failed to send.'";

echo ");";


echo "window.location.href =
      'view_applications.php';";


echo "</script>";
