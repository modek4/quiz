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
        $code = $_SESSION['code'];
        $file_name = "../analytic/".$code."_score.json";
        $correct = "";
        if(file_exists($file_name)){
            $json_data = json_decode(file_get_contents($file_name),true);
            foreach ($json_data as $value) {
                if($value['id_question']==$question_number){
                    $correct = str_replace(';', '', $value['correct_answers']);
                    break;
                }
            }
        }
        $_SESSION['last_answer_question'] = $question_number;
        echo $correct;
    }
    $conn->close();
}
?>