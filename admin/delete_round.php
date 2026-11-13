<?php

session_start();

if (!isset($_SESSION['admin_username'])) {

    header("Location: login.php");
    exit();

}

include("../database/config.php");

/** @var mysqli $conn */


if (!isset($_GET['id'])) {

    header("Location: view_companies.php");
    exit();

}

$round_id = intval($_GET['id']);


/*
    Get company ID before deleting
*/

$sql = "SELECT company_id
        FROM recruitment_rounds
        WHERE id = '$round_id'";

$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    header("Location: view_companies.php");
    exit();

}


$round = mysqli_fetch_assoc($result);

$company_id = $round['company_id'];


/*
    Delete round
*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM recruitment_rounds WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $round_id
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo "<script>

            alert(
                'Recruitment Round Deleted Successfully'
            );

            window.location.href =
            'manage_rounds.php?company_id=$company_id';

          </script>";

    exit();

}


mysqli_stmt_close($stmt);

echo "<script>

        alert('Error deleting recruitment round.');

        window.location.href =
        'manage_rounds.php?company_id=$company_id';

      </script>";

?>