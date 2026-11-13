<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


/*
    Get all scheduled interviews
*/

$sql = "SELECT

            interview_schedule.id,
            interview_schedule.student_id,
            interview_schedule.company_id,
            interview_schedule.round_name,
            interview_schedule.interview_date,
            interview_schedule.interview_time,
            interview_schedule.mode,
            interview_schedule.venue_or_link,
            interview_schedule.instructions,
            interview_schedule.status,

            student.name AS student_name,
            student.email AS student_email,

            company.company_name,
            company.job_role

        FROM interview_schedule

        INNER JOIN student
            ON interview_schedule.student_id = student.id

        INNER JOIN company
            ON interview_schedule.company_id = company.id

        ORDER BY
            interview_schedule.interview_date ASC,
            interview_schedule.interview_time ASC";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Interview Schedules
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

        <a href="view_companies.php">
            Companies
        </a>

        <a href="view_applications.php">
            Applications
        </a>

        <a
            href="interview_schedules.php"
            class="active"
        >
            📅 Interviews
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


<!-- PAGE -->

<div class="card">

    <h2>
        📅 Interview Schedules
    </h2>

    <p>
        View all interviews scheduled for students.
    </p>

    <hr>


    <?php

    if (
        $result &&
        mysqli_num_rows($result) > 0
    ) {

    ?>

        <table
            border="1"
            cellpadding="10"
            cellspacing="0"
            width="100%"
        >

            <tr>

                <th>
                    Student
                </th>

                <th>
                    Email
                </th>

                <th>
                    Company
                </th>

                <th>
                    Job Role
                </th>

                <th>
                    Interview Round
                </th>

                <th>
                    Date
                </th>

                <th>
                    Time
                </th>

                <th>
                    Mode
                </th>

                <th>
                    Venue / Link
                </th>

                <th>
                    Status
                </th>

            </tr>


            <?php

            while (
                $row =
                mysqli_fetch_assoc($result)
            ) {

            ?>

                <tr>

                    <!-- STUDENT -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['student_name']
                        );

                        ?>

                    </td>


                    <!-- EMAIL -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['student_email']
                        );

                        ?>

                    </td>


                    <!-- COMPANY -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['company_name']
                        );

                        ?>

                    </td>


                    <!-- JOB ROLE -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['job_role']
                        );

                        ?>

                    </td>


                    <!-- ROUND -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['round_name']
                        );

                        ?>

                    </td>


                    <!-- DATE -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row['interview_date']
                            )
                        ) {

                            echo htmlspecialchars(
                                date(
                                    "d-m-Y",
                                    strtotime(
                                        $row['interview_date']
                                    )
                                )
                            );

                        } else {

                            echo "Not announced";

                        }

                        ?>

                    </td>


                    <!-- TIME -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row['interview_time']
                            )
                        ) {

                            echo htmlspecialchars(
                                date(
                                    "h:i A",
                                    strtotime(
                                        $row['interview_time']
                                    )
                                )
                            );

                        } else {

                            echo "Not announced";

                        }

                        ?>

                    </td>


                    <!-- MODE -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['mode']
                        );

                        ?>

                    </td>


                    <!-- VENUE / LINK -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row['venue_or_link']
                            )
                        ) {

                            echo htmlspecialchars(
                                $row['venue_or_link']
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                      <?php

if ($row['status'] == "Completed")
{
    echo
    "<span class='status-approved'>
        ✅ Completed
    </span>";
}
else
{
    echo
    "<span class='status-pending'>
        📅 Upcoming
    </span>";
}

?>

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
                padding:30px;
                text-align:center;
            "
        >

            <h3>
                📭 No Interview Schedules
            </h3>

            <p>
                No interviews have been scheduled yet.
            </p>

        </div>


    <?php

    }

    ?>


    <br>
    <br>


    <a
        href="dashboard.php"
        class="back-btn"
    >
        ← Back to Dashboard
    </a>


</div>


</body>

</html>