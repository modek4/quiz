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
    $response = array(
        'data' => "",
        'message' => ""
    );
    if(isset($_POST['delfile'])){
        unlink('../files/'.$_POST['delfile']);
        $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['success'];
        add_log(
            $_SESSION['lang']['logs']['quiz_download']['title'],
            $_SESSION['lang']['logs']['quiz_download']['success'],
            $_SESSION['email'],
            "../../logs/",
            $_POST['delfile']
        );
        echo json_encode($response);
        exit();
    }
    if(!isset($_POST['subject']) || empty($_POST['subject'])){
        $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['no_subject'];
        add_log(
            $_SESSION['lang']['logs']['quiz_download']['title'],
            $_SESSION['lang']['logs']['quiz_download']['no_subject'],
            $_SESSION['email'],
            "../../logs/"
        );
        echo json_encode($response);
        exit();
    }
    if(!isset($_POST['format']) || empty($_POST['format'])){
        $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['no_format'];
        add_log(
            $_SESSION['lang']['logs']['quiz_download']['title'],
            $_SESSION['lang']['logs']['quiz_download']['no_format'],
            $_SESSION['email'],
            "../../logs/"
        );
        echo json_encode($response);
        exit();
    }
    if(!isset($_POST['signed_type']) || empty($_POST['signed_type'])){
        $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['no_signed_type'];
        add_log(
            $_SESSION['lang']['logs']['quiz_download']['title'],
            $_SESSION['lang']['logs']['quiz_download']['no_signed_type'],
            $_SESSION['email'],
            "../../logs/"
        );
        echo json_encode($response);
        exit();
    }
    $subject = $_POST['subject'];
    $format = $_POST['format'];
    $signed_type = $_POST['signed_type'];
    //PDF format
    if($format == 'pdf'){
        require_once('../TCPDF/tcpdf.php');
        $result = mysqli_query($conn, "SELECT * FROM questions where subject='$subject'");
        if ($result->num_rows > 0) {
            $subject = str_replace(
                [" ", "/", "\\", ":", "*", "?", "\"", "<", ">", "|", "-"],
                "_",
                $subject
            );
            $subject .= "_quiz.pdf";
            $polishChars = array(
                'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
                'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A',
                'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O',
                'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'
            );
            $subject = strtr($subject, $polishChars);
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetProtection(array('noprint', 'nocopy', 'noedit'), '', '', 3, null, array(128, true));
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Kacper Grodzki');
            $pdf->SetTitle('Quiz '.$subject);
            $pdf->SetSubject($subject);
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $subject, PDF_HEADER_STRING);
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->AddPage();
            $questions_count = 1;
            while($row = $result -> fetch_assoc()){
                $question = $row['question'];
                $answers = $row['answers'];
                $correct_answers = $row['correct_answers'];
                $correct_answer = str_replace(";", "", $correct_answers);
                $question = str_replace(
                    ["<br/>", "<code><pre>", "</pre></code>", "&lt", "&gt"],
                    ["\n", "```\n", "\n```", "<", ">"],
                $question);
                if(preg_match('/src="(.*?)"/', $question, $match)){
                    $link = $match[1];
                    $question = explode("```", $question);
                    $question = $question[0]."```\n".$link."\n```";
                }
                $pdf->Write(0, $questions_count.'. '.$question."\n", '', 0, 'L', true, 0, false, false, 0);
                $answers = explode("♥", $answers);
                $answers_count = 0;
                foreach($answers as $answer){
                    $letter = chr(97 + $answers_count);
                    $answers_count++;
                    $answer = str_replace(["&lt","&gt"], ["<",">"], $answer);
                    if($signed_type == "onlyCorrect"){
                        if (preg_match('/'.$letter.'/',$correct_answers)) {
                            $pdf->SetTextColor(51, 181, 28);
                            $pdf->Write(0, $answer."\n", '', 0, 'L', true, 0, false, false, 0);
                            $pdf->SetTextColor(0, 0, 0);
                        }
                    } else if($signed_type == "allWith"){
                        if (preg_match('/'.$letter.'/',$correct_answers)) {
                            $pdf->SetTextColor(51, 181, 28);
                            $pdf->Write(0, $letter.') '.$answer."\n", '', 0, 'L', true, 0, false, false, 0);
                            $pdf->SetTextColor(0, 0, 0);
                        } else {
                            $pdf->Write(0, $letter.') '.$answer."\n", '', 0, 'L', true, 0, false, false, 0);
                        }
                    } else if($signed_type == "allWithout"){
                        $pdf->Write(0, $letter.') '.$answer."\n", '', 0, 'L', true, 0, false, false, 0);
                    }
                }
                $pdf->Write(0, "\n", '', 0, 'L', true, 0, false, false, 0);
                $questions_count++;
            }
            $pdf->Output(realpath(dirname(__FILE__)) . '/../files/' . $subject, 'F', true, 'UTF-8');
            $response['data'] = $subject;
            echo json_encode($response);
            exit();
        }else{
            add_log(
                $_SESSION['lang']['logs']['quiz_download']['title'],
                $_SESSION['lang']['logs']['quiz_download']['error'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }else if($format == 'json'){
        $result = mysqli_query($conn, "SELECT * FROM questions where subject='$subject'");
        if ($result->num_rows > 0) {
            $subject = str_replace(
                [" ", "/", "\\", ":", "*", "?", "\"", "<", ">", "|", "-"],
                "_",
                $subject
            );
            $subject .= "_quiz.json";
            $file = fopen('../files/' . $subject, "w");
            $header = array(
                'subject' => $subject,
                'link' => 'https://modek4.com/quiz',
                'date' => date("d.m.Y H:i"),
                'questions' => array()
            );
            $questions_count = 1;
            while ($row = $result->fetch_assoc()) {
                $question = $row['question'];
                $answers = $row['answers'];
                $correct_answers = $row['correct_answers'];
                $correct_answer = str_replace(";", "", $correct_answers);
                $question = str_replace(
                    ["<br/>", "<code><pre>", "</pre></code>", "&lt", "&gt"],
                    ["\n", "```\n", "```", "<", ">"],
                    $question
                );
                if (preg_match('/src="(.*?)"/', $question, $match)) {
                    $link = $match[1];
                    $question = explode("```", $question);
                    $question = $question[0] . "```\n" . $link . "\n```";
                }
                $answers = explode("♥", $answers);
                $answers_count = 0;
                $answers_array = array();
                $correct_answers_array = array();
                foreach ($answers as $answer) {
                    $letter = chr(97 + $answers_count);
                    $answers_count++;
                    $answer = str_replace(["&lt", "&gt"], ["<", ">"], $answer);
                    if ($signed_type == "onlyCorrect") {
                        if (preg_match('/' . $letter . '/', $correct_answer)) {
                            $answers_array[$letter] = $answer;
                        }
                    } else if ($signed_type == "allWith") {
                        if (preg_match('/' . $letter . '/', $correct_answer)) {
                            array_push($correct_answers_array, $letter);
                        }
                        $answers_array[$letter] = $answer;
                    } else if ($signed_type == "allWithout") {
                        $answers_array[$letter] = $answer;
                    }
                }
                if($signed_type == "onlyCorrect"){
                    $question = array(
                        'id' => $questions_count,
                        'question' => $question,
                        'correct_answers' => $answers_array
                    );
                }else if($signed_type == "allWith"){
                    $correct_answers_array = implode(", ", $correct_answers_array);
                    $question = array(
                        'id' => $questions_count,
                        'question' => $question,
                        'answers' => $answers_array,
                        'correct_answers' => $correct_answers_array
                    );
                }else if ($signed_type == "allWithout"){
                    $question = array(
                        'id' => $questions_count,
                        'question' => $question,
                        'answers' => $answers_array
                    );
                }
                array_push($header['questions'], $question);
                $questions_count++;
            }

            fwrite($file, json_encode($header, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            fclose($file);
            $response['data'] = $subject;
            echo json_encode($response);
            exit();
        }else{
            add_log(
                $_SESSION['lang']['logs']['quiz_download']['title'],
                $_SESSION['lang']['logs']['quiz_download']['error'],
                $_SESSION['email'],
                "../../logs/"
            );
        }
    }else{
        if($signed_type == "allWith"){
            if(!isset($_POST['separator']) || empty($_POST['separator'])){
                $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['no_separator'];
                add_log(
                    $_SESSION['lang']['logs']['quiz_download']['title'],
                    $_SESSION['lang']['logs']['quiz_download']['no_separator'],
                    $_SESSION['email'],
                    "../../logs/"
                );
                echo json_encode($response);
                exit();
            }
            $separator = $_POST['separator'];
        }
        //TXT format
        if($format == 'txt'){
            $result=mysqli_query($conn, "SELECT * FROM questions where subject='$subject'");
            if($result -> num_rows > 0){
                $subject = str_replace(
                    [" ","/","\\",":","*","?","\"","<",">","|","-"],
                    "_",
                $subject);
                $subject .= "_quiz.txt";
                $file = fopen('../files/'.$subject, "w");
                $questions_count = 1;
                fwrite($file, "$subject\n");
                fwrite($file, "###############################\n\n");
                fwrite($file, "https://modek4.com/quiz\n\n");
                $date = date("d.m.Y H:i");
                fwrite($file, "$date\n\n");
                fwrite($file, "###############################\n\n");
                while($row = $result -> fetch_assoc()){
                    $question = $row['question'];
                    $answers = $row['answers'];
                    $correct_answers = $row['correct_answers'];
                    $correct_answer = str_replace(";", "", $correct_answers);
                    $question = str_replace(
                        ["<br/>", "<code><pre>", "</pre></code>", "&lt", "&gt"],
                        ["\n", "```\n", "```", "<", ">"],
                    $question);
                    if(preg_match('/src="(.*?)"/', $question, $match)){
                        $link = $match[1];
                        $question = explode("```", $question);
                        $question = $question[0]."```\n".$link."\n```";
                    }
                    fwrite($file, $questions_count.'. '.$question."\n");
                    $answers = explode("♥", $answers);
                    $answers_count = 0;
                    foreach($answers as $answer){
                        $letter = chr(97 + $answers_count);
                        $answers_count++;
                        $answer = str_replace(["&lt","&gt"], ["<",">"], $answer);
                        if($signed_type == "onlyCorrect"){
                            if(preg_match('/'.$letter.'/',$correct_answer)){
                                fwrite($file, $answer."\n");
                            }
                        } else if($signed_type == "allWith"){
                            if(preg_match('/'.$letter.'/',$correct_answer)){
                                $answer = $answer.$separator;
                            }
                            fwrite($file, $letter.') '.$answer."\n");
                        } else if($signed_type == "allWithout"){
                            fwrite($file, $letter.') '.$answer."\n");
                        }
                    }
                    fwrite($file, "\n");
                    $questions_count++;
                }
                fclose($file);
                $response['data'] = $subject;
                echo json_encode($response);
                exit();
            }else{
                add_log(
                    $_SESSION['lang']['logs']['quiz_download']['title'],
                    $_SESSION['lang']['logs']['quiz_download']['error'],
                    $_SESSION['email'],
                    "../../logs/"
                );
            }
        //CSV format
        }else if($format == 'csv'){
            $result=mysqli_query($conn, "SELECT * FROM questions where subject='$subject'");
            if($result -> num_rows > 0){
                $subject = str_replace(
                    [" ","/","\\",":","*","?","\"","<",">","|","-"],
                    "_",
                $subject);
                $subject .= "_quiz.csv";
                $file = fopen('../files/'.$subject, "w");
                $date = date("d.m.Y H:i");
                fwrite($file, '"'.$subject.'","https://modek4.com/quiz","'.$date.'"'."\n");
                while($row = $result -> fetch_assoc()){
                    $question = $row['question'];
                    $answers = $row['answers'];
                    $correct_answers = $row['correct_answers'];
                    $correct_answer = str_replace(";", "", $correct_answers);
                    $question = str_replace(
                        ["<br/>", "<code><pre>", "</pre></code>", "&lt", "&gt"],
                        ["\n", "```\n", "```", "<", ">"],
                    $question);
                    if(preg_match('/src="(.*?)"/', $question, $match)){
                        $link = $match[1];
                        $question = explode("```", $question);
                        $question = $question[0]."```\n".$link."\n```";
                    }
                    fwrite($file, '"'.$question.'"');
                    $answers = explode("♥", $answers);
                    $answers_count = 0;
                    foreach($answers as $answer){
                        $letter = chr(97 + $answers_count);
                        $answers_count++;
                        $answer = str_replace(["&lt","&gt"], ["<",">"], $answer);
                        if($signed_type == "onlyCorrect"){
                            if(preg_match('/'.$letter.'/',$correct_answer)){
                                fwrite($file, ',"'.$answer.'"');
                            }
                        } else if($signed_type == "allWith"){
                            if(preg_match('/'.$letter.'/',$correct_answer)){
                                $answer = $answer.$separator;
                            }
                            fwrite($file, ',"'.$letter.') '.$answer.'"');
                        } else if($signed_type == "allWithout"){
                            fwrite($file, ',"'.$letter.') '.$answer.'"');
                        }
                    }
                    fwrite($file, "\n");
                }
                fclose($file);
                $response['data'] = $subject;
                echo json_encode($response);
                exit();
            }else{
                add_log(
                    $_SESSION['lang']['logs']['quiz_download']['title'],
                    $_SESSION['lang']['logs']['quiz_download']['error'],
                    $_SESSION['email'],
                    "../../logs/"
                );
            }
        }else{
            $response['message'] = $_SESSION['lang']['admin']['menage']['quiz_download']['no_format'];
            add_log(
                $_SESSION['lang']['logs']['quiz_download']['title'],
                $_SESSION['lang']['logs']['quiz_download']['no_format'],
                $_SESSION['email'],
                "../../logs/"
            );
            echo json_encode($response);
            exit();
        }
    }
    $conn->close();
}