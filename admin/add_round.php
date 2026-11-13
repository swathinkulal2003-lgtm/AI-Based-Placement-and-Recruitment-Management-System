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

$company_sql = "SELECT id, company_name, job_role, total_rounds
                FROM company
                WHERE id = '$company_id'";

$company_result = mysqli_query($conn, $company_sql);


if (!$company_result || mysqli_num_rows($company_result) == 0) {

    echo "Company not found.";
    exit();

}

$company = mysqli_fetch_assoc($company_result);


/*
    Add recruitment round
*/

if (isset($_POST['add_round'])) {

    $round_number = intval($_POST['round_number']);

    $round_name = trim($_POST['round_name']);

    $conducted_by = trim($_POST['conducted_by']);

    $mode = trim($_POST['mode']);

   
 // Interview date and time are assigned
// separately for each student.
// $interview_date = NULL;
// $interview_time = NULL;


    $location = trim($_POST['location']);

    $meeting_link = trim($_POST['meeting_link']);

    $instructions = trim($_POST['instructions']);

    $status = trim($_POST['status']);


    /*
        Basic validation
    */

    if (
        $round_number < 1 ||
        empty($round_name) ||
        empty($conducted_by) ||
        empty($mode)
    ) {

        echo "<script>
                alert('Please fill all required fields.');
              </script>";

    } else {


        /*
            Check whether this round number
            already exists for this company.
        */

        $check_sql = "SELECT id
                      FROM recruitment_rounds
                      WHERE company_id = '$company_id'
                      AND round_number = '$round_number'";

        $check_result = mysqli_query(
            $conn,
            $check_sql
        );


        if (mysqli_num_rows($check_result) > 0) {

            echo "<script>
                    alert('This round number already exists for this company.');
                  </script>";

        } else {


            /*
                Insert round
            */

            
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO recruitment_rounds
    (
        company_id,
        round_number,
        round_name,
        conducted_by,
        mode,
        location,
        meeting_link,
        instructions,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "iisssssss",
    $company_id,
    $round_number,
    $round_name,
    $conducted_by,
    $mode,
    $location,
    $meeting_link,
    $instructions,
    $status
);



            if (mysqli_stmt_execute($stmt)) {

                echo "<script>

                        alert('Recruitment Round Added Successfully');

                        window.location.href =
                        'manage_rounds.php?company_id=$company_id';

                      </script>";

                exit();

            } else {

                echo "<script>
                        alert('Error adding recruitment round.');
                      </script>";

            }


            mysqli_stmt_close($stmt);

        }

    }

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Add Recruitment Round
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

        <a href="view_companies.php"
           class="active">
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
        ➕ Add Recruitment Round
    </h2>


    <h3>

        <?php
        echo htmlspecialchars(
            $company['company_name']
        );
        ?>

    </h3>


    <p>

        <strong>Job Role:</strong>

        <?php
        echo htmlspecialchars(
            $company['job_role']
        );
        ?>

    </p>


    <hr>


    <form method="POST">


        <!-- ROUND NUMBER -->

        <label>
            Round Number
        </label>

        <input
            type="number"
            name="round_number"
            min="1"
            required
        >

        <br><br>


        <!-- ROUND NAME -->

        <label>
            Round Name
        </label>

        <input
            type="text"
            name="round_name"
            placeholder="Example: Aptitude Test"
            required
        >

        <br><br>


        <!-- CONDUCTED BY -->

        <label>
            Conducted By
        </label>

        <select
            name="conducted_by"
            required
        >

            <option value="">
                -- Select --
            </option>

            <option value="College">
                College
            </option>

            <option value="Company">
                Company
            </option>

        </select>

        <br><br>


        <!-- MODE -->

        <label>
            Mode
        </label>

        <select
            name="mode"
            id="mode"
            required
        >

            <option value="">
                -- Select Mode --
            </option>

            <option value="Offline">
                Offline
            </option>

            <option value="Online">
                Online
            </option>

            <option value="Hybrid">
                Hybrid
            </option>

        </select>

        <br><br>

        <br><br>


        <!-- LOCATION -->

        <label>
            Location
        </label>

        <input
            type="text"
            name="location"
            id="location"
            placeholder="Example: College Auditorium"
        >

        <br><br>


        <!-- MEETING LINK -->

        <label>
            Meeting Link
        </label>

        <input
            type="url"
            name="meeting_link"
            id="meeting_link"
            placeholder="https://meet.google.com/..."
        >

        <br><br>


        <!-- INSTRUCTIONS -->

        <label>
            Instructions
        </label>

        <textarea
            name="instructions"
            rows="5"
            placeholder="Enter instructions for students..."
        ></textarea>

        <br><br>


        <!-- STATUS -->

        <label>
            Status
        </label>

        <select
            name="status"
            required
        >

            <option value="Upcoming">
                Upcoming
            </option>

            <option value="Ongoing">
                Ongoing
            </option>

            <option value="Completed">
                Completed
            </option>

        </select>

        <br><br>


        <input
            type="submit"
            name="add_round"
            value="➕ Add Recruitment Round"
            class="btn"
        >

    </form>


    <br>


    <a
        href="manage_rounds.php?company_id=<?php echo $company_id; ?>"
        class="back-btn"
    >

        ⬅ Back to Recruitment Rounds

    </a>


</div>


</body>

</html>