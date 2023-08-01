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
    if(!isset($_POST['subject']) || $_POST['subject'] == ""){
        echo $_SESSION['lang']['admin']['menage']['quiz_delete']['no_subject'];
        add_log(
            $_SESSION['lang']['logs']['quiz_delete']['title'],
            $_SESSION['lang']['logs']['quiz_delete']['no_subject'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    $subject = $_POST['subject'];
    //delete subject
    $sql = "DELETE FROM subjects WHERE subject='".$subject."'";
    if($conn->query($sql)){
        //delete reports
        $sql = "DELETE FROM reports WHERE subject='".$subject."'";
        if($conn->query($sql)){
            //delete scores
            $sql = "DELETE FROM scores WHERE subject='".$subject."'";
            if($conn->query($sql)){
                //delete questions
                $sql = "DELETE FROM questions WHERE subject='".$subject."'";
                if($conn->query($sql)){
                    //delete analytics
                    $sql = "DELETE FROM analytics WHERE subject='".$subject."'";
                    if($conn->query($sql)){
                        echo $_SESSION['lang']['admin']['menage']['quiz_delete']['success'];
                        add_log(
                            $_SESSION['lang']['logs']['quiz_delete']['title'],
                            $_SESSION['lang']['logs']['quiz_delete']['success'],
                            "../../logs/",
                            $subject
                        );
                    }else{
                        echo $_SESSION['lang']['admin']['menage']['quiz_delete']['error'];
                        add_log(
                            $_SESSION['lang']['logs']['quiz_delete']['title'],
                            $_SESSION['lang']['logs']['quiz_delete']['error'],
                            $_SESSION['email'],
                            "../../logs/",
                            $_SESSION['lang']['logs']['quiz_delete']['delete']['analytic']
                        );
                    }
                }else{
                    echo $_SESSION['lang']['admin']['menage']['quiz_delete']['error'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_delete']['title'],
                        $_SESSION['lang']['logs']['quiz_delete']['error'],
                        $_SESSION['email'],
                        "../../logs/",
                        $_SESSION['lang']['logs']['quiz_delete']['delete']['questions']
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_delete']['error'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_delete']['title'],
                    $_SESSION['lang']['logs']['quiz_delete']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_SESSION['lang']['logs']['quiz_delete']['delete']['scores']
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_delete']['error'];
            add_log(
                $_SESSION['lang']['logs']['quiz_delete']['title'],
                $_SESSION['lang']['logs']['quiz_delete']['error'],
                $_SESSION['email'],
                "../../logs/",
                $_SESSION['lang']['logs']['quiz_delete']['delete']['reports']
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['menage']['quiz_delete']['error'];
        add_log(
            $_SESSION['lang']['logs']['quiz_delete']['title'],
            $_SESSION['lang']['logs']['quiz_delete']['error'],
            $_SESSION['email'],
            "../../logs/",
            $_SESSION['lang']['logs']['quiz_delete']['delete']['subject']
        );
    }
    $conn->close();
}