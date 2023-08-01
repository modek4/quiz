<?php
session_start();
require_once("connect.php");
require_once("../log.php");
$connect=mysqli_connect($servername,$username,$password,$dbname);
if ($connect->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $connect->set_charset("utf8");
    //lightmode to darkmode
    if($_SESSION['dark']==0){
        $sql="UPDATE quiz_users SET dark=1 WHERE email='".$_SESSION['email']."'";
        if(mysqli_query($connect, $sql)){
            $_SESSION['dark']=1;
            add_log(
                $_SESSION['lang']['logs']['darkmode']['title'],
                $_SESSION['lang']['logs']['darkmode']['success'],
                $_SESSION['email'],
                "../logs/"
            );
        }else{
            $_SESSION['dark']=0;
            add_log(
                $_SESSION['lang']['logs']['darkmode']['title'],
                $_SESSION['lang']['logs']['darkmode']['error'],
                $_SESSION['email'],
                "../logs/"
            );
        }
    //darkmode to lightmode
    } else {
        $sql="UPDATE quiz_users SET dark=0 WHERE email='".$_SESSION['email']."'";
        if(mysqli_query($connect, $sql)){
            $_SESSION['dark']=0;
            add_log(
                $_SESSION['lang']['logs']['darkmode']['title'],
                $_SESSION['lang']['logs']['darkmode']['success'],
                $_SESSION['email'],
                "../logs/"
            );
        }else{
            $_SESSION['dark']=1;
            add_log(
                $_SESSION['lang']['logs']['darkmode']['title'],
                $_SESSION['lang']['logs']['darkmode']['error'],
                $_SESSION['email'],
                "../logs/"
            );
        }
    }
    $connect->close();
}
?>