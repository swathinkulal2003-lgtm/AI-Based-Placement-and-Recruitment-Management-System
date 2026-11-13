<?php
session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");
/** @var mysqli $conn */

$sql = "SELECT
            placement.id,
            placement.student_id,
            placement.company_id,
            student.name AS student_name,
            company.company_name,
            company.total_rounds,
            placement.status,
            placement.completed_rounds
        FROM placement
        INNER JOIN student
            ON placement.student_id = student.id
        INNER JOIN company
            ON placement.company_id = company.id";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Applications</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="navbar">

    <div class="nav-left">

        <a href="dashboard.php">Dashboard</a>

        <a href="view_students.php">Students</a>

        <a href="add_company.php">Add Company</a>

        <a href="view_companies.php">Companies</a>

        <a href="view_applications.php" class="active">Applications</a>

    </div>

    <div class="nav-right">

        <a href="logout.php"
        onclick="return confirm('Are you sure you want to logout?');">
            Logout
        </a>

    </div>

</div>

<br>

<h2>Placement Applications</h2>

<table border="1" cellpadding="10">

<tr>
    <th>Student</th>
    <th>Company</th>
    <th>Total Rounds</th>
    <th>Completed Rounds</th>
<th>Scheduled Interviews</th>
<th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<?php
// Get scheduled interview rounds for this student and company

$schedule_sql = "SELECT
                    id,
                    round_name,
                    interview_date,
                    interview_time,
                    status
                 FROM interview_schedule
                 WHERE student_id = '{$row['student_id']}'
                 AND company_id = '{$row['company_id']}'
                 ORDER BY interview_date ASC, interview_time ASC";

$schedule_result = mysqli_query($conn, $schedule_sql);

$scheduled_rounds = [];

if($schedule_result && mysqli_num_rows($schedule_result) > 0)
{
    while($schedule = mysqli_fetch_assoc($schedule_result))
    {
        $scheduled_rounds[] = $schedule;
    }
}

?>
<tr>

<td><?php echo $row['student_name']; ?></td>

<td><?php echo $row['company_name']; ?></td>

<td><?php echo $row['total_rounds']; ?></td>

<td>

    <?php

    echo $row['completed_rounds']
        . " / "
        . $row['total_rounds'];

    ?>

</td>

<td>

    <?php

    if(count($scheduled_rounds) > 0)
    {

        foreach($scheduled_rounds as $schedule)
        {

            echo "<div style='margin-bottom:8px;'>";

            echo "<strong>📅 "
    . htmlspecialchars($schedule['round_name'])
    . "</strong>";

echo "<br>";

echo "<small>";

echo htmlspecialchars(
    date(
        "d-m-Y",
        strtotime($schedule['interview_date'])
    )
);

echo " | ";

echo htmlspecialchars(
    date(
        "h:i A",
        strtotime($schedule['interview_time'])
    )
);

echo "</small>";

echo "<br>";


if ($schedule['status'] == "Upcoming")
{
    $scheduled_datetime =
        $schedule['interview_date'] . ' ' .
        $schedule['interview_time'];

    $time_reached = (
        strtotime($scheduled_datetime) <= time()
    );

    if ($time_reached) {
        echo "<span style='color:#2563eb;font-weight:bold;'>
                Status: Interview Time Reached
              </span>";
    } else {
        echo "<span style='color:orange;'>
                Status: Upcoming
              </span>";
    }

    echo "<br>";

    echo "<a href='complete_interview.php?id="
        . intval($schedule['id'])
        . "'
        onclick=\"return confirm('Are you sure this interview has been completed?');\">
        ✅ Mark Completed
        </a>";
}
else
{
    echo "<span style='color:green;font-weight:bold;'>
            Status: Completed
          </span>";

    echo "<br><br>";

    echo "<a href='view_evaluation.php?id="
        . intval($schedule['id'])
        . "'
        style='color:#2563eb;font-weight:bold;text-decoration:none;'>
        📊 View Evaluation
        </a>";
}


            echo "</div>";

        }

    }
    else
    {

        echo "<span style='color:gray;'>No interview scheduled</span>";

    }

    ?>

</td>

<td><?php echo htmlspecialchars($row['status']); ?></td>

<td>

<?php

/* ---------------- Applied ---------------- */

if($row['status'] == "Applied")
{
?>

<a href="update_status.php?id=<?php echo $row['id']; ?>&status=Shortlisted">Shortlist</a> |

<a href="update_rounds.php?id=<?php echo $row['id']; ?>">Update Rounds</a> |

<a href="update_status.php?id=<?php echo $row['id']; ?>&status=Rejected"
onclick="return confirm('Are you sure you want to reject this student?');">
Reject
</a>

<br><br>

<?php

$remaining_rounds =
    intval($row['total_rounds'])
    - (
        intval($row['completed_rounds'])
        + count($scheduled_rounds)
    );

if($remaining_rounds > 0)
{
    echo "<span style='color:orange;'>
    Remaining Rounds : "
    . $remaining_rounds .
    "</span>";
}
else
{
    echo "<span style='color:green;'>
    ✔ All Interview Rounds Completed or Scheduled
    </span>";
}

?>

<?php
}

/* ---------------- Shortlisted ---------------- */

elseif($row['status'] == "Shortlisted")
{
?>

<?php

if(count($scheduled_rounds) > 0)
{
?>

<?php

// Count all interviews already scheduled
$scheduled_count_sql = "SELECT COUNT(*) AS scheduled_count
                        FROM interview_schedule
                        WHERE student_id = '{$row['student_id']}'
                        AND company_id = '{$row['company_id']}'";

$scheduled_count_result = mysqli_query(
    $conn,
    $scheduled_count_sql
);

$scheduled_count_row = mysqli_fetch_assoc(
    $scheduled_count_result
);

$scheduled_count = intval(
    $scheduled_count_row['scheduled_count']
);


// Check whether any interview is still Upcoming
$upcoming_count_sql = "SELECT COUNT(*) AS upcoming_count
                       FROM interview_schedule
                       WHERE student_id = '{$row['student_id']}'
                       AND company_id = '{$row['company_id']}'
                       AND status = 'Upcoming'";

$upcoming_count_result = mysqli_query(
    $conn,
    $upcoming_count_sql
);

$upcoming_count_row = mysqli_fetch_assoc(
    $upcoming_count_result
);

$upcoming_count = intval(
    $upcoming_count_row['upcoming_count']
);


// Show Schedule Another Round only when:
// 1. All previously scheduled interviews are completed
// 2. Total scheduled interviews are less than total rounds

if (
    $upcoming_count == 0 &&
    $scheduled_count < intval($row['total_rounds'])
)
{
?>

<a href="schedule_interview.php?id=<?php echo $row['id']; ?>">
    📅 Schedule Another Round
</a> |

<?php
}

?>

<?php
}
else
{
?>

    <a href="schedule_interview.php?id=<?php echo $row['id']; ?>">
        📅 Schedule Interview
    </a> |

<?php
}

?>

<a href="update_status.php?id=<?php echo $row['id']; ?>&status=Approved"
onclick="return confirm('Are you sure you want to approve this student?');">

Approve

</a> |

<a href="update_status.php?id=<?php echo $row['id']; ?>&status=Rejected"
onclick="return confirm('Are you sure you want to reject this student?');">

Reject

</a> |

<a href="update_rounds.php?id=<?php echo $row['id']; ?>">
    Update Rounds
</a>

|
<a href="evaluation_history.php?id=<?php echo $row['id']; ?>">

    📚 Evaluation History

</a>
<br><br>

<?php

if($row['completed_rounds'] < $row['total_rounds'])
{
    echo "<span style='color:orange;'>
    Remaining Rounds : "
    . ($row['total_rounds'] - $row['completed_rounds']) .
    "</span>";
}
else
{
    echo "<span style='color:green;'>
    ✔ All Interview Rounds Completed
    </span>";
}

}

/* ---------------- Approved ---------------- */

elseif($row['status'] == "Approved")
{
    echo "<span style='color:green;font-weight:bold;'>
    ✔ Approved
    </span>";
}

/* ---------------- Rejected ---------------- */

elseif($row['status'] == "Rejected")
{
    echo "<span style='color:red;font-weight:bold;'>
    ✘ Rejected
    </span>";
}

?>

</td>

</tr>

<?php } ?>

</table>

<br><br>

<a href="dashboard.php" class="back-btn">
      ← Back to Dashboard
</a>

</body>

</html>