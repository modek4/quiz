<?php
session_start();
require_once("connect.php");
require_once("../log.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['score']) && isset($_POST['question_count']) && isset($_POST['subject']) && isset($_SESSION['email']) && isset($_POST['quiz_start'])){
        $score=$_POST['score'];
        $question_count=$_POST['question_count'];
        $subject=$_POST['subject'];
        $email = $_SESSION['email'];
        $quiz_start = $_POST['quiz_start'];
        $date = date("Y-m-d H:i:s");
        $time = strtotime($date) - strtotime($quiz_start);
        $time = gmdate("H:i:s", $time);
        $result = mysqli_query($conn, "SELECT * FROM scores WHERE `email`='$email' AND `subject`='$subject' AND `end_date`='$date'");
        if (mysqli_num_rows($result) == 0) {
            $code = $_SESSION['code'];
            $file_name_change = "../analytic/".$code."_score.json";
            if(file_exists($file_name_change)){
                $file = fopen($file_name_change, "r");
                $end_date = fread($file, filesize($file_name_change));
                fclose($file);
            }else{
                add_log(
                    $_SESSION['lang']['logs']['save_score']['title'],
                    $_SESSION['lang']['logs']['save_score']['nofile'],
                    $_SESSION['email'],
                    "../logs/",
                    array(
                        "subject" => $subject,
                        "score" => $score,
                        "question_count" => $question_count,
                        "quiz_start" => $quiz_start,
                        "date" => $date,
                        "time" => $time
                    )
                );
            }
            $sql = "INSERT INTO scores VALUES ('NULL', '$email', '$subject', '$score', '$question_count', '$date', '$time', '$end_date')";
            if(mysqli_query($conn, $sql)){
                unlink($file_name_change);
                $file_name_analytic = "../analytic/".$code."-".$subject.".json";
                if(file_exists($file_name_analytic)){
                    $file = fopen($file_name_analytic, "r");
                    $anyltic = fread($file, filesize($file_name_analytic));
                    fclose($file);
                    $sql_analytics = "UPDATE analytics SET analytic = '$anyltic' WHERE code = '".$_SESSION['code']."' AND subject = '$subject'";
                    if(mysqli_query($conn, $sql_analytics)){
                        unlink($file_name_analytic);
                        $date_notify = date("Y-m-d H:i");
                        $score_per = round(($score/$question_count)*100, 2);
                        $sql = "INSERT INTO `notification` VALUES ('NULL', '".$email."', '".$_SESSION['lang']['notification']['save_score_title']."', '".$_SESSION['lang']['notification']['save_score_text_sub_1']." $score/$question_count ($score_per%) ".$_SESSION['lang']['notification']['save_score_text_sub_2']." $subject ".$_SESSION['lang']['notification']['save_score_text_sub_3']."<br/>$date_notify', '1')";
                        if(mysqli_query($conn, $sql)){
                            echo $_SESSION['lang']['notification']['save_score_title'];
                            add_log(
                                $_SESSION['lang']['logs']['save_score']['title'],
                                $_SESSION['lang']['logs']['save_score']['success'],
                                $_SESSION['email'],
                                "../logs/",
                                array(
                                    "subject" => $subject,
                                    "score" => $score,
                                    "question_count" => $question_count,
                                    "quiz_start" => $quiz_start,
                                    "date" => $date,
                                    "time" => $time
                                )
                            );
                        }else{
                            add_log(
                                $_SESSION['lang']['logs']['save_score']['title'],
                                $_SESSION['lang']['logs']['save_score']['nonotification'],
                                $_SESSION['email'],
                                "../logs/",
                                array(
                                    "subject" => $subject,
                                    "score" => $score,
                                    "question_count" => $question_count,
                                    "quiz_start" => $quiz_start,
                                    "date" => $date,
                                    "time" => $time
                                )
                            );
                        }
                    }else{
                        add_log(
                            $_SESSION['lang']['logs']['save_score']['title'],
                            $_SESSION['lang']['logs']['save_score']['analytic_error'],
                            $_SESSION['email'],
                            "../logs/",
                            array(
                                "subject" => $subject,
                                "score" => $score,
                                "question_count" => $question_count,
                                "quiz_start" => $quiz_start,
                                "date" => $date,
                                "time" => $time
                            )
                        );
                    }
                }else{
                    add_log(
                        $_SESSION['lang']['logs']['save_score']['title'],
                        $_SESSION['lang']['logs']['save_score']['nofile'],
                        $_SESSION['email'],
                        "../logs/",
                        array(
                            "subject" => $subject,
                            "score" => $score,
                            "question_count" => $question_count,
                            "quiz_start" => $quiz_start,
                            "date" => $date,
                            "time" => $time
                        )
                    );
                }
            }else{
                add_log(
                    $_SESSION['lang']['logs']['save_score']['title'],
                    $_SESSION['lang']['logs']['save_score']['error'],
                    $_SESSION['email'],
                    "../logs/",
                    array(
                        "subject" => $subject,
                        "score" => $score,
                        "question_count" => $question_count,
                        "quiz_start" => $quiz_start,
                        "date" => $date,
                        "time" => $time
                    )
                );
            }
        }else{
            add_log(
                $_SESSION['lang']['logs']['save_score']['title'],
                $_SESSION['lang']['logs']['save_score']['same_score'],
                $_SESSION['email'],
                "../logs/",
                array(
                    "subject" => $subject,
                    "score" => $score,
                    "question_count" => $question_count,
                    "quiz_start" => $quiz_start,
                    "date" => $date,
                    "time" => $time
                )
            );
        }
    }else{
        add_log(
            $_SESSION['lang']['logs']['save_score']['title'],
            $_SESSION['lang']['logs']['save_score']['error_no_variable'],
            $_SESSION['email'],
            "../logs/"
        );
    }
    $conn->close();
}
?>