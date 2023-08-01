<?php
session_start();
require_once("connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if (isset($_POST['question_number']) && isset($_POST['subject'])){
        if(isset($_SESSION['email']) && isset($_SESSION['device'])){
            $sql = "SELECT open FROM devices WHERE email='".$_SESSION['email']."' AND udevices='".$_SESSION['device']."'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($result);
            if($row['open'] == 0){
                $_SESSION['open'] = 0;
            }
        }
        if(isset($_SESSION['open'])){
            if($_SESSION['open'] == 0){
                echo "valid";
                exit();
            }
        }
        $subject = $_POST['subject'];
        $question_number = $_POST['question_number'];
        $sql = "SELECT correct_answers FROM questions WHERE subject = '$subject' AND id_question = '$question_number'";
        if (mysqli_query($conn, $sql)) {
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($result);
            $correct = str_replace(';', '', $row['correct_answers']);
            echo $correct;
        } else {
            echo "";
        }
    }
    $conn->close();
}
?>