<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


/*
    Check submitted data
*/

if (
    !isset($_POST['company_id']) ||
    !isset($_POST['selected_students'])
) {

    echo "<script>
            alert('Please select at least one candidate.');
            history.back();
          </script>";

    exit();

}


$company_id =
    intval($_POST['company_id']);


$selected_students =
    $_POST['selected_students'];


/*
    Make sure at least one candidate
    has been selected
*/

if (
    !is_array($selected_students) ||
    count($selected_students) == 0
) {

    echo "<script>
            alert('Please select at least one candidate.');
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
    Get company information
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
    Convert student IDs
    into SQL list
*/

$id_list =
    implode(
        ",",
        $student_ids
    );


/*
    Get selected candidates
*/

$candidates_sql =
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

     AND candidate_ranking.student_id
         IN ($id_list)

     ORDER BY
        candidate_ranking.overall_score DESC";


$candidates_result =
    mysqli_query(
        $conn,
        $candidates_sql
    );


if (!$candidates_result) {

    die(
        "Error fetching selected candidates: "
        . mysqli_error($conn)
    );

}


$candidates = [];


while (
    $row =
    mysqli_fetch_assoc(
        $candidates_result
    )
) {

    $candidates[] =
        $row;

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Review Shortlist
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


<!-- COMPANY INFORMATION -->

<div class="card">

    <h2>
        🤖 Review AI Shortlist
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

        Selected Candidates:

        <strong>

            <?php

            echo count($candidates);

            ?>

        </strong>

    </p>

</div>


<br>


<!-- SELECTED CANDIDATES -->

<div class="card">

    <h3>
        🏆 Selected Candidates
    </h3>


    <table class="modern-table">


        <tr>

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
                Email
            </th>

            <th>
                CGPA
            </th>

            <th>
                Skill Score
            </th>

            <th>
                Resume
            </th>

            <th>
                Overall AI Score
            </th>

        </tr>


<?php

foreach (
    $candidates
    as $candidate
) {

?>


        <tr>


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


            <!-- REGISTRATION -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['regno']
                );

                ?>

            </td>


            <!-- EMAIL -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['email']
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


            <!-- RESUME -->

            <td>

                <?php

                if (
                    $candidate['resume_score'] == 100
                ) {

                    echo
                        "<span class='status-approved'>
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


        </tr>


<?php

}

?>


    </table>

</div>


<br>


<!-- CONFIRMATION -->

<div class="card">

    <h3>
        ⚠️ Final Review
    </h3>


    <p>

        You are about to shortlist
        <strong>

            <?php

            echo count($candidates);

            ?>

        </strong>

        candidate(s) for:

    </p>


    <p>

        <strong>

            <?php

            echo htmlspecialchars(
                $company['company_name']
            );

            ?>

        </strong>

        -

        <?php

        echo htmlspecialchars(
            $company['job_role']
        );

        ?>

    </p>


    <p>

        Please review the candidate information
        carefully before confirming.

    </p>


    <p>

        <strong>
            Note:
        </strong>

        Once confirmed, the selected candidates'
        placement status will be changed to
        <strong>
            Shortlisted
        </strong>.

    </p>


    <form
        method="POST"
        action="confirm_shortlist.php"
        onsubmit="return confirm(
            'Are you sure you want to confirm this shortlist?'
        );"
    >


        <input
            type="hidden"
            name="company_id"
            value="<?php echo $company_id; ?>"
        >


<?php

foreach (
    $student_ids
    as $student_id
) {

?>

        <input
            type="hidden"
            name="selected_students[]"
            value="<?php echo $student_id; ?>"
        >

<?php

}

?>


        <input
            type="submit"
            name="confirm_shortlist"
            value="Confirm Shortlist"
            class="btn"
        >


    </form>


    <br>


    <a
        href="calculate_skill_score.php?company_id=<?php echo $company_id; ?>"
        class="back-btn"
    >

        ⬅ Back to AI Ranking

    </a>


</div>


</body>

</html>