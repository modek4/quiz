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
    if(!isset($_POST['code']) || $_POST['code'] == ""){
        if(!isset($_POST['term']) || $_POST['term'] == ""){
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['add']['no_term'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['no_term'],
                $_SESSION['email'],
                "../../logs/"
            );
            exit();
        }
        if(!isset($_POST['count']) || $_POST['count'] == ""){
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['add']['no_count'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['no_count'],
                $_SESSION['email'],
                "../../logs/"
            );
            exit();
        }
        $term = $_POST['term'];
        $count = $_POST['count'];
        $codes = array();
        for($i=0; $i<$count; $i++){
            $code = "";
            for($j=0; $j<10; $j++){
                if(rand(65,90)%2==0){
                    $code .= chr(rand(48,57));
                } else{
                    $code .= chr(rand(65,90));
                }
            }
            $codes[] = [$code,$term];
        }
        $code_added = true;
        foreach($codes as $code){
            $sql = "INSERT INTO codes VALUES ('NULL', '$code[0]', '1', '$code[1]', '0', '1')";
            if(!$conn->query($sql)){
                $code_added = false;
            }
        }
        if($code_added){
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['add']['success'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['success_add'],
                $_SESSION['email'],
                "../../logs/",
                $codes
            );
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['add']['error'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['error_add'],
                $_SESSION['email'],
                "../../logs/",
                $codes
            );
        }
    }else{
        if($_POST['code'] == ""){
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['list']['error'];
            exit();
        }
        $code = $_POST['code'];
        $sql = "DELETE FROM codes WHERE code='".$code."'";
        if($conn->query($sql)){
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['list']['success'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['success_remove'],
                $_SESSION['email'],
                "../../logs/",
                $code
            );
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_code']['list']['error'];
            add_log(
                $_SESSION['lang']['logs']['quiz_code']['title'],
                $_SESSION['lang']['logs']['quiz_code']['error_remove'],
                $_SESSION['email'],
                "../../logs/",
                $code
            );
        }
    }
    $conn->close();
}