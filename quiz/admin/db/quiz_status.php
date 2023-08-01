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
    if(!isset($_POST['share']) || $_POST['share'] > 2 || $_POST['share'] < 0){
        echo $_SESSION['lang']['admin']['menage']['quiz_status']['no_share'];
        add_log(
            $_SESSION['lang']['logs']['quiz_status']['title'],
            $_SESSION['lang']['logs']['quiz_status']['no_share'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    if(!isset($_POST['subject']) || empty($_POST['subject'])){
        echo $_SESSION['lang']['admin']['menage']['quiz_status']['no_subject'];
        add_log(
            $_SESSION['lang']['logs']['quiz_status']['title'],
            $_SESSION['lang']['logs']['quiz_status']['no_subject'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    $subject = $_POST['subject'];
    $share = $_POST['share'];
    $notification = false;
    //quiz public without notification
    if($share == 2){
        $sql = "UPDATE subjects SET share=1 WHERE subject='$subject'";
    //quiz private
    }else if($share == 1){
        $sql = "UPDATE subjects SET share=0 WHERE subject='$subject'";
    //quiz public with notification
    }else{
        $sql = "UPDATE subjects SET share=1 WHERE subject='$subject'";
        $notification = true;
    }
    if($conn->query($sql) === TRUE){
        if($notification){
            $sql = "SELECT email FROM quiz_users";
            if($result = mysqli_query($conn,$sql)){
                $send_notifications = true;
                while($row = mysqli_fetch_assoc($result)){
                    $sql = "INSERT INTO notification VALUES ('NULL', '".$row['email']."','".$_SESSION['lang']['notification']['share_title']."' ,'".$_SESSION['lang']['notification']['share_text']." ".$subject."' , 1)";
                    if(!$conn->query($sql) === TRUE){
                        $send_notifications = false;
                    }
                }
                if($send_notifications){
                    echo $_SESSION['lang']['admin']['menage']['quiz_status']['success_notification'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_status']['title'],
                        $_SESSION['lang']['logs']['quiz_status']['success_notification'],
                        $_SESSION['email'],
                        "../../logs/",
                        $subject
                    );
                }else{
                    echo $_SESSION['lang']['admin']['menage']['quiz_status']['error_share'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_status']['title'],
                        $_SESSION['lang']['logs']['quiz_status']['error'],
                        $_SESSION['email'],
                        "../../logs/",
                        $subject
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_status']['error_share'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_status']['title'],
                    $_SESSION['lang']['logs']['quiz_status']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $subject
                );
            }
        }else{
            if($share == 1){
                echo $_SESSION['lang']['admin']['menage']['quiz_status']['success_private'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_status']['title'],
                    $_SESSION['lang']['logs']['quiz_status']['success_private'],
                    $_SESSION['email'],
                    "../../logs/",
                    $subject
                );
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_status']['success_no_notification'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_status']['title'],
                    $_SESSION['lang']['logs']['quiz_status']['success_no_notification'],
                    $_SESSION['email'],
                    "../../logs/",
                    $subject
                );
            }
        }
    }else{
        echo $_SESSION['lang']['admin']['menage']['quiz_status']['error_share'];
        add_log(
            $_SESSION['lang']['logs']['quiz_status']['title'],
            $_SESSION['lang']['logs']['quiz_status']['error'],
            $_SESSION['email'],
            "../../logs/",
            $subject
        );
    }
    $conn->close();
}