<?php

session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */


/* Validate placement ID */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo "<script>
            alert('Invalid application.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}

$placement_id = intval($_GET['id']);


/* Get placement, student and company information */

$placement_sql = "SELECT
                    placement.id,
                    placement.student_id,
                    placement.company_id,
                    placement.status,
                    placement.completed_rounds,
                    company.company_name,
                    company.job_role,
                    student.name AS student_name,
                    student.regno
                  FROM placement

                  INNER JOIN student
                    ON placement.student_id = student.id

                  INNER JOIN company
                    ON placement.company_id = company.id

                  WHERE placement.id = '$placement_id'

                  LIMIT 1";

$placement_result = mysqli_query($conn, $placement_sql);


if (!$placement_result || mysqli_num_rows($placement_result) == 0) {

    echo "<script>
            alert('Application not found.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}


$placement = mysqli_fetch_assoc($placement_result);


/* Get all completed interview evaluations */

$evaluation_sql = "SELECT
                    interview_evaluation.id AS evaluation_id,
                    interview_evaluation.interview_id,
                    interview_evaluation.technical_score,
                    interview_evaluation.communication_score,
                    interview_evaluation.problem_solving_score,
                    interview_evaluation.confidence_score,
                    interview_evaluation.overall_score,
                    interview_evaluation.feedback,
                    interview_evaluation.recommendation,
                    interview_evaluation.evaluated_at,

                    interview_schedule.round_name,
                    interview_schedule.interview_date,
                    interview_schedule.interview_time

                   FROM interview_evaluation

                   INNER JOIN interview_schedule
                     ON interview_evaluation.interview_id = interview_schedule.id

                   WHERE interview_evaluation.student_id = '{$placement['student_id']}'
                   AND interview_evaluation.company_id = '{$placement['company_id']}'

                   ORDER BY
                     interview_schedule.interview_date ASC,
                     interview_schedule.interview_time ASC";

$evaluation_result = mysqli_query($conn, $evaluation_sql);

?>

<!DOCTYPE html>

<html>

<head>

```
<title>Evaluation History</title>

<link rel="stylesheet" href="../css/style.css">
```

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

```
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

    <a href="view_applications.php" class="active">
        Applications
    </a>

</div>


<div class="nav-right">

    <a href="logout.php"
       onclick="return confirm('Are you sure you want to logout?');">

        Logout

    </a>

</div>
```

</div>

<br>

<div class="container">

```
<h2>
    📚 Interview Evaluation History
</h2>


<!-- CANDIDATE INFORMATION -->

<div class="card">

    <h2 class="section-title">
        Candidate Details
    </h2>


    <table>

        <tr>

            <th>Student Name</th>

            <td>
                <?php
                echo htmlspecialchars(
                    $placement['student_name']
                );
                ?>
            </td>

        </tr>


        <tr>

            <th>Registration Number</th>

            <td>
                <?php
                echo htmlspecialchars(
                    $placement['regno']
                );
                ?>
            </td>

        </tr>


        <tr>

            <th>Company</th>

            <td>
                <?php
                echo htmlspecialchars(
                    $placement['company_name']
                );
                ?>
            </td>

        </tr>


        <tr>

            <th>Job Role</th>

            <td>
                <?php
                echo htmlspecialchars(
                    $placement['job_role']
                );
                ?>
            </td>

        </tr>


        <tr>

            <th>Placement Status</th>

            <td>
                <?php
                echo htmlspecialchars(
                    $placement['status']
                );
                ?>
            </td>

        </tr>

    </table>

</div>


<!-- EVALUATION HISTORY -->

<div class="card">

    <h2 class="section-title">
        Round-wise Evaluation History
    </h2>


    <?php

    if (
        $evaluation_result &&
        mysqli_num_rows($evaluation_result) > 0
    ) {

        $evaluation_count = 0;
        $total_score = 0;
        $best_score = -1;
        $best_round = "";

        while (
            $evaluation =
            mysqli_fetch_assoc($evaluation_result)
        ) {

            $evaluation_count++;

            $total_score +=
                floatval(
                    $evaluation['overall_score']
                );


           if (
    floatval(
        $evaluation['overall_score']
    ) > $best_score
) {
    $best_score =
        floatval(
            $evaluation['overall_score']
        );

    $best_round =
        $evaluation['round_name'];
}


/* Calculate performance level for this round */

if ($evaluation['overall_score'] >= 80) {

    $round_performance = "Excellent";

} elseif ($evaluation['overall_score'] >= 60) {

    $round_performance = "Good";

} elseif ($evaluation['overall_score'] >= 40) {

    $round_performance = "Needs Improvement";

} else {

    $round_performance = "Poor";

}

?>

<div class="card">


        <h2>
            <?php
            echo htmlspecialchars(
                $evaluation['round_name']
            );
            ?>
        </h2>


        <table class="modern-table">

            <thead>

                <tr>

                    <th>
                        Evaluation Criteria
                    </th>

                    <th>
                        Score
                    </th>

                </tr>

            </thead>


            <tbody>

                <tr>

                    <td>
                        Technical Knowledge
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $evaluation['technical_score'],
                            2
                        );
                        ?> / 100
                    </td>

                </tr>


                <tr>

                    <td>
                        Communication Skills
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $evaluation['communication_score'],
                            2
                        );
                        ?> / 100
                    </td>

                </tr>


                <tr>

                    <td>
                        Problem Solving
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $evaluation['problem_solving_score'],
                            2
                        );
                        ?> / 100
                    </td>

                </tr>


                <tr>

                    <td>
                        Confidence
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $evaluation['confidence_score'],
                            2
                        );
                        ?> / 100
                    </td>

                </tr>


                <tr>

                    <td>
                        <strong>
                            Overall Score
                        </strong>
                    </td>

                    <td>

                        <strong>

                            <?php
                            echo number_format(
                                $evaluation['overall_score'],
                                2
                            );
                            ?>

                            / 100

                        </strong>

                    </td>
                    

                </tr>

                <tr>

    <td>
        <strong>
            Performance Level
        </strong>
    </td>

    <td>
    <strong>
        <?php
        if ($round_performance == "Excellent") {
            echo "🟢 Excellent";
        } elseif ($round_performance == "Good") {
            echo "🟡 Good";
        } elseif ($round_performance == "Needs Improvement") {
            echo "🟠 Needs Improvement";
        } else {
            echo "🔴 Poor";
        }
        ?>
    </strong>
</td>

</tr>
            </tbody>

        </table>


        <br>


        <p>

            <strong>
                Interview Date:
            </strong>

            <?php

            echo date(
                "d-m-Y",
                strtotime(
                    $evaluation['interview_date']
                )
            );

            ?>

        </p>


        <p>

            <strong>
                Feedback:
            </strong>

            <br>

            <?php

            if (
                !empty(
                    $evaluation['feedback']
                )
            ) {

                echo nl2br(
                    htmlspecialchars(
                        $evaluation['feedback']
                    )
                );

            } else {

                echo "No feedback provided.";

            }

            ?>

        </p>


        <p>

            <strong>
                Recommendation:
            </strong>

            <?php
            echo htmlspecialchars(
                $evaluation['recommendation']
            );
            ?>

        </p>


        <p>

            <strong>
                Evaluated On:
            </strong>

            <?php

            echo date(
                "d-m-Y h:i A",
                strtotime(
                    $evaluation['evaluated_at']
                )
            );

            ?>

        </p>


        <a href="view_evaluation.php?id=<?php
            echo intval(
                $evaluation['interview_id']
            );
        ?>">

            📊 View Full Evaluation

        </a>


    </div>


    <?php

        }

        /* Calculate overall interview performance */

        $average_score =
            $evaluation_count > 0
            ? $total_score / $evaluation_count
            : 0;

            if ($average_score >= 80) {
    $performance_level = "Excellent";
} elseif ($average_score >= 60) {
    $performance_level = "Good";
} elseif ($average_score >= 40) {
    $performance_level = "Needs Improvement";
} else {
    $performance_level = "Poor";
}

    ?>


    <!-- PERFORMANCE SUMMARY -->

    <div class="card">

        <h2 class="section-title">
            📈 Interview Performance Summary
        </h2>


        <table class="modern-table">

            <tr>

                <th>
                    Completed Evaluations
                </th>

                <td>
                    <?php
                    echo $evaluation_count;
                    ?>
                </td>

            </tr>


            <tr>

                <th>
                    Average Interview Score
                </th>

                <td>

                    <strong>

                        <?php
                        echo number_format(
                            $average_score,
                            2
                        );
                        ?>

                        / 100

                    </strong>

                </td>

            </tr>

            <tr>

    <th>
        Performance Level
    </th>

    <td>

        <strong>
            <?php
            echo htmlspecialchars($performance_level);
            ?>
        </strong>

    </td>

</tr>


            <tr>

                <th>
                    Best Performing Round
                </th>

                <td>

                    <?php
                    echo htmlspecialchars(
                        $best_round
                    );
                    ?>

                    -

                    <?php
                    echo number_format(
                        $best_score,
                        2
                    );
                    ?>
                    / 100

                </td>

            </tr>

        </table>

    </div>


    <?php

    } else {

    ?>


    <div class="card">

        <h3>
            No Evaluation Available
        </h3>

        <p>
            No completed interview evaluation has been
            recorded for this candidate and company yet.
        </p>

    </div>


    <?php

    }

    ?>


</div>


<br>


<a href="view_applications.php"
   class="back-btn">

    ← Back to Applications

</a>
```

</div>

</body>

</html>
