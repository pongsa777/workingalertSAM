<?php
session_start();
include('dbconnect.php');

$sql = "SELECT * FROM user WHERE email = '" . $_POST['email'] . "' AND password = '" . $_POST['password'] . "' ";

echo $sql;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["user_id"]. 
            "<br>email : " . $row["email"].
            "<br>name : " . $row["firstname"]." ". $row["lastname"].
            "<br>phone : ". $row["phone"] ;
    }
    $_SESSION["user_id"] = $row["user_id"];
    session_write_close();
    echo "<br><br>Login successful";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";
} else {
    echo "Username or password incorrect";
}
$conn->close();
?>