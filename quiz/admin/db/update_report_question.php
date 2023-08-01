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
    if(isset($_POST['subject']) && isset($_POST['question_id']) && isset($_POST['question']) && isset($_POST['answers']) && isset($_POST['correct_answers'])){
        $subject = $_POST['subject'];
        $question_id = $_POST['question_id'];
        $question = $_POST['question'];
        $answers = $_POST['answers'];
        $answers = addslashes($answers);
        $answers = str_replace(["<",">"], ["&lt","&gt"], $answers);
        $question = addslashes($question);
        if (strpos($question, "```") !== false){
            $question = explode("```", $question);
            $question_main = str_replace(["<",">"], ["&lt","&gt"], $question[0]);
            $question_code = "";
            if (preg_match('/\bhttps?:\/\/\S+/i', $question[1], $matches)){
                $question_code = "<br/><code><pre>".$question[1]."</pre></code>";
            }else{
                $question_code = "<br/><code><pre>".str_replace(["<",">"], ["&lt","&gt"], $question[1])."</pre></code>";
            }
            $question = $question_main.$question_code;
        }else{
            $question = str_replace(["<",">"], ["&lt","&gt"], $question);
        }
        $correct_answers = $_POST['correct_answers'];
        //update question
        $sql = "UPDATE questions SET question = '$question', answers = '$answers', correct_answers = '$correct_answers' WHERE id_question = '$question_id' AND subject = '$subject'";
        if(mysqli_query($conn, $sql)){
            //delete report
            $sql = "DELETE FROM reports WHERE question_id = '$question_id' AND subject = '$subject'";
            if(mysqli_query($conn, $sql)){
                echo $_SESSION['lang']['admin']['reports']['update']['success'];
                add_log(
                    $_SESSION['lang']['logs']['reports']['title'],
                    $_SESSION['lang']['logs']['reports']['update_success'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "subject" => $subject,
                        "question_id" => $question_id
                    )
                );
            }else{
                echo $_SESSION['lang']['admin']['reports']['update']['error'];
                add_log(
                    $_SESSION['lang']['logs']['reports']['title'],
                    $_SESSION['lang']['logs']['reports']['update_error'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "subject" => $subject,
                        "question_id" => $question_id
                    )
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['reports']['update']['error'];
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['update_error'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $subject,
                    "question_id" => $question_id
                )
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['reports']['update']['error_no_variable'];
        add_log(
            $_SESSION['lang']['logs']['reports']['title'],
            $_SESSION['lang']['logs']['reports']['update_error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}