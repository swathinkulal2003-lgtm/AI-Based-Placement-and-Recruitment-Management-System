<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

/*
    Get all shortlisted and selected candidates
    along with company and student information.
*/

$sql = "SELECT
            company.id AS company_id,
            company.company_name,
            company.job_role,
            company.logo,

            student.regno,
            student.name,

            placement.status

        FROM placement

        INNER JOIN company
        ON placement.company_id = company.id

        INNER JOIN student
        ON placement.student_id = student.id

        WHERE placement.status IN ('Shortlisted', 'Approved')

        ORDER BY company.company_name ASC,
                 placement.status DESC,
                 student.name ASC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Placement Results</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        .result-container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .result-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-result-card {

            background: white;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 30px;

            box-shadow: 0 5px 20px rgba(0,0,0,0.08);

        }

        .company-header {

            display: flex;

            align-items: center;

            gap: 20px;

            margin-bottom: 20px;

        }

        .company-header img {

            width: 70px;

            height: 70px;

            object-fit: contain;

            border-radius: 10px;

        }

        .company-header h2 {

            margin: 0;

        }

        .company-header p {

            margin: 5px 0;

            color: #666;

        }

        .section-title {

            margin-top: 25px;

            margin-bottom: 10px;

        }

        .result-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 10px;

        }

        .result-table th,
        .result-table td {

            padding: 12px;

            border: 1px solid #ddd;

            text-align: left;

        }

        .result-table th {

            background: #f5f5f5;

        }

        .shortlisted {

            color: #0066cc;

            font-weight: bold;

        }

        .selected {

            color: green;

            font-weight: bold;

        }

        .no-results {

            text-align: center;

            padding: 40px;

            background: white;

            border-radius: 15px;

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

        <a href="placement_status.php">
            Status
        </a>

        <a href="placement_results.php"
           class="active">
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


<!-- RESULT CONTENT -->

<div class="result-container">

    <h1 class="result-title">
        🏆 Placement Results
    </h1>

<?php

if (mysqli_num_rows($result) > 0) {

    /*
        Store candidates company-wise.
    */

    $companies = array();

    while ($row = mysqli_fetch_assoc($result)) {

        $company_id = $row['company_id'];

        if (!isset($companies[$company_id])) {

            $companies[$company_id] = array(

                'company_name' => $row['company_name'],

                'job_role' => $row['job_role'],

                'logo' => $row['logo'],

                'candidates' => array()

            );

        }

        $companies[$company_id]['candidates'][] = $row;

    }


    /*
        Display each company separately.
    */

    foreach ($companies as $company) {

?>

<div class="company-result-card">

    <!-- COMPANY HEADER -->

    <div class="company-header">

        <?php

        $logo = !empty($company['logo'])

            ? "../uploads/company_logo/"
              . $company['logo']

            : "../uploads/company_logo/default.jpg";

        ?>

        <img
            src="<?php echo htmlspecialchars($logo); ?>"
            alt="Company Logo"
        >

        <div>

            <h2>
                <?php
                echo htmlspecialchars(
                    $company['company_name']
                );
                ?>
            </h2>

            <p>

                <strong>Job Role:</strong>

                <?php
                echo htmlspecialchars(
                    $company['job_role']
                );
                ?>

            </p>

        </div>

    </div>


    <!-- SHORTLISTED CANDIDATES -->

    <h3 class="section-title">

        📋 Shortlisted Candidates

    </h3>

    <table class="result-table">

        <tr>

            <th>Sl. No</th>

            <th>Registration Number</th>

            <th>Student Name</th>

            <th>Status</th>

        </tr>

<?php

        $shortlisted_count = 0;

        $serial = 1;

        foreach ($company['candidates'] as $candidate) {

            if ($candidate['status'] == 'Shortlisted') {

                $shortlisted_count++;

?>

        <tr>

            <td>
                <?php echo $serial++; ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $candidate['regno']
                );
                ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $candidate['name']
                );
                ?>
            </td>

            <td class="shortlisted">

                ✓ Shortlisted

            </td>

        </tr>

<?php

            }

        }


        if ($shortlisted_count == 0) {

?>

        <tr>

            <td colspan="4"
                style="text-align:center;">

                No shortlisted candidates.

            </td>

        </tr>

<?php

        }

?>

    </table>


    <!-- SELECTED CANDIDATES -->

    <h3 class="section-title">

        🎉 Selected Candidates

    </h3>

    <table class="result-table">

        <tr>

            <th>Sl. No</th>

            <th>Registration Number</th>

            <th>Student Name</th>

            <th>Status</th>

        </tr>

<?php

        $selected_count = 0;

        $serial = 1;

        foreach ($company['candidates'] as $candidate) {

            if ($candidate['status'] == 'Approved') {

                $selected_count++;

?>

        <tr>

            <td>
                <?php echo $serial++; ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $candidate['regno']
                );
                ?>
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $candidate['name']
                );
                ?>
            </td>

            <td class="selected">

                ✓ Selected

            </td>

        </tr>

<?php

            }

        }


        if ($selected_count == 0) {

?>

        <tr>

            <td colspan="4"
                style="text-align:center;">

                No selected candidates yet.

            </td>

        </tr>

<?php

        }

?>

    </table>

</div>

<?php

    }

} else {

?>

    <div class="no-results">

        <h2>
            📢 Placement Results
        </h2>

        <p>
            No placement results have been published yet.
        </p>

    </div>

<?php

}

?>

</div>

</body>

</html>