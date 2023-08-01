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
    if (isset($_FILES['quiz_file']) && $_FILES['quiz_file']['error'] === UPLOAD_ERR_OK) {
        if(!isset($_POST['subject']) || empty($_POST['subject'])){
            echo $_SESSION['lang']['admin']['menage']['quiz_add']['no_subject'];
            add_log(
                $_SESSION['lang']['logs']['quiz_add']['title'],
                $_SESSION['lang']['logs']['quiz_add']['no_subject'],
                $_SESSION['email'],
                "../../logs/"
            );
            exit();
        }
        if(!isset($_POST['separator']) || empty($_POST['separator'])){
            echo $_SESSION['lang']['admin']['menage']['quiz_add']['no_separator'];
            add_log(
                $_SESSION['lang']['logs']['quiz_add']['title'],
                $_SESSION['lang']['logs']['quiz_add']['no_separator'],
                $_SESSION['email'],
                "../../logs/"
            );
            exit();
        }
        $separator = $_POST['separator'];
        $subject = $_POST['subject'];
        $filename = $_FILES['quiz_file']['name'];
        $filetype = $_FILES['quiz_file']['type'];
        $filesize = $_FILES['quiz_file']['size'];
        $filetmp = $_FILES['quiz_file']['tmp_name'];
        $contents = file_get_contents($filetmp, false, null);
        $encoding = mb_detect_encoding($contents, 'UTF-8, ISO-8859-1, ASCII');
        if ($encoding == 'UTF-8'){
            $lines = explode("\n", $contents);
            $question = '';
            $answers = '';
            $correct_answers = '';
            $sql = "SELECT max(id_question) FROM questions WHERE subject = '$subject'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $i = $row['max(id_question)'];
            $active_subject = true;
            if($i == null){
                $i = 0;
                $active_subject = false;
            }
            @$code_detected = false;
            $subject_add = true;
            $count_questions = $i;
            foreach ($lines as $line) {
                //format line
                if($code_detected == false){
                    $line = trim($line);
                }
                $line = addslashes($line);
                $line = str_replace("<", "&lt", $line);
                $line = str_replace(">", "&gt", $line);
                if (empty($line)) {
                    continue;
                }
                //detect code in question
                if (preg_match('/```/', $line, $matches)) {
                    if($code_detected == true){
                        $code_detected = false;
                        $question .= '</pre></code>';
                        continue;
                    } else {
                        $code_detected = true;
                        $question .= '<br/><code><pre>';
                        continue;
                    }
                }
                if($code_detected == true){
                    if (preg_match('/\bhttps?:\/\/\S+/i', $line, $matches)){
                        $question .= '<img src="' . $matches[0] . '" alt="'.$_SESSION['lang']['admin']['menage']['quiz_add']['image_alt_text'].'" loading="lazy" onclick="open_lightbox(this)"/>';
                    }else{
                        $question .= $line;
                    }
                    continue;
                }
                //detect question
                if (preg_match('/^\d+(.+)$/', $line, $matches)) {
                    $matches[1] = preg_replace('/^\d+/', '', $matches[1]);
                    $matches[1] = preg_replace('/^\s+/', '', $matches[1]);
                    if($matches[1][0] == '.' || $matches[1][0] == ")" || $matches[1][0] == '-'){
                        $matches[1] = substr($matches[1], 1);
                    }
                    $matches[1] = ltrim($matches[1]);
                    if (!empty($question)) {
                        $i++;
                        $answers = rtrim($answers, '♥');
                        $sql = "INSERT INTO questions (id_question, subject, question, answers, correct_answers) VALUES ($i, '$subject', '$question', '$answers', '$correct_answers')";
                        if(!mysqli_query($conn, $sql)){
                            $subject_add = false;
                        }
                    }
                    $question = $matches[1];
                    $answers = '';
                    $correct_answers = '';
                    $code_detected=false;
                } else {
                    //detect answers
                    if (preg_match('/^[a-zA-Z](.*)$/', $line, $matches)) {
                        $matches[1] = substr($matches[1], 1);
                        if ($matches[1][0] == '.' || $matches[1][0] == ")" || $matches[1][0] == '-' || $matches[1][0] == ']' || $matches[1][0] == '}') {
                            $matches[1] = substr($matches[1], 1);
                        }
                        $matches[1] = ltrim($matches[1]);
                        $answers .= $matches[1] . '♥';
                        $answers = str_replace($separator, '', $answers);
                        if (strpos($line, $separator) !== false) {
                            $letter_count = substr_count($answers, '♥') - 1;
                            $correct_answers .= chr(97 + $letter_count) . ';';
                        }
                    }
                }
            }
            //add last question
            if (!empty($question)) {
                $answers = rtrim($answers, '♥');
                $i++;
                $sql = "INSERT INTO questions (id_question, subject, question, answers, correct_answers) VALUES ($i, '$subject', '$question', '$answers', '$correct_answers')";
                if(!mysqli_query($conn, $sql)){
                    $subject_add = false;
                }
            }
            //add subject
            if($active_subject == false || isset($_POST['term'])){
                $term = $_POST['term'];
                $sql = "INSERT INTO subjects VALUES ('NULL', '$subject', '0', '$term', '')";
                if(!mysqli_query($conn, $sql)){
                    $subject_add = false;
                }else{
                    echo $_SESSION['lang']['admin']['menage']['quiz_add']['success_1']." ".$term." ".$_SESSION['lang']['admin']['menage']['quiz_add']['success_2']." ".$subject;
                    add_log(
                        $_SESSION['lang']['logs']['quiz_add']['title'],
                        $_SESSION['lang']['logs']['quiz_add']['success'],
                        $_SESSION['email'],
                        "../../logs/",
                        array(
                            "subject" => $subject,
                            "term" => $term
                        )
                    );
                }
            }else{
                $sql = "SELECT id, analytic FROM analytics WHERE subject='$subject'";
                if($result = $conn->query($sql)){
                    //update analytic table
                    if($result->num_rows != 0){
                        $row = $result->fetch_assoc();
                        $analytic_old = json_decode($row['analytic'], true);
                        for($j = $count_questions;$j < $i;$j++){
                            array_push($analytic_old, array(
                                "id" => $j+1,
                                "correct" => 0,
                                "incorrect" => 0,
                                "halfcorrect" => 0,
                                "checked" => 0,
                                "maxchecked" => 0,
                                "count" => 0
                            ));
                        }
                        $analytic_old = json_encode($analytic_old, JSON_UNESCAPED_UNICODE);
                        $sql = "UPDATE analytics SET analytic='$analytic_old' WHERE subject='$subject' AND id=".$row['id'];
                        if(!mysqli_query($conn, $sql)){
                            $subject_add = false;
                        }
                    }
                    //update analytic files
                    $analytic_folder = "../../analytic/";
                    $analytic_files = scandir($analytic_folder);
                    foreach ($analytic_files as $analytic_file){
                        if($analytic_file == '.' || $analytic_file == '..'){
                            continue;
                        }
                        if(strpos($analytic_file, $subject) !== false){
                            $analytic_file = $analytic_folder.$analytic_file;
                            $analytic_file_content = file_get_contents($analytic_file);
                            $analytic_file_content = json_decode($analytic_file_content, true);
                            for($j = $count_questions;$j < $i;$j++){
                                array_push($analytic_file_content, array(
                                    "id" => $j+1,
                                    "correct" => 0,
                                    "incorrect" => 0,
                                    "halfcorrect" => 0,
                                    "checked" => 0,
                                    "maxchecked" => 0,
                                    "count" => 0
                                ));
                            }
                            $analytic_file_content = json_encode($analytic_file_content, JSON_UNESCAPED_UNICODE);
                            file_put_contents($analytic_file, $analytic_file_content);
                        }
                    }
                    if($subject_add){
                        echo $_SESSION['lang']['admin']['menage']['quiz_add_more']['success']." $subject";
                        add_log(
                            $_SESSION['lang']['logs']['quiz_add']['title'],
                            $_SESSION['lang']['logs']['quiz_add']['success_more'],
                            $_SESSION['email'],
                            "../../logs/",
                            $subject
                        );
                    }else{
                        echo $_SESSION['lang']['admin']['menage']['quiz_add_more']['error_analytic']." $subject";
                        add_log(
                            $_SESSION['lang']['logs']['quiz_add']['title'],
                            $_SESSION['lang']['logs']['quiz_add']['error_more_analytic'],
                            $_SESSION['email'],
                            "../../logs/",
                            $subject
                        );
                    }
                }
            }
        } else {
            echo $_SESSION['lang']['admin']['menage']['quiz_add']['no_extension'];
            add_log(
                $_SESSION['lang']['logs']['quiz_add']['title'],
                $_SESSION['lang']['logs']['quiz_add']['no_extension'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }else{
        echo $_SESSION['lang']['admin']['menage']['quiz_add']['no_file'];
        add_log(
            $_SESSION['lang']['logs']['quiz_add']['title'],
            $_SESSION['lang']['logs']['quiz_add']['no_file'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}