<?php
session_start();
require_once("connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['status'])){
        $status = $_POST['status'];
        $_SESSION['status'] = $status;
        $sql = "UPDATE quiz_users SET last_login = NOW() WHERE email = '".$_SESSION['email']."'";
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "error";
        }
    }
    $conn->close();
}
?>