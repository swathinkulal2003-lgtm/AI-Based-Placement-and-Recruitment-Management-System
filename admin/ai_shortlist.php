<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


/*
    Check POST data
*/

if (
    !isset($_POST['company_id']) ||
    !isset($_POST['shortlist_count'])
) {

    header("Location: view_companies.php");
    exit();

}


$company_id =
    intval($_POST['company_id']);

$shortlist_count =
    intval($_POST['shortlist_count']);


/*
    Validate shortlist count
*/

if ($shortlist_count < 1) {

    echo "<script>
            alert('Please enter a valid shortlist count.');
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
        job_role

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


/*
    Get ranked candidates

    Candidates are sorted
    by Overall AI Score.
*/

$ranking_sql =
    "SELECT

        candidate_ranking.student_id,
        candidate_ranking.skill_score,
        candidate_ranking.cgpa_score,
        candidate_ranking.resume_score,
        candidate_ranking.overall_score,
        candidate_ranking.rank_position,

        student.name,
        student.regno,
        student.email,
        student.cgpa

     FROM candidate_ranking

     INNER JOIN student

        ON candidate_ranking.student_id =
           student.id

     WHERE candidate_ranking.company_id =
           '$company_id'

     ORDER BY
        candidate_ranking.overall_score DESC";


$ranking_result =
    mysqli_query(
        $conn,
        $ranking_sql
    );


if (!$ranking_result) {

    die(
        "Error fetching candidate ranking: "
        . mysqli_error($conn)
    );

}


/*
    Store all candidates
*/

$candidates = [];


while (
    $row =
    mysqli_fetch_assoc(
        $ranking_result
    )
) {

    $candidates[] = $row;

}


/*
    Make sure shortlist count
    does not exceed applicants
*/

if (
    $shortlist_count >
    count($candidates)
) {

    $shortlist_count =
        count($candidates);

}


/*
    Get top candidates
*/

$shortlisted_candidates =
    array_slice(
        $candidates,
        0,
        $shortlist_count
    );
// Get required skills of the company
$skills_sql = "SELECT required_skills FROM company WHERE id = '$company_id'";
$skills_result = mysqli_query($conn, $skills_sql);
$skills_data = mysqli_fetch_assoc($skills_result);

$required_skills = [];

if (!empty($skills_data['required_skills'])) {
    $required_skills = array_map(
        'trim',
        explode(',', strtolower($skills_data['required_skills']))
    );
}


// Get missing skills for each shortlisted candidate
foreach ($shortlisted_candidates as &$candidate) {

    $student_id = intval($candidate['student_id']);

    $student_sql = "SELECT skills FROM student WHERE id = '$student_id'";
    $student_result = mysqli_query($conn, $student_sql);
    $student_data = mysqli_fetch_assoc($student_result);

    $student_skills = [];

    if (!empty($student_data['skills'])) {
        $student_skills = array_map(
            'trim',
            explode(',', strtolower($student_data['skills']))
        );
    }

    $missing_skills = [];

    foreach ($required_skills as $required_skill) {

        if (
            $required_skill !== '' &&
            !in_array($required_skill, $student_skills)
        ) {
            $missing_skills[] = $required_skill;
        }
    }

    $candidate['missing_skills'] = $missing_skills;
}

unset($candidate);
?>

<!DOCTYPE html>

<html>

<head>

    <title>
        AI Shortlist
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>
.ai-shortlist-table {
    min-width: 1200px !important;
    width: 1200px !important;
    table-layout: auto !important;
}

.ai-shortlist-table th:nth-child(1),
.ai-shortlist-table td:nth-child(1) {
    width: 55px;
}

.ai-shortlist-table th:nth-child(2),
.ai-shortlist-table td:nth-child(2) {
    width: 60px;
}

.ai-shortlist-table th:nth-child(3),
.ai-shortlist-table td:nth-child(3) {
    min-width: 100px;
}

.ai-shortlist-table th:nth-child(4),
.ai-shortlist-table td:nth-child(4) {
    min-width: 120px;
}

.ai-shortlist-table th:nth-child(5),
.ai-shortlist-table td:nth-child(5) {
    width: 70px;
}

.ai-shortlist-table th:nth-child(6),
.ai-shortlist-table td:nth-child(6) {
    width: 90px;
}

.ai-shortlist-table th:nth-child(7),
.ai-shortlist-table td:nth-child(7) {
    min-width: 140px;
}

.ai-shortlist-table th:nth-child(8),
.ai-shortlist-table td:nth-child(8) {
    width: 100px;
    white-space: nowrap;
}

.ai-shortlist-table th:nth-child(9),
.ai-shortlist-table td:nth-child(9) {
    width: 100px;
}

.ai-shortlist-table th:nth-child(10),
.ai-shortlist-table td:nth-child(10) {
    width: 120px;
}

.ai-shortlist-table th:nth-child(11),
.ai-shortlist-table td:nth-child(11) {
    min-width: 150px;
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

        <a href="view_students.php">
            Students
        </a>

        <a href="add_company.php">
            Add Company
        </a>

        <a
            href="view_companies.php"
            class="active"
        >
            Companies
        </a>

        <a href="view_applications.php">
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


<div class="card">


    <h2>
        🤖 AI Recommended Shortlist
    </h2>


    <h3>

        <?php

        echo htmlspecialchars(
            $company['company_name']
        );

        ?>

    </h3>


    <p>

        <strong>
            Job Role:
        </strong>

        <?php

        echo htmlspecialchars(
            $company['job_role']
        );

        ?>

    </p>


    <hr>


    <h3>
        📊 Shortlist Summary
    </h3>


    <p>

        Total Applicants:

        <strong>

            <?php

            echo count($candidates);

            ?>

        </strong>

    </p>


    <p>

        AI Recommended Candidates:

        <strong>

            <?php

            echo count(
                $shortlisted_candidates
            );

            ?>

        </strong>

    </p>


</div>


<br>


<div class="card ai-shortlist-results-card">


    <h3>
        🏆 AI Recommended Candidates
    </h3>

    <p style="margin-top: 8px;">
    Candidates are ranked from highest to lowest based on their
    <strong>Overall AI Score</strong>.
</p>

    <form
    method="POST"
    action="review_shortlist.php"
>

    <input
        type="hidden"
        name="company_id"
        value="<?php echo $company_id; ?>"
    >

   <div style="overflow-x:auto; width:100%;">
<table class="modern-table ai-shortlist-table">

        <tr>

            <th>
                Select
            </th>

            <th>
                Rank
            </th>

            <th>
                Student
            </th>

            <th>
                Registration No
            </th>

            <th>
                CGPA
            </th>

            <th>
    Skill Score
</th>

<th>
    Missing Skills
</th>

<th>
    CGPA Score
</th>

            <th style="min-width:90px;">
    Resume
</th>
            <th>
                Overall AI Score
            </th>

            <th>AI Recommendation</th>
        </tr>


<?php

if (
    count($shortlisted_candidates) > 0
) {

    foreach (
        $shortlisted_candidates
        as $candidate
    ) {

?>

        <tr>

            <!-- SELECT -->

            <td>

                <input
                    type="checkbox"
                    name="selected_students[]"
                    value="<?php echo $candidate['student_id']; ?>"
                >

            </td>


            <!-- RANK -->

            <td>

                <?php

                if (
                    $candidate['rank_position'] == 1
                ) {

                    echo "🥇";

                } elseif (
                    $candidate['rank_position'] == 2
                ) {

                    echo "🥈";

                } elseif (
                    $candidate['rank_position'] == 3
                ) {

                    echo "🥉";

                } else {

                    echo
                        $candidate['rank_position'];

                }

                ?>

            </td>


            <!-- STUDENT -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['name']
                );

                ?>

            </td>


            <!-- REGISTRATION NUMBER -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['regno']
                );

                ?>

            </td>


            <!-- CGPA -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['cgpa']
                );

                ?>

            </td>


            <!-- SKILL SCORE -->

            <td>

                <?php

                echo
                    $candidate['skill_score']
                    . "%";

                ?>

            </td>

            <!-- MISSING SKILLS -->

<td>

    <?php

    if (!empty($candidate['missing_skills'])) {

        echo htmlspecialchars(
            implode(', ', $candidate['missing_skills'])
        );

    } else {

        echo "None";

    }

    ?>

</td>


            <!-- CGPA SCORE -->

            <td>

                <?php

                echo
                    $candidate['cgpa_score']
                    . "%";

                ?>

            </td>


            <!-- RESUME -->

            <td>

                <?php

                if (
                    $candidate['resume_score'] == 100
                ) {

                    echo
    "<span class='status-approved' style='white-space:nowrap;'>
        Uploaded
     </span>";

                } else {

                    echo
                        "<span class='status-rejected'>
                            Not Uploaded
                         </span>";

                }

                ?>

            </td>


          <!-- OVERALL SCORE -->

<td>

    <strong>

        <?php

        echo
            $candidate['overall_score']
            . "%";

        ?>

    </strong>

</td>


<!-- AI RECOMMENDATION -->

<!-- AI RECOMMENDATION -->

<td>

    <?php

    if ($candidate['overall_score'] >= 80) {

        echo "<span class='status-approved'>Strong Candidate</span>";

    } elseif ($candidate['overall_score'] >= 60) {

        echo "<span class='status-pending'>Moderate Candidate</span>";

    } else {

        echo "<span class='status-rejected'>Low Match</span>";

    }

    ?>

</td>


        </tr>


<?php

    }

} else {

?>

        <tr>

            <td
                colspan="10"
                style="text-align:center;"
            >

                No ranked candidates
                are available.

            </td>

        </tr>

<?php

}

?>

       </table>
</div>


    <br>


    <div
        style="
            text-align:center;
            padding:20px;
        "
    >

        <p>

            ☑ Select the candidates you want
            to recommend for final shortlisting.

        </p>


        <input
            type="submit"
            name="review_selected"
            value="Review Selected Candidates"
            class="btn"
        >

    </div>


</form>


</div>


<br>


<div class="card">


    <h3>
        ℹ️ AI Shortlisting Information
    </h3>


    <p>

        The candidates shown above are
        automatically recommended based on
        their Overall AI Score.

    </p>


    <p>

        The Overall AI Score considers:

    </p>


    <ul>

        <li>
            Skill Match — 60%
        </li>

        <li>
            CGPA — 25%
        </li>

        <li>
            Resume — 15%
        </li>

    </ul>


    <p>

        <strong>
            Note:
        </strong>

        This is an AI-assisted recommendation.
        The final shortlisting decision remains
        with the placement administrator.

    </p>


</div>


<br>


<a
    href="calculate_skill_score.php?company_id=<?php echo $company_id; ?>"
    class="back-btn"
>

    ⬅ Back to AI Ranking

</a>


</body>

</html>