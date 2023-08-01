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
    if(isset($_POST['email'])){
        if(isset($_POST['name'])){
            //reset device for user
            $sql = "DELETE FROM devices WHERE email='".$_POST['email']."' AND udevices='".$_POST['name']."'";
            if($conn->query($sql) === TRUE){
                echo $_SESSION['lang']['admin']['users']['reset']['device']['success'];
                add_log(
                    $_SESSION['lang']['logs']['reset']['title'],
                    $_SESSION['lang']['logs']['reset']['device']['success'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['email']
                );
            }else{
                echo $_SESSION['lang']['admin']['users']['reset']['device']['error'];
                add_log(
                    $_SESSION['lang']['logs']['reset']['title'],
                    $_SESSION['lang']['logs']['reset']['device']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['email']
                );
            }
        }else{
            //reset all devices for user
            $sql = "DELETE FROM devices WHERE email='".$_POST['email']."'";
            if($conn->query($sql) === TRUE){
                echo $_SESSION['lang']['admin']['users']['reset']['devices']['success'];
                add_log(
                    $_SESSION['lang']['logs']['reset']['title'],
                    $_SESSION['lang']['logs']['reset']['devices']['success'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['email']
                );
            }else{
                echo $_SESSION['lang']['admin']['users']['reset']['devices']['error'];
                add_log(
                    $_SESSION['lang']['logs']['reset']['title'],
                    $_SESSION['lang']['logs']['reset']['devices']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['email']
                );
            }
        }
    }else{
        //reset all devices for all users
        $sql = "DELETE FROM devices";
        if($conn->query($sql) === TRUE){
            echo $_SESSION['lang']['admin']['users']['reset']['all_devices']['success'];
            add_log(
                $_SESSION['lang']['logs']['reset']['title'],
                $_SESSION['lang']['logs']['reset']['all_devices']['success'],
                $_SESSION['email'],
                "../../logs/"
            );
        }else{
            echo $_SESSION['lang']['admin']['users']['reset']['all_devices']['error'];
            add_log(
                $_SESSION['lang']['logs']['reset']['title'],
                $_SESSION['lang']['logs']['reset']['all_devices']['error'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }
    $conn->close();
}