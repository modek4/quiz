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
        echo $_SESSION['lang']['admin']['menage']['quiz_rename']['no_subject'];
        add_log(
            $_SESSION['lang']['logs']['quiz_rename']['title'],
            $_SESSION['lang']['logs']['quiz_rename']['no_subject'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    if(!isset($_POST['new_name']) || $_POST['new_name'] == ""){
        echo $_SESSION['lang']['admin']['menage']['quiz_rename']['no_name'];
        add_log(
            $_SESSION['lang']['logs']['quiz_rename']['title'],
            $_SESSION['lang']['logs']['quiz_rename']['no_name'],
            $_SESSION['email'],
            "../../logs/"
        );
        exit();
    }
    $subject = $_POST['subject'];
    $new_name = $_POST['new_name'];
    $sql = "SELECT * FROM subjects WHERE subject='".$new_name."'";
    if($result = mysqli_query($conn,$sql)){
        if(mysqli_num_rows($result) > 0){
            echo $_SESSION['lang']['admin']['menage']['quiz_rename']['name_exists'];
            add_log(
                $_SESSION['lang']['logs']['quiz_rename']['title'],
                $_SESSION['lang']['logs']['quiz_rename']['name_exists'],
                $_SESSION['email'],
                "../../logs/"
            );
            exit();
        }
    }
    //rename subject
    $sql = "UPDATE subjects SET subject='".$new_name."' WHERE subject='".$subject."'";
    if($conn->query($sql)){
        //rename questions
        $sql = "UPDATE questions SET subject='".$new_name."' WHERE subject='".$subject."'";
        if($conn->query($sql)){
            //rename reports
            $sql = "UPDATE reports SET subject='".$new_name."' WHERE subject='".$subject."'";
            if($conn->query($sql)){
                //rename scores
                $sql = "SELECT id, answers FROM scores WHERE subject='".$subject."'";
                if($result = mysqli_query($conn,$sql)){
                    $rename_scores = true;
                    while($row = mysqli_fetch_assoc($result)){
                        if($row['answers'] != null || $row['answers'] != ''){
                            $answers = json_decode($row['answers'],true);
                            foreach ($answers as $key => $value) {
                                $answers[$key]['subject'] = $new_name;
                            }
                            $answers = json_encode($answers, JSON_UNESCAPED_UNICODE);
                            $sql = "UPDATE scores SET answers='".$answers."' WHERE id=".$row['id'];
                            if(!$conn->query($sql)){
                                $rename_scores = false;
                            }
                        }
                    }
                    if($rename_scores){
                        $sql = "UPDATE scores SET subject='".$new_name."' WHERE subject='".$subject."'";
                        //rename analytics
                        if($conn->query($sql)){
                            $sql = "UPDATE analytics SET subject='".$new_name."' WHERE subject='".$subject."'";
                            if($conn->query($sql)){
                                echo $_SESSION['lang']['admin']['menage']['quiz_rename']['success'];
                                add_log(
                                    $_SESSION['lang']['logs']['quiz_rename']['title'],
                                    $_SESSION['lang']['logs']['quiz_rename']['success'],
                                    $_SESSION['email'],
                                    "../../logs/",
                                    array(
                                        "old_name" => $subject,
                                        "new_name" => $new_name
                                    )
                                );
                            }else{
                                echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
                                add_log(
                                    $_SESSION['lang']['logs']['quiz_rename']['title'],
                                    $_SESSION['lang']['logs']['quiz_rename']['error'],
                                    $_SESSION['email'],
                                    "../../logs/",
                                    $_SESSION['lang']['logs']['quiz_rename']['rename']['analytic']
                                );
                            }
                        }else{
                            echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
                            add_log(
                                $_SESSION['lang']['logs']['quiz_rename']['title'],
                                $_SESSION['lang']['logs']['quiz_rename']['error'],
                                $_SESSION['email'],
                                "../../logs/",
                                $_SESSION['lang']['logs']['quiz_rename']['rename']['analytic']
                            );
                        }
                    }else{
                        echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
                        add_log(
                            $_SESSION['lang']['logs']['quiz_rename']['title'],
                            $_SESSION['lang']['logs']['quiz_rename']['error'],
                            $_SESSION['email'],
                            "../../logs/",
                            $_SESSION['lang']['logs']['quiz_rename']['rename']['scores']
                        );
                    }
                }else{
                    echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
                    add_log(
                        $_SESSION['lang']['logs']['quiz_rename']['title'],
                        $_SESSION['lang']['logs']['quiz_rename']['error'],
                        $_SESSION['email'],
                        "../../logs/",
                        $_SESSION['lang']['logs']['quiz_rename']['rename']['scores']
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_rename']['title'],
                    $_SESSION['lang']['logs']['quiz_rename']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_SESSION['lang']['logs']['quiz_rename']['rename']['reports']
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
            add_log(
                $_SESSION['lang']['logs']['quiz_rename']['title'],
                $_SESSION['lang']['logs']['quiz_rename']['error'],
                $_SESSION['email'],
                "../../logs/",
                $_SESSION['lang']['logs']['quiz_rename']['rename']['questions']
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['menage']['quiz_rename']['error'];
        add_log(
            $_SESSION['lang']['logs']['quiz_rename']['title'],
            $_SESSION['lang']['logs']['quiz_rename']['error'],
            $_SESSION['email'],
            "../../logs/",
            $_SESSION['lang']['logs']['quiz_rename']['rename']['subject']
        );
    }
    $conn->close();
}