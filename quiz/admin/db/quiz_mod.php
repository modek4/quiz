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
    if(!isset($_POST['add_remove']) || empty($_POST['add_remove'])){
        echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['no_add_remove'];
        add_log(
            $_SESSION['lang']['logs']['quiz_moderation']['title'],
            $_SESSION['lang']['logs']['quiz_moderation']['no_add_remove'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    if(!isset($_POST['users']) || empty($_POST['users'])){
        echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['no_user'];
        add_log(
            $_SESSION['lang']['logs']['quiz_moderation']['title'],
            $_SESSION['lang']['logs']['quiz_moderation']['no_user'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    $add_remove = $_POST['add_remove'];
    $users = $_POST['users'];
    //add moderator
    if($add_remove == "add"){
        $sql = "SELECT * FROM quiz_users WHERE email='".$users."'";
        if($result = mysqli_query($conn,$sql)){
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result);
                $sql = "INSERT INTO users VALUES ('NULL', '".$row['email']."', '".$row['password']."', '".$row['dark']."', 1)";
                if($conn->query($sql)){
                    echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['success_add'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_moderation']['title'],
                        $_SESSION['lang']['logs']['quiz_moderation']['success_add'],
                        $_SESSION['email'],
                        "../../logs/",
                        $row['email']
                    );
                }else{
                    echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error_add'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_moderation']['title'],
                        $_SESSION['lang']['logs']['quiz_moderation']['error_add'],
                        $_SESSION['email'],
                        "../../logs/"
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error_add'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_moderation']['title'],
                    $_SESSION['lang']['logs']['quiz_moderation']['error_add'],
                    $_SESSION['email'],
                    "../../logs/"
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error_add'];
            add_log(
                $_SESSION['lang']['logs']['quiz_moderation']['title'],
                $_SESSION['lang']['logs']['quiz_moderation']['error_add'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    //remove moderator
    }else if($add_remove == "remove"){
        $sql = "DELETE FROM users WHERE email='".$users."'";
        if($conn->query($sql)){
            echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['success_remove'];
            add_log(
                $_SESSION['lang']['logs']['quiz_moderation']['title'],
                $_SESSION['lang']['logs']['quiz_moderation']['success_remove'],
                $_SESSION['email'],
                "../../logs/",
                $users
            );
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error_remove'];
            add_log(
                $_SESSION['lang']['logs']['quiz_moderation']['title'],
                $_SESSION['lang']['logs']['quiz_moderation']['error_remove'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }
    $conn->close();
}