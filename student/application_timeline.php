<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$student_id = $_SESSION['student_id'];

/*
    Check whether a placement ID was provided.
*/

if (!isset($_GET['placement_id'])) {
    header("Location: placement_status.php");
    exit();
}

$placement_id = intval($_GET['placement_id']);


/*
    Get placement information.

    IMPORTANT:
    We also check student_id so that one student
    cannot view another student's application.
*/

$sql = "SELECT
            placement.id,
            placement.status,
            placement.completed_rounds,

            company.company_name,
            company.job_role,
            company.total_rounds

        FROM placement

        INNER JOIN company
        ON placement.company_id = company.id

        WHERE placement.id = '$placement_id'
        AND placement.student_id = '$student_id'";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {

    echo "Application not found.";
    exit();

}

$row = mysqli_fetch_assoc($result);

$company_name = $row['company_name'];
$job_role = $row['job_role'];

$status = $row['status'];

$completed_rounds = intval(
    $row['completed_rounds']
);

$total_rounds = intval(
    $row['total_rounds']
);


/*
    Make sure completed rounds never
    exceed total rounds.
*/

if ($completed_rounds > $total_rounds) {
    $completed_rounds = $total_rounds;
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Application Timeline</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        .timeline-container {

            width: 90%;

            max-width: 800px;

            margin: 40px auto;

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

        }


        .timeline-header {

            text-align: center;

            margin-bottom: 35px;

        }


        .timeline-header h2 {

            margin-bottom: 8px;

        }


        .timeline-header p {

            color: #666;

            margin: 5px;

        }


        .timeline {

            position: relative;

            margin: 30px 0;

            padding-left: 45px;

        }


        .timeline::before {

            content: "";

            position: absolute;

            left: 17px;

            top: 0;

            bottom: 0;

            width: 3px;

            background: #ddd;

        }


        .timeline-item {

            position: relative;

            margin-bottom: 30px;

            padding: 5px 0;

        }


        .timeline-dot {

            position: absolute;

            left: -37px;

            top: 3px;

            width: 18px;

            height: 18px;

            border-radius: 50%;

            background: #ddd;

            border: 4px solid white;

            box-shadow:
                0 0 0 2px #ddd;

        }


        .timeline-item.completed
        .timeline-dot {

            background: #28a745;

            box-shadow:
                0 0 0 2px #28a745;

        }


        .timeline-item.current
        .timeline-dot {

            background: #007bff;

            box-shadow:
                0 0 0 3px #007bff;

        }


        .timeline-item.rejected
        .timeline-dot {

            background: #dc3545;

            box-shadow:
                0 0 0 2px #dc3545;

        }


        .timeline-content {

            padding: 15px;

            border-radius: 10px;

            background: #f8f9fa;

        }


        .timeline-item.completed
        .timeline-content {

            border-left:
                4px solid #28a745;

        }


        .timeline-item.current
        .timeline-content {

            border-left:
                4px solid #007bff;

        }


        .timeline-item.rejected
        .timeline-content {

            border-left:
                4px solid #dc3545;

        }


        .timeline-content h3 {

            margin: 0 0 5px 0;

        }


        .timeline-content p {

            margin: 0;

            color: #666;

        }


        .timeline-status {

            display: inline-block;

            margin-top: 8px;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

        }


        .status-completed {

            background: #d4edda;

            color: #155724;

        }


        .status-current {

            background: #cce5ff;

            color: #004085;

        }


        .status-upcoming {

            background: #e9ecef;

            color: #6c757d;

        }


        .status-rejected {

            background: #f8d7da;

            color: #721c24;

        }


        .current-status {

            text-align: center;

            margin-top: 30px;

            padding: 15px;

            border-radius: 10px;

            background: #f8f9fa;

        }


    </style>

</head>


<body>


<!-- NAVBAR -->

<div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="companies.php">
            Companies
        </a>

        <a href="prediction.php">
            AI Prediction
        </a>

        <a href="placement_status.php"
           class="active">
            Status
        </a>

        <a href="placement_results.php">
            Results
        </a>

        <a href="upload_resume.php">
            Upload Resume
        </a>

        <a href="job_recommendations.php">
            Job Recommendations
        </a>

    </div>


    <div class="nav-right">

        <a href="logout.php"
           onclick="return confirm('Are you sure you want to logout?');">

            Logout

        </a>

    </div>

</div>



<div class="timeline-container">


    <div class="timeline-header">

        <h2>
            📊 Application Timeline
        </h2>

        <p>

            <strong>
                <?php
                echo htmlspecialchars(
                    $company_name
                );
                ?>
            </strong>

        </p>

        <p>

            <?php
            echo htmlspecialchars(
                $job_role
            );
            ?>

        </p>

    </div>



    <div class="timeline">


        <!-- STEP 1 -->

        <div class="timeline-item completed">

            <div class="timeline-dot"></div>

            <div class="timeline-content">

                <h3>
                    ✓ Application Submitted
                </h3>

                <p>
                    Your application has been submitted successfully.
                </p>

                <span class="timeline-status status-completed">
                    Completed
                </span>

            </div>

        </div>



        <?php

        /*
            If application is rejected,
            show rejection immediately.
        */

        if ($status == "Rejected") {

        ?>

            <div class="timeline-item rejected">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        ❌ Application Rejected
                    </h3>

                    <p>
                        Unfortunately, your application
                        was not selected.
                    </p>

                    <span class="timeline-status status-rejected">
                        Rejected
                    </span>

                </div>

            </div>

        <?php

        } else {

        ?>


        <!-- STEP 2 -->

        <div class="timeline-item completed">

            <div class="timeline-dot"></div>

            <div class="timeline-content">

                <h3>
                    ✓ Application Reviewed
                </h3>

                <p>
                    Your application has been reviewed
                    by the placement team.
                </p>

                <span class="timeline-status status-completed">
                    Completed
                </span>

            </div>

        </div>



        <?php

        /*
            Show shortlisted step.
        */

        if (
            $status == "Shortlisted" ||
            $status == "Approved"
        ) {

        ?>

            <div class="timeline-item completed">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        ✓ Shortlisted
                    </h3>

                    <p>
                        Congratulations! You have been
                        shortlisted for the next stage.
                    </p>

                    <span class="timeline-status status-completed">
                        Completed
                    </span>

                </div>

            </div>

        <?php

        } else {

        ?>

            <div class="timeline-item current">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        ● Application Screening
                    </h3>

                    <p>
                        Your application is currently
                        under consideration.
                    </p>

                    <span class="timeline-status status-current">
                        In Progress
                    </span>

                </div>

            </div>

        <?php

        }



        /*
            Interview rounds.
        */

        for (
            $round = 1;
            $round <= $total_rounds;
            $round++
        ) {

            if ($round <= $completed_rounds) {

        ?>

                <div class="timeline-item completed">

                    <div class="timeline-dot"></div>

                    <div class="timeline-content">

                        <h3>

                            ✓ Round
                            <?php echo $round; ?>

                            Completed

                        </h3>

                        <p>

                            Interview round
                            <?php echo $round; ?>
                            has been completed.

                        </p>

                        <span class="timeline-status status-completed">

                            Completed

                        </span>

                    </div>

                </div>

        <?php

            } elseif (
                $round == $completed_rounds + 1
                &&
                $status != "Approved"
            ) {

        ?>

                <div class="timeline-item current">

                    <div class="timeline-dot"></div>

                    <div class="timeline-content">

                        <h3>

                            ● Round
                            <?php echo $round; ?>

                        </h3>

                        <p>

                            This is your next
                            interview round.

                        </p>

                        <span class="timeline-status status-current">

                            Current Stage

                        </span>

                    </div>

                </div>

        <?php

            } else {

        ?>

                <div class="timeline-item">

                    <div class="timeline-dot"></div>

                    <div class="timeline-content">

                        <h3>

                            ○ Round
                            <?php echo $round; ?>

                        </h3>

                        <p>

                            This interview round
                            is yet to be completed.

                        </p>

                        <span class="timeline-status status-upcoming">

                            Upcoming

                        </span>

                    </div>

                </div>

        <?php

            }

        }



        /*
            Final result.
        */

        if ($status == "Approved") {

        ?>

            <div class="timeline-item completed">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        🎉 Final Selection
                    </h3>

                    <p>

                        Congratulations!
                        You have been selected
                        for this position.

                    </p>

                    <span class="timeline-status status-completed">

                        Selected

                    </span>

                </div>

            </div>

        <?php

        } elseif (
            $completed_rounds >= $total_rounds
            &&
            $status != "Rejected"
        ) {

        ?>

            <div class="timeline-item current">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        ● Final Result
                    </h3>

                    <p>

                        All interview rounds have
                        been completed.

                        Waiting for the final result.

                    </p>

                    <span class="timeline-status status-current">

                        Result Pending

                    </span>

                </div>

            </div>

        <?php

        } else {

        ?>

            <div class="timeline-item">

                <div class="timeline-dot"></div>

                <div class="timeline-content">

                    <h3>
                        ○ Final Result
                    </h3>

                    <p>

                        Final result will be available
                        after all interview rounds
                        are completed.

                    </p>

                    <span class="timeline-status status-upcoming">

                        Pending

                    </span>

                </div>

            </div>

        <?php

        }

        ?>

        <?php

        }

        ?>

    </div>



    <!-- CURRENT STATUS -->

    <div class="current-status">

        <strong>
            Current Status:
        </strong>

        <?php

        if ($status == "Applied") {

            echo "🟡 Application Under Review";

        } elseif ($status == "Shortlisted") {

            echo "🟢 Shortlisted";

        } elseif ($status == "Approved") {

            echo "🎉 Selected";

        } else {

            echo "🔴 Rejected";

        }

        ?>

        <br><br>

        <strong>
            Interview Progress:
        </strong>

        <?php

        echo $completed_rounds
            . " / "
            . $total_rounds;

        ?>

    </div>


    <br>


    <a href="placement_status.php"
       class="btn">

        ⬅ Back to Placement Status

    </a>


</div>


</body>

</html>