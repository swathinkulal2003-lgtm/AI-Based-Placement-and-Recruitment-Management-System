<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


/*
    Check round ID
*/

if (!isset($_GET['id'])) {

    header("Location: view_companies.php");
    exit();

}

$round_id = intval($_GET['id']);


/*
    Get existing round
*/

$sql = "SELECT *
        FROM recruitment_rounds
        WHERE id = '$round_id'";

$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    echo "Recruitment round not found.";
    exit();

}

$round = mysqli_fetch_assoc($result);

$company_id = $round['company_id'];


/*
    Get company information
*/

$company_sql = "SELECT company_name, job_role
                FROM company
                WHERE id = '$company_id'";

$company_result = mysqli_query(
    $conn,
    $company_sql
);

$company = mysqli_fetch_assoc(
    $company_result
);


/*
    Update recruitment round
*/

if (isset($_POST['update_round'])) {

    $round_number = intval(
        $_POST['round_number']
    );

    $round_name = trim(
        $_POST['round_name']
    );

    $conducted_by = trim(
        $_POST['conducted_by']
    );

    $mode = trim(
        $_POST['mode']
    );

    
 // Interview date and time are assigned
// separately for each student.
// $interview_date = NULL;
// $interview_time = NULL;


    $location = trim(
        $_POST['location']
    );

    $meeting_link = trim(
        $_POST['meeting_link']
    );

    $instructions = trim(
        $_POST['instructions']
    );

    $status = trim(
        $_POST['status']
    );


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
            Check duplicate round number
        */

        $check_sql = "SELECT id
                      FROM recruitment_rounds
                      WHERE company_id = '$company_id'
                      AND round_number = '$round_number'
                      AND id != '$round_id'";

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
                Update using prepared statement
            */

            
$stmt = mysqli_prepare(
    $conn,
    "UPDATE recruitment_rounds
     SET
        round_number = ?,
        round_name = ?,
        conducted_by = ?,
        mode = ?,
        location = ?,
        meeting_link = ?,
        instructions = ?,
        status = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "isssssssi",
    $round_number,
    $round_name,
    $conducted_by,
    $mode,
    $location,
    $meeting_link,
    $instructions,
    $status,
    $round_id
);



            if (mysqli_stmt_execute($stmt)) {

                echo "<script>

                        alert(
                            'Recruitment Round Updated Successfully'
                        );

                        window.location.href =
                        'manage_rounds.php?company_id=$company_id';

                      </script>";

                exit();

            } else {

                echo "<script>
                        alert('Error updating recruitment round.');
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
        Edit Recruitment Round
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
        ✏ Edit Recruitment Round
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
            value="<?php
                echo htmlspecialchars(
                    $round['round_number']
                );
            ?>"
        >

        <br><br>


        <!-- ROUND NAME -->

        <label>
            Round Name
        </label>

        <input
            type="text"
            name="round_name"
            required
            value="<?php
                echo htmlspecialchars(
                    $round['round_name']
                );
            ?>"
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

            <option value="College"
                <?php
                if ($round['conducted_by'] == 'College')
                    echo 'selected';
                ?>
            >
                College
            </option>

            <option value="Company"
                <?php
                if ($round['conducted_by'] == 'Company')
                    echo 'selected';
                ?>
            >
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
            required
        >

            <option value="Offline"
                <?php
                if ($round['mode'] == 'Offline')
                    echo 'selected';
                ?>
            >
                Offline
            </option>

            <option value="Online"
                <?php
                if ($round['mode'] == 'Online')
                    echo 'selected';
                ?>
            >
                Online
            </option>

            <option value="Hybrid"
                <?php
                if ($round['mode'] == 'Hybrid')
                    echo 'selected';
                ?>
            >
                Hybrid
            </option>

        </select>

        <br><br>


        <!-- LOCATION -->

        <label>
            Location
        </label>

        <input
            type="text"
            name="location"
            value="<?php
                echo htmlspecialchars(
                    $round['location']
                );
            ?>"
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
            value="<?php
                echo htmlspecialchars(
                    $round['meeting_link']
                );
            ?>"
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
        ><?php
            echo htmlspecialchars(
                $round['instructions']
            );
        ?></textarea>

        <br><br>


        <!-- STATUS -->

        <label>
            Status
        </label>

        <select
            name="status"
            required
        >

            <option value="Upcoming"
                <?php
                if ($round['status'] == 'Upcoming')
                    echo 'selected';
                ?>
            >
                Upcoming
            </option>

            <option value="Ongoing"
                <?php
                if ($round['status'] == 'Ongoing')
                    echo 'selected';
                ?>
            >
                Ongoing
            </option>

            <option value="Completed"
                <?php
                if ($round['status'] == 'Completed')
                    echo 'selected';
                ?>
            >
                Completed
            </option>

        </select>

        <br><br>


        <input
            type="submit"
            name="update_round"
            value="💾 Update Recruitment Round"
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