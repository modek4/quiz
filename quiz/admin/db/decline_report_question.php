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
    if(isset($_POST['subject']) && isset($_POST['question_id'])){
        $subject = $_POST['subject'];
        $question_id = $_POST['question_id'];
        //decline report
        $sql = "DELETE FROM reports WHERE question_id = '$question_id' AND subject = '$subject'";
        if(mysqli_query($conn, $sql)){
            echo $_SESSION['lang']['admin']['reports']['decline']['success'];
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['decline_success'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $subject,
                    "question_id" => $question_id
                )
            );
        }else{
            echo $_SESSION['lang']['admin']['reports']['decline']['error'];
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['decline_error'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $subject,
                    "question_id" => $question_id
                )
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['reports']['decline']['error_no_variable'];
        add_log(
            $_SESSION['lang']['logs']['reports']['title'],
            $_SESSION['lang']['logs']['reports']['decline_error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}