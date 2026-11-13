
<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "placement_management",
    3307
);

if (!$conn)
{
    die("Connection Failed: " . mysqli_connect_error());
}

?>
