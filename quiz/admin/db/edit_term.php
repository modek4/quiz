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
    //edit term of single user
    if(isset($_POST['term']) && isset($_POST['code'])){
        if(isset($_SESSION['mod']) && $_SESSION['mod']==true){
            echo $_SESSION['lang']['admin']['users']['term']['mod_text'];
            exit();
        }
        if($_POST['term'] != ""){
            $sql = "UPDATE codes SET term='".$_POST['term']."' WHERE code='".$_POST['code']."'";
            if($conn->query($sql) === TRUE){
                echo $_SESSION['lang']['admin']['users']['term']['success'];
                add_log(
                    $_SESSION['lang']['logs']['term']['title'],
                    $_SESSION['lang']['logs']['term']['success'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "code" => $_POST['code'],
                        "term" => $_POST['term']
                    )
                );
            }else{
                echo $_SESSION['lang']['admin']['users']['term']['error'];
                add_log(
                    $_SESSION['lang']['logs']['term']['title'],
                    $_SESSION['lang']['logs']['term']['error'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "code" => $_POST['code'],
                        "term" => $_POST['term']
                    )
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['users']['term']['error'];
            add_log(
                $_SESSION['lang']['logs']['term']['title'],
                $_SESSION['lang']['logs']['term']['error'],
                $_SESSION['email'],
                "../../logs/",
                $_POST['code']
            );
        }
    }else{
        add_log(
            $_SESSION['lang']['logs']['term']['title'],
            $_SESSION['lang']['logs']['term']['error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}