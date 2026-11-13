<?php

session_start();

if(!isset($_SESSION['admin_username']))
{
    header("Location: login.php");
    exit();
}

include("../database/config.php");

/** @var mysqli $conn */

$id = $_GET['id'];

$sql = "SELECT placement.*, company.total_rounds
        FROM placement
        INNER JOIN company
        ON placement.company_id = company.id
        WHERE placement.id='$id'";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
if(isset($_POST['update_rounds']))
{
    $completed_rounds = $_POST['completed_rounds'];

    if($completed_rounds > $row['total_rounds'])
    {
        echo "<script>alert('Completed rounds cannot be greater than Total Rounds');</script>";
    }
    else
    {
        $update_sql = "UPDATE placement
                       SET completed_rounds='$completed_rounds'
                       WHERE id='$id'";

        if(mysqli_query($conn, $update_sql))
        {
            header("Location: view_applications.php");
            exit();
        }
        else
        {
            echo "Error Updating Rounds";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Rounds</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Update Interview Rounds</h2>

<form method="POST">

    Completed Rounds:

    <input type="number"
name="completed_rounds"
value="<?php echo $row['completed_rounds']; ?>"
min="0"
max="<?php echo $row['total_rounds']; ?>"
required>

<p>
Total Interview Rounds:
<b><?php echo $row['total_rounds']; ?></b>
</p>

    <br><br>

    <input type="submit"
           name="update_rounds"
           value="Update">

</form>
<br><br>

<a href="view_applications.php" class="back-btn">
    ⬅ Back to View Applications
</a>

</body>
</html>