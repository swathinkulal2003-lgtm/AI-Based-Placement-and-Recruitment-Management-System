<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


/*
    Check company ID
*/

if (!isset($_GET['company_id'])) {

    header("Location: view_companies.php");
    exit();

}

$company_id = intval($_GET['company_id']);


/*
    Get company information
*/

$company_sql = "SELECT
                    company_name,
                    job_role,
                    required_skills

                FROM company

                WHERE id = '$company_id'";

$company_result = mysqli_query(
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


$company = mysqli_fetch_assoc(
    $company_result
);


/*
    Convert required skills
    into an array
*/

$required_skills =
    explode(
        ',',
        $company['required_skills']
    );


/*
    Clean required skills
*/

$required_skills_clean = [];


foreach (
    $required_skills
    as $skill
) {

    $skill =
        strtolower(
            trim($skill)
        );


    if (!empty($skill)) {

        $required_skills_clean[] =
            $skill;

    }

}


/*
    Remove duplicate skills
*/

$required_skills_clean =
    array_unique(
        $required_skills_clean
    );


/*
    Count required skills
*/

$total_required_skills =
    count(
        $required_skills_clean
    );


/*
    Get all students who applied
*/

$student_sql = "SELECT
                    placement.student_id,
                    student.name,
                    student.regno,
                    student.email,
                    student.cgpa,
                    student.skills,
                    student.resume

                FROM placement

                INNER JOIN student
                    ON placement.student_id =
                       student.id

                WHERE placement.company_id =
                      '$company_id'";


$student_result = mysqli_query(
    $conn,
    $student_sql
);


if (!$student_result) {

    die(
        "Error fetching students: "
        . mysqli_error($conn)
    );

}


/*
    Store candidates
*/

$candidates = [];


if (
    mysqli_num_rows($student_result) > 0
) {


    while (
        $student =
        mysqli_fetch_assoc(
            $student_result
        )
    ) {


        /*
            Student skills
        */

        $student_skills =
            explode(
                ',',
                $student['skills']
            );


        /*
            Clean student skills
        */

        $student_skills_clean = [];


        foreach (
            $student_skills
            as $skill
        ) {

            $skill =
                strtolower(
                    trim($skill)
                );


            if (!empty($skill)) {

                $student_skills_clean[] =
                    $skill;

            }

        }


        /*
            Remove duplicate student skills
        */

        $student_skills_clean =
            array_unique(
                $student_skills_clean
            );


        /*
            Find matching skills
        */

        $matched_skills = [];


        foreach (
            $required_skills_clean
            as $required_skill
        ) {


            foreach (
                $student_skills_clean
                as $student_skill
            ) {


                if (
                    $required_skill ===
                    $student_skill
                ) {

                    $matched_skills[] =
                        $required_skill;

                    break;

                }

            }

        }


        /*
            Remove duplicate matches
        */

        $matched_skills =
            array_unique(
                $matched_skills
            );


            /*
    Find missing skills
*/
$missing_skills = [];

foreach($required_skills_clean as $required_skill)
{
    if(!in_array($required_skill, $matched_skills))
    {
        $missing_skills[] = $required_skill;
    }
}
        /*
            Calculate skill score
        */

        if (
            $total_required_skills > 0
        ) {

            $skill_score =
                (
                    count($matched_skills)
                    /
                    $total_required_skills
                )
                * 100;

        } else {

            $skill_score = 0;

        }


        $skill_score =
            round(
                $skill_score,
                2
            );
            /*
    Calculate CGPA score
*/

$cgpa = floatval(
    $student['cgpa']
);

$cgpa_score =
    $cgpa * 10;


/*
    Keep CGPA score
    between 0 and 100
*/

if ($cgpa_score > 100) {

    $cgpa_score = 100;

}

if ($cgpa_score < 0) {

    $cgpa_score = 0;

}


$cgpa_score =
    round(
        $cgpa_score,
        2
    );

    /*
    Calculate Resume Score
*/

if (
    !empty($student['resume'])
) {

    $resume_score = 100;

} else {

    $resume_score = 0;

}


/*
    Calculate Overall AI Score

    Skill  = 60%
    CGPA   = 25%
    Resume = 15%
*/

$overall_score =
    (
        $skill_score * 0.60
    )
    +
    (
        $cgpa_score * 0.25
    )
    +
    (
        $resume_score * 0.15
    );


$overall_score =
    round(
        $overall_score,
        2
    );



        /*
            Eligibility category
        */

        /*
    Overall AI eligibility
*/

if ($overall_score >= 80) {

    $eligibility =
        "Strong Candidate";

} elseif ($overall_score >= 60) {

    $eligibility =
        "Moderate Candidate";

} else {

    $eligibility =
        "Low Match";

}


        /*
            Add candidate to array
        */

        $candidates[] = [

            'student_id' =>
                $student['student_id'],

            'name' =>
                $student['name'],

            'regno' =>
                $student['regno'],

            'email' =>
                $student['email'],

            'cgpa' =>
    $student['cgpa'],

'cgpa_score' =>
    $cgpa_score,

'resume_score' =>
    $resume_score,

'skills' =>
    $student['skills'],

'overall_score' =>
    $overall_score,

            'matched_skills' =>
                $matched_skills,

                'missing_skills' =>
    $missing_skills,

            'skill_score' =>
                $skill_score,

            'eligibility' =>
                $eligibility

        ];

    }

}


/*
    Sort candidates by skill score
    from highest to lowest
*/

usort(
    $candidates,
    function (
        $a,
        $b
    ) {

        return
            $b['overall_score']
            <=>
            $a['overall_score'];

    }
);


/*
    Save ranking
*/

$rank = 1;


foreach (
    $candidates
    as &$candidate
) {

    $student_id =
        intval(
            $candidate['student_id']
        );

    $skill_score =
        $candidate['skill_score'];

    $cgpa_score =
        $candidate['cgpa_score'];

    $resume_score =
        $candidate['resume_score'];

    $overall_score =
        $candidate['overall_score'];


    /*
        Check existing ranking
    */

    $check_sql =
        "SELECT id

         FROM candidate_ranking

         WHERE student_id =
               '$student_id'

         AND company_id =
             '$company_id'";


    $check_result =
        mysqli_query(
            $conn,
            $check_sql
        );


    if (
        mysqli_num_rows(
            $check_result
        ) > 0
    ) {


        /*
            Update
        */

        $update_sql =
    "UPDATE candidate_ranking

     SET
        skill_score =
            '$skill_score',

        cgpa_score =
            '$cgpa_score',

        resume_score =
            '$resume_score',

        overall_score =
            '$overall_score',

        rank_position =
            '$rank'

     WHERE student_id =
           '$student_id'

     AND company_id =
         '$company_id'";


        mysqli_query(
            $conn,
            $update_sql
        );


    } else {


        /*
            Insert
        */

     $insert_sql =
    "INSERT INTO candidate_ranking

    (
        student_id,
        company_id,
        skill_score,
        cgpa_score,
        resume_score,
        overall_score,
        rank_position
    )

    VALUES

    (
        '$student_id',
        '$company_id',
        '$skill_score',
        '$cgpa_score',
        '$resume_score',
        '$overall_score',
        '$rank'
    )";


        mysqli_query(
            $conn,
            $insert_sql
        );

    }


    $candidate['rank'] =
        $rank;


    $rank++;

}


unset($candidate);

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        AI Candidate Ranking
    </title>

    <link
    rel="stylesheet"
    href="../css/style.css"
>

<style>

.ai-ranking-table {
    min-width: 1250px !important;
    width: 1250px !important;
    table-layout: auto !important;
}

.ai-ranking-table th:nth-child(1),
.ai-ranking-table td:nth-child(1) {
    width: 55px;
}

.ai-ranking-table th:nth-child(2),
.ai-ranking-table td:nth-child(2) {
    min-width: 100px;
}

.ai-ranking-table th:nth-child(3),
.ai-ranking-table td:nth-child(3) {
    min-width: 120px;
}

.ai-ranking-table th:nth-child(4),
.ai-ranking-table td:nth-child(4) {
    width: 70px;
}

.ai-ranking-table th:nth-child(5),
.ai-ranking-table td:nth-child(5) {
    min-width: 180px;
}

.ai-ranking-table th:nth-child(6),
.ai-ranking-table td:nth-child(6) {
    min-width: 150px;
}

.ai-ranking-table th:nth-child(7),
.ai-ranking-table td:nth-child(7) {
    width: 90px;
}

.ai-ranking-table th:nth-child(8),
.ai-ranking-table td:nth-child(8) {
    width: 90px;
}

.ai-ranking-table th:nth-child(9),
.ai-ranking-table td:nth-child(9) {
    width: 100px;
    white-space: nowrap;
}

.ai-ranking-table th:nth-child(10),
.ai-ranking-table td:nth-child(10) {
    width: 110px;
}

.ai-ranking-table th:nth-child(11),
.ai-ranking-table td:nth-child(11) {
    min-width: 120px;
}

.ai-ranking-table th:nth-child(12),
.ai-ranking-table td:nth-child(12) {
    min-width: 180px;
}

</style>

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
        🤖 AI Candidate Ranking
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


    <p>

        <strong>
            Required Skills:
        </strong>

        <?php

        echo htmlspecialchars(
            $company['required_skills']
        );

        ?>

    </p>


    <hr>


    <div style="overflow-x:auto; width:100%;">
<table class="modern-table ai-ranking-table">


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
                CGPA
            </th>

            <th>
                Matched Skills
            </th>

            <th>
    Missing Skills
</th>

          <th>
    Skill Match
</th>

<th>
    CGPA Score
</th>

<th>
    Resume
</th>

<th>
    Overall AI Score
</th>

<th>
    Eligibility
</th>

<th>AI Recommendation</th>

        </tr>


<?php

if (
    count($candidates) > 0
) {


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
                    $candidate['rank'] == 1
                ) {

                    echo "🥇";

                } elseif (
                    $candidate['rank'] == 2
                ) {

                    echo "🥈";

                } elseif (
                    $candidate['rank'] == 3
                ) {

                    echo "🥉";

                } else {

                    echo
                        $candidate['rank'];

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


            <!-- CGPA -->

            <td>

                <?php

                echo htmlspecialchars(
                    $candidate['cgpa']
                );

                ?>

            </td>


            <!-- MATCHED SKILLS -->

            <td>

                <?php

                if (
                    count(
                        $candidate['matched_skills']
                    ) > 0
                ) {

                    echo htmlspecialchars(
                        implode(
                            ', ',
                            $candidate[
                                'matched_skills'
                            ]
                        )
                    );

                } else {

                    echo
                        "No matching skills";

                }

                ?>

            </td>

            <!-- MISSING SKILLS -->
<td>
    <?php

    if(count($candidate['missing_skills']) > 0)
    {
        echo "<span class='status-rejected'>";

        echo htmlspecialchars(
            implode(
                ', ',
                $candidate['missing_skills']
            )
        );

        echo "</span>";
    }
    else
    {
        echo "<span class='status-approved'>
                None
              </span>";
    }

    ?>
</td>


            <!-- SKILL SCORE -->

            <td>

                <?php

                $score =
                    $candidate[
                        'skill_score'
                    ];


                if ($score >= 80) {

                    echo
                        "<span class='status-approved'>"
                        . $score
                        . "%</span>";

                } elseif ($score >= 40) {

                    echo
                        "<span class='status-shortlisted'>"
                        . $score
                        . "%</span>";

                } else {

                    echo
                        "<span class='status-rejected'>"
                        . $score
                        . "%</span>";

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


<!-- OVERALL AI SCORE -->

<td>

    <strong>

        <?php

        echo
            $candidate['overall_score']
            . "%";

        ?>

    </strong>

</td>


           <!-- ELIGIBILITY -->

<td>

    <?php

    if (
        $candidate['eligibility']
        ==
        "Strong Candidate"
    ) {

        echo
            "<span class='status-approved'>
                Strong Candidate
             </span>";

    } elseif (
        $candidate['eligibility']
        ==
        "Moderate Candidate"
    ) {

        echo
            "<span class='status-shortlisted'>
                Moderate Candidate
             </span>";

    } else {

        echo
            "<span class='status-rejected'>
                Low Match
             </span>";

    }

    ?>

</td>
<!-- AI RECOMMENDATION -->
<td>
    <?php

    $skill_score = $candidate['skill_score'];
    $cgpa_score = $candidate['cgpa_score'];
    $resume_score = $candidate['resume_score'];
    $overall_score = $candidate['overall_score'];

    $reasons = [];

    // Skill analysis
    if ($skill_score >= 80)
    {
        $reasons[] = "Excellent skill match";
    }
    elseif ($skill_score >= 60)
    {
        $reasons[] = "Good skill match";
    }
    elseif ($skill_score >= 40)
    {
        $reasons[] = "Moderate skill match";
    }
    else
    {
        $reasons[] = "Low skill match";
    }

    // CGPA analysis
    if ($cgpa_score >= 80)
    {
        $reasons[] = "good academic performance";
    }
    elseif ($cgpa_score >= 60)
    {
        $reasons[] = "average academic performance";
    }
    else
    {
        $reasons[] = "low academic score";
    }

    // Resume analysis
    if ($resume_score == 100)
    {
        $reasons[] = "resume available";
    }
    else
    {
        $reasons[] = "resume not uploaded";
    }

    // Display recommendation
    echo "<small>";

    echo htmlspecialchars(
        ucfirst(
            implode(", ", $reasons)
        )
    );

    echo ".</small>";

    ?>
</td>

        </tr>


<?php

    }


} else {

?>


        <tr>

            <td
                colspan="12"
                style="text-align:center;"
            >

                No students have applied
                for this company.

            </td>

        </tr>


<?php

}

?>


    </table>
</div>
    <br>


    <div class="card">

        <h3>
            📊 Skill Match Interpretation
        </h3>

        <p>
            🟢 <strong>80% or above</strong>
            — Strong Match
        </p>

        <p>
            🟡 <strong>40% – 79.99%</strong>
            — Moderate Match
        </p>

        <p>
            🔴 <strong>Below 40%</strong>
            — Low Skill Match
        </p>

        <p>
            <strong>Note:</strong>
            A low skill match does not automatically
            reject a candidate. The ranking is provided
            as decision support for the administrator.
        </p>

    </div>


    <br>

    <!-- AI SHORTLIST CONTROL -->

<div class="card">

    <h3>
        🤖 AI Shortlist Candidates
    </h3>

    <p>
        Select how many top-ranked candidates
        you want the AI system to recommend
        for shortlisting.
    </p>

    <form
        method="POST"
        action="ai_shortlist.php"
    >

        <input
            type="hidden"
            name="company_id"
            value="<?php echo $company_id; ?>"
        >

        <label>
            Number of Candidates to Shortlist
        </label>

        <br><br>

        <input
    type="number"
    name="shortlist_count"
    min="1"
    max="<?php echo count($candidates); ?>"
    value="<?php echo min(5, count($candidates)); ?>"
    required
>

        <br><br>

        <input
            type="submit"
            name="generate_shortlist"
            value="Generate AI Shortlist"
            class="btn"
        >

    </form>

</div>

<br>


    <a
        href="view_companies.php"
        class="back-btn"
    >

        ⬅ Back to Companies

    </a>


</div>


</body>

</html>