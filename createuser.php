<?php
include('dbconnect.php');

$email = $_POST['inputEmail'];
$pass = $_POST['inputPassword'];
$repass = $_POST['inputPassword2'];
$firstname = $_POST['inputFirstname'];
$lastname = $_POST['inputLastname'];
$middlename = $_POST['inputMiddlename'];
$phone = $_POST['inputMobileNo'];

if($pass != $repass){
    echo 'password not match';
}else{
    echo $firstname.' ';
    echo $middlename.' ';
    echo $lastname.'<br>';
    echo $phone.'<br>';
    echo $email.'<br>';
    echo $pass.'<br>';
}

$sql = "SELECT email FROM user WHERE email ='".$email."';";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo 'This email are already in use';
} else {
    echo 'you can use this email<br>';
    $sql2 = "INSERT INTO `workingalert`.`user` (`user_id`, `facebook_id`, `email`, `password`, `firstname`, `lastname`, `middlename`, `phone`, `picture`) VALUES (NULL, '0', '".$email."', '".$pass."', '".$firstname."', '".$lastname."', '".$middlename."', '".$phone."', '')";
    echo $sql2."<br><br>";
    if ($conn->query($sql2) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br><br><br>" . $conn->error;
    }
}
$conn->close();

?>
