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
    if(isset($_POST['subject']) && isset($_POST['question_id'])){
        $subject = $_POST['subject'];
        $question_id = $_POST['question_id'];
        //delete question
        $sql = "DELETE FROM questions WHERE id_question = '$question_id' AND subject = '$subject'";
        if(mysqli_query($conn, $sql)){
            //delete report
            $sql = "DELETE FROM reports WHERE question_id = '$question_id' AND subject = '$subject'";
            if(mysqli_query($conn, $sql)){
                //update question_id in reports
                $sql = "UPDATE reports SET question_id = question_id - 1 WHERE subject = '$subject' AND question_id > $question_id";
                if(mysqli_query($conn, $sql)){
                    //update analytic
                    $sql = "SELECT id FROM analytics WHERE subject = '$subject'";
                    $result = mysqli_query($conn, $sql);
                    if($result){
                        $update_analytic = true;
                        while($row = mysqli_fetch_assoc($result)){
                            $id = $row['id'];
                            $sql = "SELECT code, analytic FROM analytics WHERE id = '$id' AND subject = '$subject'";
                            $result_analytic = mysqli_query($conn, $sql);
                            if($result_analytic){
                                $row_analytic = mysqli_fetch_assoc($result_analytic);
                                $analytic = json_decode($row_analytic['analytic'], true);
                                $analytic[$question_id-1] = null;
                                $analytic = array_values(array_filter($analytic));
                                for($i = $question_id-1; $i < count($analytic); $i++){
                                    if($analytic[$i] != null){
                                        $analytic[$i]['id'] = $analytic[$i]['id'] - 1;
                                    }
                                }
                                $encode_analytic = json_encode($analytic);
                                $sql = "UPDATE analytics SET analytic = '$encode_analytic' WHERE id = '$id' AND subject = '$subject'";
                                $files = scandir("../../analytic/");
                                $file_name = $row_analytic['code']."-".$subject.".json";
                                if(file_exists("../../analytic/".$file_name)){
                                    $file = fopen("../../analytic/".$file_name, "w");
                                    fwrite($file, $encode_analytic);
                                    fclose($file);
                                }
                                if(!mysqli_query($conn, $sql)){
                                    $update_analytic = false;
                                }
                            }else{
                                $update_analytic = false;
                            }
                        }
                        if($update_analytic){
                            //update question_id in questions
                            $update_questions = true;
                            $sql = "SELECT max(id_question) FROM questions WHERE subject = '$subject'";
                            $result = mysqli_query($conn, $sql);
                            if($result){
                                $row = mysqli_fetch_assoc($result);
                                $max_id = $row['max(id_question)'];
                                $id = $question_id + 1;
                                for($i = $id; $i <= $max_id; $i++){
                                    $sql = "UPDATE questions SET id_question = '$i' - 1 WHERE id_question = '$i' AND subject = '$subject'";
                                    if(!mysqli_query($conn, $sql)){
                                        $update_questions = false;
                                    }
                                }
                                if($update_questions){
                                    //update scores
                                    $sql = "SELECT id, score, question_count, answers FROM scores WHERE subject = '$subject' AND answers<>''";
                                    $result = mysqli_query($conn, $sql);
                                    if($result){
                                        $update_scores = true;
                                        while($row = mysqli_fetch_assoc($result)){
                                            if($row['question_count'] - 1 == 0){
                                                $sql = "DELETE FROM scores WHERE id = '".$row['id']."' AND subject = '$subject'";
                                                if(!mysqli_query($conn, $sql)){
                                                    $update_scores = false;
                                                }
                                            }else{
                                                $id = $row['id'];
                                                $score = $row['score'];
                                                $question_count = $row['question_count'];
                                                $answers = json_decode($row['answers'], true);
                                                $delete_answer = null;
                                                foreach($answers as $answer){
                                                    if($answer['id_question'] == $question_id){
                                                        $delete_answer = $answer;
                                                        $id_question_delete = $answer['id'];
                                                    }
                                                }
                                                if($delete_answer == null){
                                                    continue;
                                                }
                                                if($delete_answer['answers'] != 0){
                                                    $answer_letters = str_split($delete_answer['answers']);
                                                    $correct_letters = str_split($delete_answer['correct_answers']);
                                                    $common_letters = count(array_intersect($answer_letters, $correct_letters));
                                                    $score_assign = round((1.00 / count($correct_letters))*$common_letters,2);
                                                    $score = $score - $score_assign;
                                                }
                                                $answers[$id_question_delete-1] = null;
                                                $answers = array_values(array_filter($answers));
                                                for($i = $id_question_delete-1; $i < count($answers); $i++){
                                                    if($answers[$i] != null){
                                                        $answers[$i]['id'] = $answers[$i]['id'] - 1;
                                                    }
                                                }
                                                for($i = 0; $i < count($answers); $i++){
                                                    if($answers[$i] != null){
                                                        if($answers[$i]['id_question'] > $delete_answer['id_question']){
                                                            $answers[$i]['id_question'] = $answers[$i]['id_question'] - 1;
                                                        }
                                                    }
                                                }
                                                $encode_answers = json_encode($answers);
                                                $sql = "UPDATE scores SET score = '$score', question_count = '$question_count' - 1, answers = '$encode_answers' WHERE id = '$id' AND subject = '$subject'";
                                                if(!mysqli_query($conn, $sql)){
                                                    $update_scores = false;
                                                }
                                            }
                                        }
                                        if($update_scores){
                                            echo $_SESSION['lang']['admin']['reports']['remove']['success'];
                                            add_log(
                                                $_SESSION['lang']['logs']['reports']['title'],
                                                $_SESSION['lang']['logs']['reports']['remove_success'],
                                                $_SESSION['email'],
                                                "../../logs/",
                                                array(
                                                    "subject" => $subject,
                                                    "question_id" => $question_id
                                                )
                                            );
                                        }else{
                                            echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                                            add_log(
                                                $_SESSION['lang']['logs']['reports']['title'],
                                                $_SESSION['lang']['logs']['reports']['remove_error'],
                                                $_SESSION['email'],
                                                "../../logs/",
                                                array(
                                                    "subject" => $subject,
                                                    "question_id" => $question_id,
                                                    "error" => $_SESSION['lang']['logs']['reports']['remove']['update_scores']
                                                )
                                            );
                                        }
                                    }else{
                                        echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                                        add_log(
                                            $_SESSION['lang']['logs']['reports']['title'],
                                            $_SESSION['lang']['logs']['reports']['remove_error'],
                                            $_SESSION['email'],
                                            "../../logs/",
                                            array(
                                                "subject" => $subject,
                                                "question_id" => $question_id,
                                                "error" => $_SESSION['lang']['logs']['reports']['remove']['update_scores']
                                            )
                                        );
                                    }
                                }else{
                                    echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                                    add_log(
                                        $_SESSION['lang']['logs']['reports']['title'],
                                        $_SESSION['lang']['logs']['reports']['remove_error'],
                                        $_SESSION['email'],
                                        "../../logs/",
                                        array(
                                            "subject" => $subject,
                                            "question_id" => $question_id,
                                            "error" => $_SESSION['lang']['logs']['reports']['remove']['update_ids']
                                        )
                                    );
                                }
                            }else{
                                echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                                add_log(
                                    $_SESSION['lang']['logs']['reports']['title'],
                                    $_SESSION['lang']['logs']['reports']['remove_error'],
                                    $_SESSION['email'],
                                    "../../logs/",
                                    array(
                                        "subject" => $subject,
                                        "question_id" => $question_id,
                                        "error" => $_SESSION['lang']['logs']['reports']['remove']['update_ids']
                                    )
                                );
                            }
                        }else{
                            echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                            add_log(
                                $_SESSION['lang']['logs']['reports']['title'],
                                $_SESSION['lang']['logs']['reports']['remove_error'],
                                $_SESSION['email'],
                                "../../logs/",
                                array(
                                    "subject" => $subject,
                                    "question_id" => $question_id,
                                    "error" => $_SESSION['lang']['logs']['reports']['remove']['update_analytic']
                                )
                            );
                        }
                    }else{
                        echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                        add_log(
                            $_SESSION['lang']['logs']['reports']['title'],
                            $_SESSION['lang']['logs']['reports']['remove_error'],
                            $_SESSION['email'],
                            "../../logs/",
                            array(
                                "subject" => $subject,
                                "question_id" => $question_id,
                                "error" => $_SESSION['lang']['logs']['reports']['remove']['update_analytic']
                            )
                        );
                    }
                }else{
                    echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                    add_log(
                        $_SESSION['lang']['logs']['reports']['title'],
                        $_SESSION['lang']['logs']['reports']['remove_error'],
                        $_SESSION['email'],
                        "../../logs/",
                        array(
                            "subject" => $subject,
                            "question_id" => $question_id,
                            "error" => $_SESSION['lang']['logs']['reports']['remove']['update_reports']
                        )
                    );
                }
            }else{
                echo $_SESSION['lang']['admin']['reports']['remove']['error'];
                add_log(
                    $_SESSION['lang']['logs']['reports']['title'],
                    $_SESSION['lang']['logs']['reports']['remove_error'],
                    $_SESSION['email'],
                    "../../logs/",
                    array(
                        "subject" => $subject,
                        "question_id" => $question_id,
                        "error" => $_SESSION['lang']['logs']['reports']['remove']['report']
                    )
                );
            }
        }else{
            echo $_SESSION['lang']['admin']['reports']['remove']['error'];
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['remove_error'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $subject,
                    "question_id" => $question_id,
                    "error" => $_SESSION['lang']['logs']['reports']['remove']['delete']
                )
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['reports']['remove']['error_no_variable'];
        add_log(
            $_SESSION['lang']['logs']['reports']['title'],
            $_SESSION['lang']['logs']['reports']['remove_error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}