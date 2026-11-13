<?php

session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */

/* Check interview ID */

/* Check and validate interview ID */

$interview_id = $_GET['id'] ?? '';

if (!is_numeric($interview_id) || $interview_id <= 0) {
    echo "<script>
            alert('Invalid interview ID.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}

$interview_id = (int)$interview_id;


/* Get interview details */

$sql = "SELECT
            interview_schedule.id,
            interview_schedule.student_id,
            interview_schedule.company_id,
           interview_schedule.round_name,
interview_schedule.status AS interview_status,
interview_schedule.interview_date,
interview_schedule.interview_time,

            student.name,
            student.regno,

            company.company_name,
            company.job_role,
            company.total_rounds,

            placement.completed_rounds

        FROM interview_schedule

        INNER JOIN student
            ON interview_schedule.student_id = student.id

        INNER JOIN company
            ON interview_schedule.company_id = company.id

        INNER JOIN placement
            ON interview_schedule.student_id = placement.student_id
            AND interview_schedule.company_id = placement.company_id

        WHERE interview_schedule.id = '$interview_id'

        LIMIT 1";


$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    echo "<script>
            alert('Interview not found.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}


$row = mysqli_fetch_assoc($result);


/* Only Upcoming interviews can be evaluated */

if ($row['interview_status'] != "Upcoming") {

    echo "<script>
            alert('This interview has already been completed.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}

/* Interview cannot be evaluated before its scheduled date and time */

$scheduled_datetime =
    $row['interview_date'] . ' ' . $row['interview_time'];

$current_datetime = date('Y-m-d H:i:s');

if (strtotime($scheduled_datetime) > strtotime($current_datetime)) {

    echo "<script>
            alert('This interview cannot be evaluated yet. The scheduled interview time has not arrived.');
            window.location.href='view_applications.php';
          </script>";
    exit();
}


/* Check whether evaluation already exists */

$check_sql = "SELECT id
              FROM interview_evaluation
              WHERE interview_id='$interview_id'
              LIMIT 1";

$check_result = mysqli_query($conn, $check_sql);

if ($check_result && mysqli_num_rows($check_result) > 0) {

    echo "<script>
            alert('This interview has already been evaluated.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}


/* Save evaluation */

if (isset($_POST['save_evaluation'])) {

    $technical_score = $_POST['technical_score'] ?? '';
    $communication_score = $_POST['communication_score'] ?? '';
    $problem_solving_score = $_POST['problem_solving_score'] ?? '';
    $confidence_score = $_POST['confidence_score'] ?? '';

    if (
        $technical_score === '' ||
        $communication_score === '' ||
        $problem_solving_score === '' ||
        $confidence_score === '' ||
        !is_numeric($technical_score) ||
        !is_numeric($communication_score) ||
        !is_numeric($problem_solving_score) ||
        !is_numeric($confidence_score)
    ) {
        echo "<script>
                alert('All evaluation scores are required and must be valid numbers.');
                window.location.href='interview_evaluation.php?id=$interview_id';
              </script>";
        exit();
    }

    $technical_score = floatval($technical_score);
    $communication_score = floatval($communication_score);
    $problem_solving_score = floatval($problem_solving_score);
    $confidence_score = floatval($confidence_score);


    $feedback = trim($_POST['feedback'] ?? '');

    if ($feedback === '') {
        echo "<script>
                alert('Interview feedback is required.');
                window.location.href='interview_evaluation.php?id=$interview_id';
              </script>";
        exit();
    }

    $feedback = mysqli_real_escape_string(
        $conn,
        $feedback
    );


    $recommendation = $_POST['recommendation'] ?? '';

    $allowed_recommendations = [
        'Recommended',
        'Not Recommended',
        'Needs Improvement'
    ];

    if (!in_array($recommendation, $allowed_recommendations, true)) {
        echo "<script>
                alert('Invalid recommendation selected.');
                window.location.href='view_applications.php';
              </script>";
        exit();
    }

    $recommendation = mysqli_real_escape_string(
        $conn,
        $recommendation
    );


    /* Validate scores */

    if (
        $technical_score < 0 ||
        $technical_score > 100 ||
        $communication_score < 0 ||
        $communication_score > 100 ||
        $problem_solving_score < 0 ||
        $problem_solving_score > 100 ||
        $confidence_score < 0 ||
        $confidence_score > 100
    ) {

        echo "<script>
                alert('Scores must be between 0 and 100.');
              </script>";

    } else {

        /* Calculate overall score */

        $overall_score =
            (
                $technical_score +
                $communication_score +
                $problem_solving_score +
                $confidence_score
            ) / 4;

        $overall_score =
            round($overall_score, 2);


        /* Final duplicate evaluation check */

        $duplicate_check_sql = "SELECT id
                                FROM interview_evaluation
                                WHERE interview_id='$interview_id'
                                LIMIT 1";

        $duplicate_check_result = mysqli_query(
            $conn,
            $duplicate_check_sql
        );

        if ($duplicate_check_result && mysqli_num_rows($duplicate_check_result) > 0) {

            echo "<script>
                    alert('This interview has already been evaluated.');
                    window.location.href='view_applications.php';
                  </script>";

            exit();
        }


        /* Insert evaluation */

        $insert_sql = "INSERT INTO interview_evaluation
        (
            interview_id,
            student_id,
            company_id,
            technical_score,
            communication_score,
            problem_solving_score,
            confidence_score,
            overall_score,
            feedback,
            recommendation
        )
        VALUES
        (
            '$interview_id',
            '{$row['student_id']}',
            '{$row['company_id']}',
            '$technical_score',
            '$communication_score',
            '$problem_solving_score',
            '$confidence_score',
            '$overall_score',
            '$feedback',
            '$recommendation'
        )";


        /* Start database transaction */

        mysqli_begin_transaction($conn);


        if (mysqli_query($conn, $insert_sql)) {

            /*
             * Mark interview as completed
             */

            $update_interview_sql =
                "UPDATE interview_schedule
                 SET status='Completed'
                 WHERE id='$interview_id'
                 AND status='Upcoming'";


            if (
                mysqli_query($conn, $update_interview_sql)
                && mysqli_affected_rows($conn) == 1
            ) {

                /*
                 * Increase completed rounds
                 */

                $update_rounds_sql =
                    "UPDATE placement
                     SET completed_rounds =
                         completed_rounds + 1
                     WHERE student_id='{$row['student_id']}'
                     AND company_id='{$row['company_id']}'
                     AND completed_rounds < {$row['total_rounds']}";


                if (
                    mysqli_query($conn, $update_rounds_sql)
                    && mysqli_affected_rows($conn) == 1
                ) {

                    /*
                     * Everything succeeded
                     */

                    mysqli_commit($conn);

                    echo "<script>
                            alert('Interview evaluation saved successfully. Interview completed.');
                            window.location.href='view_applications.php';
                          </script>";

                    exit();

                } else {

                    /*
                     * Rollback if completed rounds update fails
                     */

                    mysqli_rollback($conn);

                    echo "<script>
                            alert('Evaluation could not be completed. No changes were saved.');
                            window.location.href='view_applications.php';
                          </script>";

                    exit();
                }

            } else {

                /*
                 * Rollback if interview completion fails
                 */

                mysqli_rollback($conn);

                echo "<script>
                        alert('Evaluation could not be completed. No changes were saved.');
                        window.location.href='view_applications.php';
                      </script>";

                exit();
            }

        } else {

            /*
             * Rollback if evaluation INSERT fails
             */

            mysqli_rollback($conn);

            echo "<script>
                    alert('Error saving evaluation. No changes were saved.');
                    window.location.href='view_applications.php';
                  </script>";

            exit();
        }
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Interview Evaluation</title>

    <link
        rel="stylesheet"
        href="../css/style.css">

</head>

<body>


    <div class="navbar">

        <div class="nav-left">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="view_students.php">
                Students
            </a>

            <a href="view_companies.php">
                Companies
            </a>

            <a href="view_applications.php"
                class="active">
                Applications
            </a>

        </div>


        <div class="nav-right">

            <a
                href="logout.php"
                onclick="return confirm('Are you sure you want to logout?');">
                Logout
            </a>

        </div>

    </div>


    <div class="card evaluation-card">

        <h2>
            📋 Interview Evaluation
        </h2>


        <h3>
            <?php
            echo htmlspecialchars($row['name']);
            ?>
        </h3>


        <p>
            <strong>Registration No:</strong>

            <?php
            echo htmlspecialchars($row['regno']);
            ?>
        </p>


        <p>
            <strong>Company:</strong>

            <?php
            echo htmlspecialchars($row['company_name']);
            ?>
        </p>


        <p>
            <strong>Job Role:</strong>

            <?php
            echo htmlspecialchars($row['job_role']);
            ?>
        </p>


        <p>
            <strong>Interview Round:</strong>

            <?php
            echo htmlspecialchars($row['round_name']);
            ?>
        </p>


        <hr>


        <form method="POST">


            <label>
                Technical Knowledge
            </label>

            <input
                type="number"
                name="technical_score"
                min="0"
                max="100"
                step="0.01"
                placeholder="Enter score (0-100)"
                required>


            <label>
                Communication Skills
            </label>

            <input
                type="number"
                name="communication_score"
                min="0"
                max="100"
                step="0.01"
                placeholder="Enter score (0-100)"
                required>


            <label>
                Problem Solving
            </label>

            <input
                type="number"
                name="problem_solving_score"
                min="0"
                max="100"
                step="0.01"
                placeholder="Enter score (0-100)"
                required>


            <label>
                Confidence
            </label>

            <input
                type="number"
                name="confidence_score"
                min="0"
                max="100"
                step="0.01"
                placeholder="Enter score (0-100)"
                required>


            <label>
                Interview Feedback
            </label>

            <textarea
                name="feedback"
                placeholder="Enter interview feedback..."
                rows="5"
                required></textarea>


            <label>
                Recommendation
            </label>

            <select
                name="recommendation"
                required>

                <option value="">
                    Select Recommendation
                </option>

                <option value="Recommended">
                    Recommended
                </option>

                <option value="Not Recommended">
                    Not Recommended
                </option>

                <option value="Needs Improvement">
                    Needs Improvement
                </option>

            </select>


            <br><br>


            <input
                type="submit"
                name="save_evaluation"
                value="Save Evaluation & Complete Interview"
                class="btn">

        </form>


        <br>


        <a
            href="view_applications.php"
            class="back-btn">
            ⬅ Cancel
        </a>


    </div>


</body>

</html>