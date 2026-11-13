<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    exit("Unauthorized");
}

include("../database/config.php");

/** @var mysqli $conn */

$student_id = $_SESSION['student_id'];

$student_sql = "SELECT cgpa, skills FROM student WHERE id='$student_id'";
$student_result = mysqli_query($conn, $student_sql);
$student = mysqli_fetch_assoc($student_result);

$student_cgpa = $student['cgpa'];
$student_skills = $student['skills'];

// Convert student's skills into an array
$student_skill_array = [];

if (!empty($student_skills)) {

    $student_skill_array = array_map(
        'trim',
        preg_split('/[,;]+/', strtolower($student_skills))
    );

}

if (!isset($_GET['company'])) {
    exit();
}

$company_name = mysqli_real_escape_string($conn, $_GET['company']);

$sql = "SELECT * FROM company
        WHERE company_name='$company_name'
        ORDER BY job_role";

$result = mysqli_query($conn, $sql);

while ($company = mysqli_fetch_assoc($result)) {

   // --------------------------------------------------
// BASIC ELIGIBILITY CHECK
// --------------------------------------------------

$cgpa_eligible = ($student_cgpa >= $company['min_cgpa']);


// --------------------------------------------------
// SKILL MATCHING
// --------------------------------------------------

$required_skills = [];

if (!empty($company['required_skills'])) {

    $required_skills = array_map(
        'trim',
        preg_split('/[,;]+/', strtolower($company['required_skills']))
    );

}


// Calculate matched skills
$matched_skills = [];

foreach ($required_skills as $required_skill) {

    if (empty($required_skill)) {
        continue;
    }

    foreach ($student_skill_array as $student_skill) {

        if (
            $required_skill === $student_skill ||
            strpos($student_skill, $required_skill) !== false ||
            strpos($required_skill, $student_skill) !== false
        ) {

            $matched_skills[] = $required_skill;

            break;
        }

    }

}


// Remove duplicates
$matched_skills = array_unique($matched_skills);


// Calculate skill match percentage
$total_required_skills = count($required_skills);
$total_matched_skills = count($matched_skills);

if ($total_required_skills > 0) {

    $skill_match_percentage =
        round(
            ($total_matched_skills / $total_required_skills) * 100,
            2
        );

} else {

    $skill_match_percentage = 100;

}


// Find missing skills
$missing_skills = array_diff(
    $required_skills,
    $matched_skills
);

// --------------------------------------------------
// FINAL ELIGIBILITY
// --------------------------------------------------

// Only mandatory/basic requirements decide eligibility.
// Skill match does NOT block the student.

$eligible = $cgpa_eligible;

    echo "<div class='role-card'>";

    echo "<h3>" . htmlspecialchars($company['job_role']) . "</h3>";

    echo "<p><strong>💰 Package:</strong> " . htmlspecialchars($company['package']) . " LPA</p>";

    echo "<p><strong>🎓 Minimum CGPA:</strong> " . htmlspecialchars($company['min_cgpa']) . "</p>";

    echo "<p><strong>💼 Experience:</strong> " .
        (!empty($company['experience']) ? htmlspecialchars($company['experience']) : "Not Available")
        . "</p>";

    if ($eligible) {

        $company_id = $company['id'];
        echo "<div style='margin:10px 0;'>";

echo "<strong>🎯 Skill Match:</strong> ";

if ($skill_match_percentage >= 70) {

    echo "<span style='color:green;font-weight:bold;'>
            " . $skill_match_percentage . "% - Excellent Match
          </span>";

} elseif ($skill_match_percentage >= 50) {

    echo "<span style='color:#d97706;font-weight:bold;'>
            " . $skill_match_percentage . "% - Good Match
          </span>";

} elseif ($skill_match_percentage >= 40) {

    echo "<span style='color:#ca8a04;font-weight:bold;'>
            " . $skill_match_percentage . "% - Partial Match
          </span>";

} else {

    echo "<span style='color:#dc2626;font-weight:bold;'>
            " . $skill_match_percentage . "% - Low Match
          </span>";

}

echo "</div>";

if (!empty($matched_skills)) {

    echo "<p><strong>✓ Matched Skills:</strong> ";

    foreach ($matched_skills as $skill) {

        echo "<span class='skill-badge'>"
            . htmlspecialchars(ucwords($skill))
            . "</span> ";

    }

    echo "</p>";

}

if (!empty($missing_skills)) {

    echo "<p>";

    echo "<strong>⚠ Missing Skills:</strong> ";

    foreach ($missing_skills as $skill) {

        echo "<span style='
                display:inline-block;
                background:#fff3cd;
                color:#856404;
                padding:5px 10px;
                margin:3px;
                border-radius:5px;
             '>"
            . htmlspecialchars(ucwords($skill))
            . "</span> ";

    }

    echo "</p>";

}

        $check = mysqli_query(
            $conn,
            "SELECT id FROM placement
             WHERE student_id='$student_id'
             AND company_id='$company_id'"
        );
        $alreadyApplied = (mysqli_num_rows($check) > 0);

        echo "<button
class='btn details-btn'
data-company='" . htmlspecialchars($company['company_name']) . "'
data-companyid='" . $company['id'] . "'
data-role='" . htmlspecialchars($company['job_role']) . "'
data-package='" . htmlspecialchars($company['package']) . "'
data-cgpa='" . htmlspecialchars($company['min_cgpa']) . "'
data-location='" . htmlspecialchars($company['location']) . "'
data-experience='" . htmlspecialchars($company['experience']) . "'
data-skills='" . htmlspecialchars($company['required_skills']) . "'
data-description='" . htmlspecialchars($company['job_description']) . "'
data-applied='" . ($alreadyApplied ? "yes" : "no") . "'
onclick='showRoleDetails(this)'>

View Details

</button>";

        echo " ";
        if (mysqli_num_rows($check) > 0) {

            echo "<button class='btn' disabled>Already Applied</button>";
        } else {
            if ($skill_match_percentage < 40) {

    echo "<p style='
        background:#fff3cd;
        color:#856404;
        padding:10px;
        border-radius:8px;
    '>
    ⚠ Your skill match is below 40%.
    You are still eligible to apply because you meet the
    basic eligibility requirements.
    </p>";

}

            echo "<a class='btn'
            href='apply.php?company_id=" . $company['id'] . "'>
            Apply
          </a>";
        }
    } else {

        echo "<p style='
    background:#f8d7da;
    color:#842029;
    padding:10px;
    border-radius:8px;
'>
❌ You are not eligible for this position.

<br>

Required CGPA:
<strong>" . htmlspecialchars($company['min_cgpa']) . "</strong>

<br>

Your CGPA:
<strong>" . htmlspecialchars($student_cgpa) . "</strong>

</p>";

echo "<button class='btn' disabled>
        Not Eligible
      </button>";
    }

    echo "<hr>";

    echo "</div>";
}
