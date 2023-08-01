<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['mod'])){
        $mod = $_POST['mod'];
        //list of users to change moderator add
        if($mod == "add"){
            $sql = "SELECT * FROM quiz_users WHERE email not in (SELECT email FROM users)";
            if($result = mysqli_query($conn,$sql)){
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<option value='".$row["email"]."'>".$row["email"]."</option>";
                    }
                }else{
                    echo "<option value=''>".$_SESSION['lang']['admin']['menage']['quiz_moderation']['no_users']."</option>";
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error'];
            }
        //list of users to change moderator remove
        }else if($mod == "remove"){
            $sql = "SELECT * FROM users WHERE moderator = 1";
            if($result = mysqli_query($conn,$sql)){
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<option value='".$row["email"]."'>".$row["email"]."</option>";
                    }
                }else{
                    echo "<option value=''>".$_SESSION['lang']['admin']['menage']['quiz_moderation']['no_users']."</option>";
                }
            }else{
                echo $_SESSION['lang']['admin']['menage']['quiz_moderation']['error'];
            }
        }
    }else{
        echo "<option value=''>".$_SESSION['lang']['admin']['menage']['quiz_moderation']['no_users']."</option>";
    }
    $conn->close();
}