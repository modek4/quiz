<?php
session_start();
require_once("../admin/db/connect.php");
require_once("log.php");
$connect=mysqli_connect($servername,$username,$password,$dbname);
if ($connect->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    if(isset($_POST['email_reset']) && isset($_POST['password_reset']) && isset($_POST['code_reset'])){
        $email=$_POST['email_reset'];
        $password=$_POST['password_reset'];
        $code=$_POST['code_reset'];
        $result=mysqli_query($connect, "SELECT * FROM quiz_users WHERE email='$email'");
        $resultCode=mysqli_query($connect, "SELECT * FROM codes WHERE code='$code' and code_use=0");
        if($result && $resultCode){
            if(mysqli_num_rows($result)==0){
                if(mysqli_num_rows($resultCode)==0){
                    echo "emailnocode";
                    add_log(
                        $_SESSION['lang']['logs']['change_password']['title'],
                        $_SESSION['lang']['logs']['change_password']['emailnocode'],
                        $email,
                        "./logs/"
                    );
                }
            }else{
                if(mysqli_num_rows($resultCode)!=0){
                    $password_hash=password_hash($password, PASSWORD_DEFAULT);
                    $result=mysqli_query($connect, "UPDATE quiz_users SET password='$password_hash' WHERE email='$email' and code='$code'");
                    $resultCode=mysqli_query($connect, "UPDATE codes SET code_use='0' WHERE code='$code'");
                    if($result && $resultCode){
                        $dateNotify = date("Y-m-d H:i");
                        $sql = "INSERT INTO `notification` VALUES ('NULL', '$email', '".$_SESSION['lang']['notification']['change_password_title']."', '".$_SESSION['lang']['notification']['change_password_text']."<br/>$dateNotify', '1')";
                        $connect->query($sql);
                        echo "success";
                        add_log(
                            $_SESSION['lang']['logs']['change_password']['title'],
                            $_SESSION['lang']['logs']['change_password']['success'],
                            $email,
                            "./logs/"
                        );
                    }else{
                        echo "error";
                        add_log(
                            $_SESSION['lang']['logs']['change_password']['title'],
                            $_SESSION['lang']['logs']['change_password']['error'],
                            $email,
                            "./logs/"
                        );
                    }
                }
            }
        }else{
            echo "error";
            add_log(
                $_SESSION['lang']['logs']['change_password']['title'],
                $_SESSION['lang']['logs']['change_password']['error'],
                $email,
                "./logs/"
            );
        }
    } else {
        echo "error";
        exit();
    }
    $connect->close();
}