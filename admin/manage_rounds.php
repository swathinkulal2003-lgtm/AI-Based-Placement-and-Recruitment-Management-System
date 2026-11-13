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
    Get recruitment rounds
*/

$round_sql = "SELECT *
              FROM recruitment_rounds
              WHERE company_id = '$company_id'
              ORDER BY round_number ASC";

$round_result = mysqli_query($conn, $round_sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Manage Recruitment Rounds
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
        ⚙ Manage Recruitment Rounds
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


    <p>

        <strong>Configured Total Rounds:</strong>

        <?php
        echo htmlspecialchars(
            $company['total_rounds']
        );
        ?>

    </p>


    <hr>


    <h3>
        Recruitment Rounds
    </h3>


    <?php

    if (
        mysqli_num_rows($round_result) > 0
    ) {

    ?>

        <table
            class="modern-table"
        >

            <tr>

                <th>
                    Round
                </th>

                <th>
                    Round Name
                </th>

                <th>
                    Conducted By
                </th>

                <th>
                    Mode
                </th>

                <th>
                    Status
                </th>

                <th>
                    Actions
                </th>

            </tr>


            <?php

            while (
                $round =
                mysqli_fetch_assoc(
                    $round_result
                )
            ) {

            ?>

                <tr>

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $round['round_number']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $round['round_name']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $round['conducted_by']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $round['mode']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $round['status']
                        );
                        ?>

                    </td>


                    <td>

                        <a
                            href="edit_round.php?id=<?php echo $round['id']; ?>"
                            class="btn"
                        >
                            ✏ Edit
                        </a>


                        <a
                            href="delete_round.php?id=<?php echo $round['id']; ?>&company_id=<?php echo $company_id; ?>"
                            class="btn"
                            onclick="return confirm('Are you sure you want to delete this recruitment round?');"
                        >
                            🗑 Delete
                        </a>

                    </td>

                </tr>


            <?php

            }

            ?>

        </table>


    <?php

    } else {

    ?>

        <div
            style="
                padding:20px;
                text-align:center;
                background:#f8f9fa;
                border-radius:10px;
            "
        >

            <h3>
                📋 No Recruitment Rounds Added
            </h3>

            <p>
                Add the recruitment rounds for this company.
            </p>

        </div>


    <?php

    }

    ?>


    <br>


    <a
        href="add_round.php?company_id=<?php echo $company_id; ?>"
        class="btn"
    >

        ➕ Add Recruitment Round

    </a>


    <br><br>


    <a
        href="view_companies.php"
        class="back-btn"
    >

        ⬅ Back to Companies

    </a>


</div>


</body>

</html>