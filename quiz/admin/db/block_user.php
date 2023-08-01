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
    if(isset($_POST['code'])){
        $response = array(
            'data' => "",
            'message' => ""
        );
        $sql = "SELECT * FROM quiz_users WHERE code='".$_POST['code']."'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if($row['dark'] == -1){
            //unblock user
            if(isset($_SESSION['mod']) && $_SESSION['mod']==true){
                $response['data'] = "";
                $response['message'] = $_SESSION['lang']['admin']['users']['block']['mod_text'];
                exit(json_encode($response));
            }
            $sql = "UPDATE quiz_users SET dark=0 WHERE code='".$_POST['code']."'";
            if($conn->query($sql) === TRUE){
                $response['data'] = $_SESSION['lang']['admin']['users']['block']['text'];
                $response['message'] = $_SESSION['lang']['admin']['users']['unblock']['success'];
                add_log(
                    $_SESSION['lang']['logs']['block_user']['title'],
                    $_SESSION['lang']['logs']['block_user']['unblock_success'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['code']
                );
            }else{
                $response['data'] = $_SESSION['lang']['admin']['users']['unblock']['text'];
                $response['message'] = $_SESSION['lang']['admin']['users']['unblock']['error'];
                add_log(
                    $_SESSION['lang']['logs']['block_user']['title'],
                    $_SESSION['lang']['logs']['block_user']['unblock_error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['code']
                );
            }
        }else{
            //block user
            if(isset($_SESSION['mod']) && $_SESSION['mod']==true){
                $response['data'] = "";
                $response['message'] = $_SESSION['lang']['admin']['users']['block']['mod_text'];
                exit(json_encode($response));
            }
            $sql = "UPDATE quiz_users SET dark=-1 WHERE code='".$_POST['code']."'";
            if($conn->query($sql) === TRUE){
                $response['data'] = $_SESSION['lang']['admin']['users']['unblock']['text'];
                $response['message'] = $_SESSION['lang']['admin']['users']['block']['success'];
                add_log(
                    $_SESSION['lang']['logs']['block_user']['title'],
                    $_SESSION['lang']['logs']['block_user']['block_success'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['code']
                );
            }else{
                $response['data'] = $_SESSION['lang']['admin']['users']['block']['text'];
                $response['message'] = $_SESSION['lang']['admin']['users']['block']['error'];
                add_log(
                    $_SESSION['lang']['logs']['block_user']['title'],
                    $_SESSION['lang']['logs']['block_user']['block_error'],
                    $_SESSION['email'],
                    "../../logs/",
                    $_POST['code']
                );
            }
        }
        echo json_encode($response);
    }
    $conn->close();
}