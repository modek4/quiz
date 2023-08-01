<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    $sql = "SELECT code, term FROM codes WHERE code_use = 1";
    if($result = mysqli_query($conn,$sql)){
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                echo "<option value='".$row["code"]."'>".$row["code"]." : ".$row["term"]."</option>";
            }
        }else{
            echo "<option value=''>".$_SESSION['lang']['admin']['menage']['quiz_code']['list']['no_code']."</option>";
        }
    }else{
        echo $_SESSION['lang']['admin']['menage']['quiz_code']['list']['error_list'];
    }
    $conn->close();
}