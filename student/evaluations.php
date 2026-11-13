
<?php

session_start();

if (!isset($_SESSION['student_id'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */

$student_id = intval($_SESSION['student_id']);


/*
|--------------------------------------------------------------------------
| Get completed interview evaluations
| belonging ONLY to the logged-in student
|--------------------------------------------------------------------------
*/

$sql = "SELECT

            interview_evaluation.id AS evaluation_id,
            interview_evaluation.interview_id,

            interview_evaluation.student_id,
            interview_evaluation.company_id,

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

            company.company_name,
            company.job_role

        FROM interview_evaluation

        INNER JOIN interview_schedule
            ON interview_evaluation.interview_id =
               interview_schedule.id

        INNER JOIN company
            ON interview_evaluation.company_id =
               company.id

        WHERE interview_evaluation.student_id = '$student_id'

        ORDER BY
            company.company_name ASC,
            company.job_role ASC,
            interview_schedule.interview_date ASC,
            interview_schedule.interview_time ASC";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>My Interview Evaluations</title>

    <link rel="stylesheet"
          href="../css/style.css?v=<?php echo time(); ?>">

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

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

        <a href="interview_schedule.php">
            📅 Interviews
        </a>

        <a href="evaluations.php"
           class="active">
            📊 Evaluations
        </a>

        <a href="upload_resume.php">
            Upload Resume
        </a>

        <a href="job_recommendations.php">
            Job Recommendations
        </a>

    </div>


    <div>

        <a href="logout.php"
           onclick="return confirm('Are you sure you want to logout?');">

            Logout

        </a>

    </div>

</div>


<br>


<div class="container">


    <h2>
        📊 My Interview Evaluations
    </h2>


    <p>
        View the evaluation results of your completed interview rounds.
    </p>


<?php

/*
|--------------------------------------------------------------------------
| Check whether evaluations exist
|--------------------------------------------------------------------------
*/

if ($result && mysqli_num_rows($result) > 0) {


    /*
    |--------------------------------------------------------------------------
    | Group evaluations company-wise
    |--------------------------------------------------------------------------
    */

    $evaluations_by_company = [];


    while ($evaluation = mysqli_fetch_assoc($result)) {


        /*
        |--------------------------------------------------------------------------
        | Company + Job Role is used as the grouping key.
        |
        | This keeps evaluations for different companies separate.
        |--------------------------------------------------------------------------
        */

        $company_key =
            $evaluation['company_name']
            . '|'
            . $evaluation['job_role'];


        $evaluations_by_company[$company_key][] =
            $evaluation;

    }


    /*
    |--------------------------------------------------------------------------
    | Display each company separately
    |--------------------------------------------------------------------------
    */

    foreach (
        $evaluations_by_company
        as $company_evaluations
    ) {


        /*
        |--------------------------------------------------------------------------
        | First evaluation is used to display company information
        |--------------------------------------------------------------------------
        */

        $first_evaluation =
            $company_evaluations[0];


        /*
        |--------------------------------------------------------------------------
        | Calculate overall interview performance
        |--------------------------------------------------------------------------
        */

        $completed_count =
            count($company_evaluations);


        $total_score = 0;


        $best_score = -1;


        $best_round = "";


        /*
        |--------------------------------------------------------------------------
        | Recommendation counters
        |--------------------------------------------------------------------------
        */

        $recommended_count = 0;

        $needs_improvement_count = 0;

        $not_recommended_count = 0;


        /*
        |--------------------------------------------------------------------------
        | Process every completed round
        |--------------------------------------------------------------------------
        */

        foreach (
            $company_evaluations
            as $performance
        ) {


            $score =
                floatval(
                    $performance['overall_score']
                );


            $total_score += $score;


            /*
            |--------------------------------------------------------------------------
            | Find best-performing round
            |--------------------------------------------------------------------------
            */

            if ($score > $best_score) {

                $best_score = $score;

                $best_round =
                    $performance['round_name'];

            }


            /*
            |--------------------------------------------------------------------------
            | Count recommendations
            |--------------------------------------------------------------------------
            */

            if (
                $performance['recommendation']
                == "Recommended"
            ) {

                $recommended_count++;

            }

            elseif (
                $performance['recommendation']
                == "Needs Improvement"
            ) {

                $needs_improvement_count++;

            }

            else {

                $not_recommended_count++;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Average interview score
        |--------------------------------------------------------------------------
        */

        $average_score =

            $completed_count > 0

            ? $total_score / $completed_count

            : 0;


        /*
        |--------------------------------------------------------------------------
        | Recommendation summary
        |
        | This is only a summary of stored round recommendations.
        | It does NOT change placement status.
        |--------------------------------------------------------------------------
        */

        if (

            $recommended_count >=
            $needs_improvement_count

            &&

            $recommended_count >=
            $not_recommended_count

        ) {

            $recommendation_summary =
                "Recommended";

        }

        elseif (

            $needs_improvement_count >=
            $not_recommended_count

        ) {

            $recommendation_summary =
                "Needs Improvement";

        }

        else {

            $recommendation_summary =
                "Not Recommended";

        }

?>


<!-- =========================================================
     OVERALL INTERVIEW PERFORMANCE
========================================================= -->

<div class="card">


    <h2>
        📈 Overall Interview Performance
    </h2>


    <p>

        <strong>
            Company:
        </strong>

        <?php

        echo htmlspecialchars(
            $first_evaluation['company_name']
        );

        ?>

    </p>


    <p>

        <strong>
            Job Role:
        </strong>

        <?php

        echo htmlspecialchars(
            $first_evaluation['job_role']
        );

        ?>

    </p>


    <table class="modern-table">


        <tr>

            <th>
                Completed Evaluations
            </th>

            <td>

                <?php

                echo $completed_count;

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


        <tr>

            <th>
                Recommendation Summary
            </th>

            <td>


                <?php

                if (
                    $recommendation_summary
                    == "Recommended"
                ) {

                ?>

                    <span class="status-approved">
                        Recommended
                    </span>

                <?php

                }

                elseif (
                    $recommendation_summary
                    == "Needs Improvement"
                ) {

                ?>

                    <span class="status-pending">
                        Needs Improvement
                    </span>

                <?php

                }

                else {

                ?>

                    <span class="status-rejected">
                        Not Recommended
                    </span>

                <?php

                }

                ?>


            </td>

        </tr>


    </table>


</div>


<br>


<!-- =========================================================
     INDIVIDUAL ROUND EVALUATIONS
========================================================= -->


<?php

/*
|--------------------------------------------------------------------------
| Loop through every evaluation for this company
|--------------------------------------------------------------------------
*/

foreach (
    $company_evaluations
    as $evaluation
) {

?>


<!-- EVALUATION CARD -->

<div class="card">


    <h2>

        <?php

        echo htmlspecialchars(
            $evaluation['company_name']
        );

        ?>

    </h2>


    <p>

        <strong>
            Job Role:
        </strong>

        <?php

        echo htmlspecialchars(
            $evaluation['job_role']
        );

        ?>

    </p>


    <p>

        <strong>
            Interview Round:
        </strong>

        <?php

        echo htmlspecialchars(
            $evaluation['round_name']
        );

        ?>

    </p>


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
            Interview Time:
        </strong>

        <?php

        echo date(
            "h:i A",
            strtotime(
                $evaluation['interview_time']
            )
        );

        ?>

    </p>


    <hr>


    <h3>
        Interview Scores
    </h3>


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

                    ?>

                    / 100

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

                    ?>

                    / 100

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

                    ?>

                    / 100

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

                    ?>

                    / 100

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


        </tbody>


    </table>


    <br>


    <!-- =====================================================
         FEEDBACK
    ====================================================== -->

    <h3>
        Interview Feedback
    </h3>


    <div class="card">


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

        }

        else {

            echo "No feedback provided.";

        }

        ?>


    </div>


    <!-- =====================================================
         RECOMMENDATION
    ====================================================== -->

    <h3>
        Recommendation
    </h3>


    <?php

    if (
        $evaluation['recommendation']
        == "Recommended"
    ) {

    ?>

        <span class="status-approved">
            Recommended
        </span>

    <?php

    }

    elseif (
        $evaluation['recommendation']
        == "Needs Improvement"
    ) {

    ?>

        <span class="status-pending">
            Needs Improvement
        </span>

    <?php

    }

    else {

    ?>

        <span class="status-rejected">
            Not Recommended
        </span>

    <?php

    }

    ?>


    <br>
    <br>


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


</div>


<br>


<?php

/*
|--------------------------------------------------------------------------
| End individual evaluation loop
|--------------------------------------------------------------------------
*/

}


/*
|--------------------------------------------------------------------------
| End company-wise loop
|--------------------------------------------------------------------------
*/

    }


}
else {

?>


<!-- =========================================================
     NO EVALUATION
========================================================= -->

<div class="card">


    <h3>
        📋 No Evaluations Available
    </h3>


    <p>

        You do not have any completed interview
        evaluations yet.

    </p>


</div>


<?php

}

?>


</div>


</body>

</html>
