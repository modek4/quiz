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
    //change points analytic
    if(isset($_POST['correct']) && isset($_POST['incorrect']) && isset($_POST['halfcorrect'])){
        $sql = "UPDATE quiz_admin SET points_correct='".$_POST['correct']."', points_incorrect='".$_POST['incorrect']."', points_halfcorrect='".$_POST['halfcorrect']."'";
        if(mysqli_query($conn, $sql)){
            echo $_SESSION['lang']['admin']['menage']['quiz_analytic']['success'];
            add_log(
                $_SESSION['lang']['logs']['quiz_analytic']['title'],
                $_SESSION['lang']['logs']['quiz_analytic']['success'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "correct" => $_POST['correct'],
                    "incorrect" => $_POST['incorrect'],
                    "halfcorrect" => $_POST['halfcorrect']
                )
            );
        }else{
            echo $_SESSION['lang']['admin']['menage']['quiz_analytic']['error'];
            add_log(
                $_SESSION['lang']['logs']['quiz_analytic']['title'],
                $_SESSION['lang']['logs']['quiz_analytic']['error'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }else{
        //load random analytic points to list
        if(isset($_POST['correct_points']) && isset($_POST['incorrect_points']) && isset($_POST['halfcorrect_points']) && isset($_POST['random_points'])){
            if($_POST['correct_points'] == "" || $_POST['incorrect_points'] == "" || $_POST['halfcorrect_points'] == ""){
                $sql = "SELECT * FROM quiz_admin";
                if($result = mysqli_query($conn, $sql)){
                    $row = mysqli_fetch_array($result);
                    $points_correct = $row['points_correct'];
                    $points_incorrect = $row['points_incorrect'];
                    $points_halfcorrect = $row['points_halfcorrect'];
                }
            }else{
                $points_correct = $_POST['correct_points'];
                $points_incorrect = $_POST['incorrect_points'];
                $points_halfcorrect = $_POST['halfcorrect_points'];
            }
            $random_points = (float)$_POST['random_points'];
            $sql = "SELECT analytic FROM analytics ORDER BY RAND()";
            $random = [];
            if($result = mysqli_query($conn, $sql)){
                if(mysqli_num_rows($result) == 0){
                    $response = [0,0,0,0,0,0,0];
                    echo json_encode($response);
                    exit();
                }
                $find_random = false;
                while($row = mysqli_fetch_array($result)){
                    $analytic = $row['analytic'];
                    $analytic = json_decode($analytic, true);
                    shuffle($analytic);
                    foreach($analytic as $key){
                        if($find_random){
                            break;
                        }else{
                            if($key['count'] == 0){
                                unset($key);
                            }else{
                                $random[] = $key;
                                $find_random = true;
                            }
                        }
                    }
                    if($find_random){
                        break;
                    }
                }
                $response = [];
                $response[0] = $random[0]['correct']; $correct_analytic = $response[0];
                $response[1] = $random[0]['incorrect']; $incorrect_analytic = $response[1];
                $response[2] = $random[0]['halfcorrect']; $halfcorrect_analytic = $response[2];
                $response[3] = $random[0]['checked']; $checked_analytic = $response[3];
                $response[4] = $random[0]['maxchecked']; $max_checked_analytic = $response[4];
                $response[5] = $random[0]['count']; $count_analytic = $response[5];
                require_once("../../db/points_system.php");
                $response[6] = calculate_points($points_correct, $points_incorrect, $points_halfcorrect, $correct_analytic, $incorrect_analytic, $halfcorrect_analytic, $checked_analytic, $max_checked_analytic, $count_analytic)." ".$_SESSION['lang']['admin']['menage']['quiz_analytic']['showcase']['points'];
                echo json_encode($response);
            }else{
                $response = [0,0,0,0,0,0,0];
                echo json_encode($response);
            }
        }else{
            //load analytic points to form
            $response = [];
            $sql = "SELECT * FROM quiz_admin";
            if($result = mysqli_query($conn, $sql)){
                $row = mysqli_fetch_array($result);
                $response[0] = $row['points_correct'];
                $response[1] = $row['points_incorrect'];
                $response[2] = $row['points_halfcorrect'];
                echo json_encode($response);
            }
        }
    }
    $conn->close();
}