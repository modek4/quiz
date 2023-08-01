<?php
session_start();
require_once("../../db/connect.php");
require_once("../../log.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    //change limit
    if(isset($_POST['device_good']) && isset($_POST['device_bad']) && isset($_POST['location_good']) && isset($_POST['location_bad'])){
        $sql = "UPDATE quiz_admin SET device_limit_good='".$_POST['device_good']."', device_limit_bad='".$_POST['device_bad']."', location_limit_good='".$_POST['location_good']."', location_limit_bad='".$_POST['location_bad']."'";
        if(mysqli_query($conn, $sql)){
            echo $_SESSION['lang']['admin']['users']['limit']['success'];
            add_log(
                $_SESSION['lang']['logs']['device_limit']['title'],
                $_SESSION['lang']['logs']['device_limit']['success'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "device_good" => $_POST['device_good'],
                    "device_bad" => $_POST['device_bad'],
                    "location_good" => $_POST['location_good'],
                    "location_bad" => $_POST['location_bad']
                )
            );
        }else{
            echo $_SESSION['lang']['admin']['users']['limit']['error'];
            add_log(
                $_SESSION['lang']['logs']['device_limit']['title'],
                $_SESSION['lang']['logs']['device_limit']['error'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }else{
        //load limit to form
        $sql = "SELECT * FROM quiz_admin";
        if($result = mysqli_query($conn, $sql)){
            $row = mysqli_fetch_array($result);
            $responose = [];
            $responose[0] = $row['device_limit_good'];
            $responose[1] = $row['device_limit_bad'];
            $responose[2] = $row['location_limit_good'];
            $responose[3] = $row['location_limit_bad'];
            echo json_encode($responose);
        }
    }
    $conn->close();
}