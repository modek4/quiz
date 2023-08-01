<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    //subject list for share
    if(isset($_POST['share'])){
        $share = $_POST['share'] == 2 ? 0 : ($_POST['share'] == 1 ? 1 : 0);
        $sql = "SELECT * FROM subjects WHERE share=".$share;
    }else{
        $sql = "SELECT * FROM subjects";
    }
    if($result = mysqli_query($conn,$sql)){
        if(isset($_POST['share'])){
            echo "<option value=''>".$_SESSION['lang']['admin']['menage']['quiz_status']['subject']."</option>";
        }
        while($row = mysqli_fetch_assoc($result)){
            echo "<option value='".$row['subject']."'>".$row['subject']."</option>";
        }
    }
    $conn->close();
}