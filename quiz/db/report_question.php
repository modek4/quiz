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
    if(isset($_POST['subject']) && isset($_POST['report_id']) && isset($_POST['selected_correct'])){
        $subject = $_POST['subject'];
        $report_id = $_POST['report_id'];
        $selected_correct = $_POST['selected_correct'];
        $email = $_SESSION['email'];
        $sql = "SELECT * FROM reports WHERE question_id = '$report_id' AND subject='$subject'";
        $result = mysqli_query($conn, $sql);
        //check if report already exists
        if(mysqli_num_rows($result) > 0){
            $dateNotify = date("Y-m-d H:i");
            $sql = "INSERT INTO `notification` VALUES ('NULL', '".$_SESSION['email']."', '".$_SESSION['lang']['notification']['report_title']."', '".$_SESSION['lang']['notification']['reported_text']." $report_id ".$_SESSION['lang']['notification']['report_text_subject']." $subject.<br/>$dateNotify', '1')";
            if($conn->query($sql)){
                echo $_SESSION['lang']['quiz']['report']['already_reported'];
                add_log(
                    $_SESSION['lang']['logs']['report']['title'],
                    $_SESSION['lang']['logs']['report']['already_reported'],
                    $_SESSION['email'],
                    "../logs/",
                    array(
                        "subject" => $subject,
                        "report_id" => $report_id
                    )
                );
            }else{
                add_log(
                    $_SESSION['lang']['logs']['report']['title'],
                    $_SESSION['lang']['logs']['report']['already_reported_error'],
                    $_SESSION['email'],
                    "../logs/",
                    array(
                        "subject" => $subject,
                        "report_id" => $report_id
                    )
                );
            }
            exit();
        }else{
            //create report
            $dateNotify = date("Y-m-d H:i");
            if($selected_correct == "0"){
                $selected_correct = NULL;
            }
            $sql = "INSERT INTO reports VALUES (NULL, '$subject', '$report_id', '$selected_correct', '$email', '$dateNotify')";
            if(mysqli_query($conn, $sql)){
                $dateNotify = date("Y-m-d H:i");
                $sql = "INSERT INTO `notification` VALUES ('NULL', '".$_SESSION['email']."', '".$_SESSION['lang']['notification']['report_title']."', '".$_SESSION['lang']['notification']['report_text']." $report_id ".$_SESSION['lang']['notification']['report_text_subject']." $subject ".$_SESSION['lang']['notification']['report_text_success'].".<br/>$dateNotify', '1')";
                if($conn->query($sql)){
                    echo $_SESSION['lang']['quiz']['report']['report_sent'];
                    add_log(
                        $_SESSION['lang']['logs']['report']['title'],
                        $_SESSION['lang']['logs']['report']['sent_success'],
                        $_SESSION['email'],
                        "../logs/",
                        array(
                            "subject" => $subject,
                            "report_id" => $report_id
                        )
                    );
                }else{
                    add_log(
                        $_SESSION['lang']['logs']['report']['title'],
                        $_SESSION['lang']['logs']['report']['sent_error'],
                        $_SESSION['email'],
                        "../logs/",
                        array(
                            "subject" => $subject,
                            "report_id" => $report_id
                        )
                    );
                }
            }else{
                echo $_SESSION['lang']['quiz']['report']['report_error'];
                add_log(
                    $_SESSION['lang']['logs']['report']['title'],
                    $_SESSION['lang']['logs']['report']['error'],
                    $_SESSION['email'],
                    "../logs/",
                    array(
                        "subject" => $subject,
                        "report_id" => $report_id
                    )
                );
            }
        }
    }else{
        add_log(
            $_SESSION['lang']['logs']['report']['title'],
            $_SESSION['lang']['logs']['report']['error'],
            $_SESSION['email'],
            "../logs/"
        );
    }
    $conn->close();
}
?>