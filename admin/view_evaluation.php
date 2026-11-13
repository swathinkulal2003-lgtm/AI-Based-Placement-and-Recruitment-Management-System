<?php

session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */


/* Check interview ID */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo "<script>
            alert('Invalid interview.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}


$interview_id = intval($_GET['id']);


/* Get evaluation with interview, student and company details */

$sql = "SELECT

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
            interview_schedule.interview_time,
            interview_schedule.mode,
            interview_schedule.status AS interview_status,

            student.name AS student_name,
            student.regno,

            company.company_name,
            company.job_role

        FROM interview_evaluation

        INNER JOIN interview_schedule
            ON interview_evaluation.interview_id = interview_schedule.id

        INNER JOIN student
            ON interview_evaluation.student_id = student.id

        INNER JOIN company
            ON interview_evaluation.company_id = company.id

        WHERE interview_evaluation.interview_id = '$interview_id'

        LIMIT 1";


$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    echo "<script>
            alert('Evaluation not found for this interview.');
            window.location.href='view_applications.php';
          </script>";

    exit();
}


$evaluation = mysqli_fetch_assoc($result);


/* =========================================================
   AI-STYLE INTERVIEW EVALUATION INSIGHT
   Rule-based analysis using actual evaluation scores
   ========================================================= */

$technical_score =
    floatval($evaluation['technical_score']);

$communication_score =
    floatval($evaluation['communication_score']);

$problem_solving_score =
    floatval($evaluation['problem_solving_score']);

$confidence_score =
    floatval($evaluation['confidence_score']);

$overall_score =
    floatval($evaluation['overall_score']);


// Find strongest area
$scores = [
    "Technical Knowledge" => $technical_score,
    "Communication Skills" => $communication_score,
    "Problem Solving" => $problem_solving_score,
    "Confidence" => $confidence_score
];

arsort($scores);

$strongest_area = array_key_first($scores);
$strongest_score = $scores[$strongest_area];


// Find weakest area
$weakest_area = array_key_last($scores);
$weakest_score = $scores[$weakest_area];


// Generate AI-style evaluation insight

// Determine overall performance level
if ($overall_score >= 80) {
    $performance_level = "Excellent";
} elseif ($overall_score >= 60) {
    $performance_level = "Good";
} elseif ($overall_score >= 40) {
    $performance_level = "Needs Improvement";
} else {
    $performance_level = "Poor";
}


// Main AI insight
$ai_insight =
    "Overall Performance: "
    . $performance_level
    . ". ";


// Overall performance explanation
if ($overall_score >= 80) {

    $ai_insight .=
        "The candidate demonstrates strong overall interview performance and appears well prepared for the role. ";

} elseif ($overall_score >= 60) {

    $ai_insight .=
        "The candidate demonstrates good overall interview performance with some areas that can still be improved. ";

} elseif ($overall_score >= 40) {

    $ai_insight .=
        "The candidate demonstrates moderate interview performance and would benefit from further improvement in key areas. ";

} else {

    $ai_insight .=
        "The candidate's interview performance requires significant improvement before being considered ready for the role. ";
}


// Strongest area
$ai_insight .=
    "The strongest evaluation area is "
    . $strongest_area
    . " with a score of "
    . number_format($strongest_score, 2)
    . "/100. ";


// Weakest area
$ai_insight .=
    "The area requiring the most improvement is "
    . $weakest_area
    . " with a score of "
    . number_format($weakest_score, 2)
    . "/100. ";


// Additional targeted analysis
if ($technical_score >= 80) {

    $ai_insight .=
        "The candidate demonstrates strong technical knowledge. ";

} elseif ($technical_score < 60) {

    $ai_insight .=
        "Technical knowledge should be strengthened. ";
}


if ($problem_solving_score >= 80) {

    $ai_insight .=
        "Strong problem-solving ability was demonstrated. ";

} elseif ($problem_solving_score < 60) {

    $ai_insight .=
        "Problem-solving ability requires further development. ";
}


if ($communication_score >= 80) {

    $ai_insight .=
        "Communication skills were demonstrated effectively. ";

} elseif ($communication_score < 60) {

    $ai_insight .=
        "Communication skills may require further improvement. ";
}


if ($confidence_score >= 80) {

    $ai_insight .=
        "The candidate demonstrated a high level of confidence during the interview.";

} elseif ($confidence_score < 60) {

    $ai_insight .=
        "Confidence during the interview could be improved.";

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>View Interview Evaluation</title>

    <link rel="stylesheet" href="../css/style.css">

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

        <a href="view_companies.php">
            Companies
        </a>

        <a href="view_applications.php" class="active">
            Applications
        </a>

        <a href="interview_schedules.php">
            Interviews
        </a>

    </div>


    <div class="nav-right">

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">


    <h1 class="page-title">
        📊 Interview Evaluation
    </h1>


    <!-- CANDIDATE DETAILS -->

    <div class="card">

        <h2 class="section-title">
            Candidate & Interview Details
        </h2>


        <table>

            <tr>

                <th>Student Name</th>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $evaluation['student_name']
                    );
                    ?>
                </td>

            </tr>


            <tr>

                <th>Registration Number</th>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $evaluation['regno']
                    );
                    ?>
                </td>

            </tr>


            <tr>

                <th>Company</th>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $evaluation['company_name']
                    );
                    ?>
                </td>

            </tr>


            <tr>

                <th>Job Role</th>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $evaluation['job_role']
                    );
                    ?>
                </td>

            </tr>


            <tr>

                <th>Interview Round</th>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $evaluation['round_name']
                    );
                    ?>
                </td>

            </tr>


            <tr>

                <th>Interview Date</th>

                <td>

                    <?php

                    echo date(
                        "d M Y",
                        strtotime(
                            $evaluation['interview_date']
                        )
                    );

                    ?>

                </td>

            </tr>

        </table>

    </div>



    <!-- INTERVIEW SCORES -->

    <div class="card">

        <h2 class="section-title">
            Interview Performance
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

                        <strong>
                            <?php
                            echo number_format(
                                $evaluation['technical_score'],
                                2
                            );
                            ?> / 100
                        </strong>

                    </td>

                </tr>


                <tr>

                    <td>
                        Communication Skills
                    </td>

                    <td>

                        <strong>
                            <?php
                            echo number_format(
                                $evaluation['communication_score'],
                                2
                            );
                            ?> / 100
                        </strong>

                    </td>

                </tr>


                <tr>

                    <td>
                        Problem Solving
                    </td>

                    <td>

                        <strong>
                            <?php
                            echo number_format(
                                $evaluation['problem_solving_score'],
                                2
                            );
                            ?> / 100
                        </strong>

                    </td>

                </tr>


                <tr>

                    <td>
                        Confidence
                    </td>

                    <td>

                        <strong>
                            <?php
                            echo number_format(
                                $evaluation['confidence_score'],
                                2
                            );
                            ?> / 100
                        </strong>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>



    <!-- OVERALL SCORE -->

    <div class="card">

        <h2 class="section-title">
            Overall Interview Score
        </h2>


        <div class="prediction-box

        <?php

        if ($evaluation['overall_score'] >= 80) {

            echo "prediction-high";

        } elseif ($evaluation['overall_score'] >= 60) {

            echo "prediction-medium";

        } else {

            echo "prediction-low";

        }

        ?>">

            <h2>

                <?php
                echo number_format(
                    $evaluation['overall_score'],
                    2
                );
                ?> / 100

            </h2>

        </div>

    </div>

<!-- AI EVALUATION INSIGHT -->
<div class="card">

    <h2 class="section-title">
        🤖 AI-Based Evaluation Insight
    </h2>

    <div class="description-box">

        <p>
            <strong>📊 Overall Performance:</strong>
            <?php echo htmlspecialchars($performance_level); ?>
        </p>

        <p>
            <strong>🏆 Strongest Area:</strong>
            <?php
            echo htmlspecialchars($strongest_area);
            ?>
            —
            <?php
            echo number_format($strongest_score, 2);
            ?>/100
        </p>

        <p>
            <strong>⚠️ Improvement Area:</strong>
            <?php
            echo htmlspecialchars($weakest_area);
            ?>
            —
            <?php
            echo number_format($weakest_score, 2);
            ?>/100
        </p>

        <p>
            <strong>💡 AI Insight:</strong>
            <?php
            echo htmlspecialchars($ai_insight);
            ?>
        </p>

    </div>

</div>

    <!-- FEEDBACK -->

    <div class="card">

        <h2 class="section-title">
            Interview Feedback
        </h2>


        <div class="description-box">

            <?php

            if (!empty($evaluation['feedback'])) {

                echo nl2br(
                    htmlspecialchars(
                        $evaluation['feedback']
                    )
                );

            } else {

                echo "No interview feedback provided.";

            }

            ?>

        </div>

    </div>



    <!-- RECOMMENDATION -->

    <div class="card">

        <h2 class="section-title">
            Recommendation
        </h2>


        <div style="text-align:center;">

            <?php

            if (
                $evaluation['recommendation']
                == "Recommended"
            ) {

                echo "
                <span class='status-approved'>
                    Recommended
                </span>
                ";

            }
            elseif (
                $evaluation['recommendation']
                == "Needs Improvement"
            ) {

                echo "
                <span class='status-pending'>
                    Needs Improvement
                </span>
                ";

            }
            else {

                echo "
                <span class='status-rejected'>
                    Not Recommended
                </span>
                ";

            }

            ?>

        </div>


        <br>


        <p style="text-align:center;">

            <strong>
                Evaluated On:
            </strong>

            <?php

            echo date(
                "d M Y, h:i A",
                strtotime(
                    $evaluation['evaluated_at']
                )
            );

            ?>

        </p>

    </div>



    <div class="button-group">

        <a href="view_applications.php"
           class="btn">

            ← Back to Applications

        </a>

    </div>


</div>


</body>

</html>