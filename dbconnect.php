<?php
$servername = "localhost";
$username = "root";
$password = "workingalert";
$db = "Workingalert";
// Create connection
$conn = new mysqli($servername, $username, $password,$db);
$conn->set_charset("utf8");
if ($con == FALSE ) {
    die("Error : Connection failed: " . mysql_error());
}
?>
