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
    Get student's placement applications
*/

$sql = "SELECT
            placement.id AS placement_id,
            placement.company_id,
            company.company_name,
            company.job_role,
            company.total_rounds,
            placement.status,
            placement.completed_rounds
        FROM placement
        INNER JOIN company
            ON placement.company_id = company.id
        WHERE placement.student_id = '$student_id'";

$result = mysqli_query($conn, $sql);


/*
    Get interview rounds for the student
*/

$interview_sql = "
    SELECT
        interview_schedule.student_id,
        interview_schedule.company_id,
        interview_schedule.round_name,
        interview_schedule.status
    FROM interview_schedule
    WHERE interview_schedule.student_id = '$student_id'
    ORDER BY interview_schedule.interview_date ASC,
             interview_schedule.interview_time ASC
";

$interview_result = mysqli_query($conn, $interview_sql);

$interview_rounds = [];

while ($interview = mysqli_fetch_assoc($interview_result)) {

    $key = $interview['student_id'] . '_' . $interview['company_id'];

    $interview_rounds[$key][] = [
        'round_name' => $interview['round_name'],
        'status' => $interview['status']
    ];
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Placement Status</title>

    <link rel="stylesheet" href="../css/style.css">

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

            <a href="placement_status.php" class="active">
                Status
            </a>

            <a href="placement_results.php">
                Results
            </a>

            <a href="interview_schedule.php">
                📅 Interviews
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


    <!-- PLACEMENT APPLICATIONS -->

    <div class="card glow">

        <h2>📋 My Placement Applications</h2>


        <table class="modern-table">

            <tr>

                <th>Company</th>

                <th>Job Role</th>

                <th>Progress</th>

                <th>Status</th>

                <th>Result</th>

                <th>Interview Rounds</th>

                <th>Timeline</th>

            </tr>


            <?php

            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

                    /*
                        Calculate interview progress
                    */

                    $completed = (int)$row['completed_rounds'];

                    $total = (int)$row['total_rounds'];

                    $progress = 0;

                    if ($total > 0) {

                        $progress = round(
                            ($completed / $total) * 100
                        );

                    }

                    if ($progress > 100) {
                        $progress = 100;
                    }

                    $pending = max(
                        0,
                        $total - $completed
                    );

            ?>

                    <tr>

                        <!-- COMPANY -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row['company_name']
                            );
                            ?>

                        </td>


                        <!-- JOB ROLE -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row['job_role']
                            );
                            ?>

                        </td>


                        <!-- PROGRESS -->

                        <td>

                            <strong>

                                <?php
                                echo $completed . " / " . $total;
                                ?>

                                rounds

                            </strong>


                            <div style="
                                width:150px;
                                height:10px;
                                background:#e5e7eb;
                                border-radius:10px;
                                margin-top:8px;
                                overflow:hidden;
                            ">

                                <div style="width:<?php echo $progress; ?>%;height:100%;background:#4CAF50;border-radius:10px;"></div>

                            </div>


                            <small>

                                <?php
                                echo $progress;
                                ?>

                                % completed

                            </small>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php

                            if ($row['status'] == "Applied") {

                                echo "
                                    <span class='status-pending'>
                                        Applied
                                    </span>
                                ";

                            } elseif ($row['status'] == "Shortlisted") {

                                echo "
                                    <span class='status-shortlisted'>
                                        Shortlisted
                                    </span>
                                ";

                            } elseif ($row['status'] == "Approved") {

                                echo "
                                    <span class='status-approved'>
                                        Approved
                                    </span>
                                ";

                            } else {

                                echo "
                                    <span class='status-rejected'>
                                        Rejected
                                    </span>
                                ";

                            }

                            ?>

                        </td>


                        <!-- RESULT -->

                        <td>

                            <?php

                            if ($row['status'] == "Applied") {

                                echo "
                                    <span class='status-pending'>

                                        Interview Progress:
                                        " . $completed . " / " . $total . "

                                        (Remaining:
                                        " . $pending . ")

                                    </span>
                                ";


                            } elseif ($row['status'] == "Shortlisted") {


                                if ($completed >= $total) {

                                    echo "
                                        <span class='status-shortlisted'>

                                            🟢 All interview rounds completed.
                                            Waiting for final approval.

                                        </span>
                                    ";


                                } elseif ($completed > 0) {

                                    $remaining =
                                        $total - $completed;

                                    echo "
                                        <span class='status-shortlisted'>

                                            🔵 Interview process in progress.

                                            " . $remaining . "
                                            round(s) remaining.

                                        </span>
                                    ";


                                } else {

                                    echo "
                                        <span class='status-shortlisted'>

                                            🔵 You have been shortlisted.
                                            Interview process will begin soon.

                                        </span>
                                    ";

                                }


                            } elseif ($row['status'] == "Approved") {

                                echo "
                                    <span class='status-approved'>

                                        🎉 Congratulations!
                                        You have been selected
                                        for this position.

                                    </span>
                                ";


                            } else {

                                echo "
                                    <span class='status-rejected'>

                                        Application was not selected.
                                        Keep applying to other opportunities.

                                    </span>
                                ";

                            }

                            ?>

                        </td>


                        <!-- INTERVIEW ROUNDS -->

                        <td>

                            <?php

                            /*
                                Create key using
                                student_id + company_id
                            */

                            $key =
                                $student_id . '_' . $row['company_id'];


                            if (isset($interview_rounds[$key])) {

                                foreach (
                                    $interview_rounds[$key]
                                    as $round
                                ) {


                                    if (
                                        $round['status']
                                        == "Completed"
                                    ) {

                                        echo "
                                            <div style='margin-bottom:6px;'>

                                                🟢 "
                                                . htmlspecialchars(
                                                    $round['round_name']
                                                )
                                                . " - Completed

                                            </div>
                                        ";

                                    } else {

                                        echo "
                                            <div style='margin-bottom:6px;'>

                                                🟡 "
                                                . htmlspecialchars(
                                                    $round['round_name']
                                                )
                                                . " - Upcoming

                                            </div>
                                        ";

                                    }

                                }

                            } else {

                                echo "
                                    <span style='color:#777;'>

                                        No interview rounds
                                        scheduled yet.

                                    </span>
                                ";

                            }

                            ?>

                        </td>


                        <!-- TIMELINE -->

                        <td>

                            <a
                                href="application_timeline.php?placement_id=<?php echo $row['placement_id']; ?>"
                                class="btn"
                            >

                                📊 View Timeline

                            </a>

                        </td>

                    </tr>


            <?php

                }

            } else {

            ?>

                <tr>

                    <td
                        colspan="7"
                        style="text-align:center;"
                    >

                        No placement applications found.

                    </td>

                </tr>

            <?php

            }

            ?>

        </table>


        <br>


        <a
            href="dashboard.php"
            class="btn"
        >

            ⬅ Back to Dashboard

        </a>

    </div>

</body>

</html>