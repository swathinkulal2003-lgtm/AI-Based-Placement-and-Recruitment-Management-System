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
    Get recruitment rounds for companies
    that the student has applied to.

    Only upcoming and ongoing rounds
    are displayed.
*/



/*
    Get interviews specifically scheduled
    for this student by the admin.
*/

$scheduled_sql = "SELECT

                    interview_schedule.*,

                    company.company_name,
                    company.job_role

                  FROM interview_schedule

                  INNER JOIN company
                      ON interview_schedule.company_id = company.id

                  WHERE interview_schedule.student_id = '$student_id'

                  ORDER BY
                    interview_schedule.interview_date ASC,
                    interview_schedule.interview_time ASC";


$scheduled_result = mysqli_query(
    $conn,
    $scheduled_sql
);

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Interview Schedule
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

        <a href="profile.php">
            Profile
        </a>

        <a href="companies.php">
            Companies
        </a>

        <a href="prediction.php">
            AI Prediction
        </a>

        <a href="placement_status.php">
            Status
        </a>

        <a href="placement_results.php">
            Results
        </a>

        <a
            href="interview_schedule.php"
            class="active"
        >
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

        <a
            href="logout.php"
            onclick="return confirm('Are you sure you want to logout?');"
        >
            Logout
        </a>

    </div>

</div>


<br>


<div class="card">

<!-- ADMIN SCHEDULED INTERVIEWS -->




    <h2>
        📅 My Interview Schedule
    </h2>


    <p>
        View your upcoming and ongoing recruitment rounds.
    </p>


    <hr>


    <?php

    if (
    $scheduled_result &&
    mysqli_num_rows($scheduled_result) > 0
) {

        while (
            $row =
    mysqli_fetch_assoc($scheduled_result)
        ) {
        
$interview_datetime =
    $row['interview_date'] . ' ' . $row['interview_time'];

$current_datetime = date('Y-m-d H:i:s');

// Show Completed only if the admin marked it completed.
if ($row['status'] == 'Completed') {
    $display_status = "Completed";
}
// The scheduled time has arrived or passed,
// but the admin has not marked it completed.
elseif ($interview_datetime <= $current_datetime) {
    $display_status = "Interview Time Reached";
}
// The interview is still in the future.
else {
    $display_status = "Upcoming";
}


    ?>


        <div
            style="
                border:1px solid #ddd;
                border-radius:12px;
                padding:20px;
                margin-bottom:20px;
            "
        >


            <!-- COMPANY -->

            <h2>

                🏢

                <?php

                echo htmlspecialchars(
                    $row['company_name']
                );

                ?>

            </h2>


            <!-- JOB ROLE -->

            <h3>

                💼

                <?php

                echo htmlspecialchars(
                    $row['job_role']
                );

                ?>

            </h3>


            <hr>


            <!-- ROUND -->

            <p>

    <strong>
        🎯 Interview Round:
    </strong>

    <?php
    echo htmlspecialchars(
        $row['round_name']
    );
    ?>

</p>




            <!-- MODE -->

            <p>

                <strong>
                    💻 Mode:
                </strong>

                <?php

                echo htmlspecialchars(
                    $row['mode']
                );

                ?>

            </p>


            <!-- DATE -->

            <p>

                <strong>
                    📅 Date:
                </strong>

                <?php

                if (
                    !empty(
                        $row['interview_date']
                    )
                ) {

                    echo htmlspecialchars(
                        date(
                            "d-m-Y",
                            strtotime(
                                $row['interview_date']
                            )
                        )
                    );

                } else {

                    echo "Not announced";

                }

                ?>

            </p>


            <!-- TIME -->

            <p>

                <strong>
                    ⏰ Time:
                </strong>

                <?php

                if (
                    !empty(
                        $row['interview_time']
                    )
                ) {

                    echo htmlspecialchars(
                        date(
                            "h:i A",
                            strtotime(
                                $row['interview_time']
                            )
                        )
                    );

                } else {

                    echo "Not announced";

                }

                ?>

            </p>


            <!-- LOCATION -->

            <?php

if (!empty($row['venue_or_link'])) {

?>

    <p>

        <strong>
            📍 Venue / Meeting Link:
        </strong>

        <?php
        echo htmlspecialchars(
            $row['venue_or_link']
        );
        ?>

    </p>

<?php

}

?>


            <!-- INSTRUCTIONS -->

            <?php

            if (
                !empty(
                    $row['instructions']
                )
            ) {

            ?>

                <p>

                    <strong>
                        📝 Instructions:
                    </strong>

                    <br>

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $row['instructions']
                        )
                    );

                    ?>

                </p>

            <?php

            }

            ?>


            <!-- STATUS -->

            <p>

    <strong>
        Status:
    </strong>

    <?php

   
if ($display_status == "Completed") {
    echo "<span class='status-approved'>
            ✅ Completed
          </span>";
} elseif ($display_status == "Interview Time Reached") {
    echo "<span class='status-pending'>
            🔵 Interview Time Reached
          </span>";
} else {
    echo "<span class='status-pending'>
            📅 Upcoming
          </span>";
}


    ?>

</p>

<?php

$interview_datetime =
    $row['interview_date']
    . ' '
    . $row['interview_time'];

?>

<div
    class="interview-countdown"
    data-time="<?php echo htmlspecialchars($interview_datetime); ?>"
    style="
        margin-top:15px;
        padding:15px;
        border-radius:10px;
        background:#f5f5f5;
        text-align:center;
    "
>

    <strong>⏳ Interview Countdown</strong>

    <div
        class="countdown-timer"
        style="
            font-size:24px;
            font-weight:bold;
            margin-top:8px;
        "
    >
        Loading...
    </div>

</div>


            <!-- MEETING LINK -->

            <?php

if (
    !empty($row['venue_or_link']) &&
    (
        $row['mode'] == "Online" ||
        $row['mode'] == "Hybrid"
    )
) {

?>

    <p>

        <a
            href="<?php
                echo htmlspecialchars(
                    $row['venue_or_link']
                );
            ?>"
            target="_blank"
            class="btn"
        >

            🔗 Join Interview

        </a>

    </p>

<?php

}

?>


        </div>


    <?php

        }

    } else {

    ?>


        <div
            style="
                padding:30px;
                text-align:center;
            "
        >

            <h3>
                📭 No Upcoming Interviews
            </h3>

            <p>
                You currently don't have any scheduled
                recruitment rounds.
            </p>

        </div>


    <?php

    }

    ?>


    <br>


    <a
        href="dashboard.php"
        class="back-btn"
    >
        ⬅ Back to Dashboard
    </a>


</div>


<script>

document.querySelectorAll('.interview-countdown')
.forEach(function(countdown) {

    const interviewTime =
        new Date(
            countdown.dataset.time.replace(' ', 'T')
        ).getTime();

    const timer =
        countdown.querySelector('.countdown-timer');


    function updateCountdown() {

        const now = new Date().getTime();

        const difference =
            interviewTime - now;


        if (difference <= 0) {

            timer.innerHTML =
                "🔵 Interview Time Reached";

            return;
        }


        const days =
            Math.floor(
                difference /
                (1000 * 60 * 60 * 24)
            );

        const hours =
            Math.floor(
                (difference %
                (1000 * 60 * 60 * 24)) /
                (1000 * 60 * 60)
            );

        const minutes =
            Math.floor(
                (difference %
                (1000 * 60 * 60)) /
                (1000 * 60)
            );

        const seconds =
            Math.floor(
                (difference %
                (1000 * 60)) /
                1000
            );


        timer.innerHTML =
            days + "d "
            + hours + "h "
            + minutes + "m "
            + seconds + "s";

    }


    updateCountdown();

    setInterval(
        updateCountdown,
        1000
    );

});

</script>

</body>
</html>