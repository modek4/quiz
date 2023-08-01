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
    if(isset($_POST['title']) && isset($_POST['text'])){
        if(isset($_POST['email'])){
            //send notification to user
            $text_notification = $_POST['text']."<br/>".date("Y-m-d H:i");
            $sql = "INSERT INTO notification VALUES (NULL, '".$_POST['email']."', '".$_POST['title']."', '".$text_notification."', 1)";
            if($conn->query($sql) === TRUE){
                echo $_SESSION['lang']['admin']['users']['notification']['user']['success'];
                add_log(
                    $_SESSION['lang']['logs']['send_notification']['title'],
                    $_SESSION['lang']['logs']['send_notification']['user']['success'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "email" => $_POST['email'],
                        "title" => $_POST['title'],
                        "text" => $_POST['text']
                    )
                );
            }else{
                echo $_SESSION['lang']['admin']['users']['notification']['user']['error'];
                add_log(
                    $_SESSION['lang']['logs']['send_notification']['title'],
                    $_SESSION['lang']['logs']['send_notification']['user']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "email" => $_POST['email'],
                        "title" => $_POST['title'],
                        "text" => $_POST['text']
                    )
                );
            }
        }else{
            //send notifications to users
            $sql = "SELECT * FROM quiz_users";
            if($result = $conn->query($sql)){
                $notification_users = true;
                $user_list_command = "INSERT INTO `notification` VALUES ";
                $text_notification = $_POST['text']."<br/>".date("Y-m-d H:i");
                foreach ($result as $row) {
                    $user_list_command .= "(NULL, '".$row['email']."', '".$_POST['title']."', '".$text_notification."', 1),";
                }
                $result->free_result();
                $user_list_command = substr($user_list_command, 0, -1);
                if($notification_users = $conn->query($user_list_command)){
                    echo $_SESSION['lang']['admin']['users']['notification']['all_users']['success'];
                    add_log(
                        $_SESSION['lang']['logs']['send_notification']['title'],
                        $_SESSION['lang']['logs']['send_notification']['all_users']['success'],
                        $_SESSION['email'],
                        "../../logs/",
                        array(
                            "title" => $_POST['title'],
                            "text" => $_POST['text']
                        )
                    );
                }else{
                    echo $_SESSION['lang']['admin']['users']['notification']['all_users']['error'];
                    add_log(
                        $_SESSION['lang']['logs']['send_notification']['title'],
                        $_SESSION['lang']['logs']['send_notification']['all_users']['error'],
                        $_SESSION['email'],
                        "../../logs/",
                        array(
                            "title" => $_POST['title'],
                            "text" => $_POST['text']
                        )
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['users']['notification']['all_users']['error'];
                add_log(
                    $_SESSION['lang']['logs']['send_notification']['title'],
                    $_SESSION['lang']['logs']['send_notification']['all_users']['error'],
                    $_SESSION['email'],
                    "../../logs/"
                );
            }
        }
    }else{
        echo $_SESSION['lang']['admin']['users']['notification']['all_users']['error'];
        add_log(
            $_SESSION['lang']['logs']['send_notification']['title'],
            $_SESSION['lang']['logs']['send_notification']['error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}