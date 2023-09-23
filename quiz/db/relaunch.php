<?php
session_start();
require_once("../log.php");
if(isset($_POST['code']) && isset($_POST['relaunch'])){
    $code = $_POST['code'];
    $relaunch = $_POST['relaunch'];
    $file_name_change = "../analytic/".$code."_score.json";
    $data = null;
    if(file_exists($file_name_change)){
        $file = fopen($file_name_change, "r");
        $data = fread($file, filesize($file_name_change));
        fclose($file);
        $data = json_decode($data, true);
    }
    if($data != null){
        $date = array();
        $points = 0;
        foreach($data as $item){
            $subject = $item['subject'];
            $question_count = $item['id'];
            if($item['date'] != null){
                array_push($date, $item['date']);
                //calculate points
                $correct_answers = str_split(str_replace(';','',$item['correct_answers']));
                $user_answers = str_split(str_replace(';','',$item['answers']));
                foreach ($user_answers as $user_answer) {
                    $user_answer = trim($user_answer);
                    if (in_array($user_answer, $correct_answers)) {
                        $points += 1.00000000 / count($correct_answers);
                    }
                }
            }
        }
        //calculate time
        if(!empty($date)){
            $date_max = max($date);
            $date_min = min($date);
            $diff = abs(strtotime($date_max) - strtotime($date_min));
            $hours = floor($diff / (60*60));
            $hours < 10 ? $hours = "0".$hours : $hours;
            $mins = floor(($diff - ($hours*60*60)) / (60));
            $mins < 10 ? $mins = "0".$mins : $mins;
            $secs = floor(($diff - (($hours*60*60)+($mins*60))));
            $secs < 10 ? $secs = "0".$secs : $secs;
            $date = $hours.":".$mins.":".$secs;
            $date = (string)$date;
        }else{
            $date = "00:00:00";
        }
    }else{
        $subject = null;
        $question_count = 0;
        $date = "00:00:00";
        $points = 0;
    }
    $date = explode(":", $date);
    $date = ($date[0]*60*60)+($date[1]*60)+$date[2];
    $response = array(
        "variation" => 1,
        "subject" => $subject,
        "number_of_questions" => $question_count,
        "date" => $date,
        "points" => $points
    );
    if($relaunch==0){
        if(file_exists($file_name_change)){
            $file = fopen($file_name_change, "r");
            $data = fread($file, filesize($file_name_change));
            fclose($file);
            $data = json_decode($data, true);
            $data_new = array();
            $question_count = 0;
            foreach($data as $item){
                if($item['date'] != null){
                    array_push($data_new, $item);
                    $question_count++;
                }
            }
            $file = fopen($file_name_change, "w");
            fwrite($file, json_encode($data_new, JSON_UNESCAPED_UNICODE));
            fclose($file);
            $hours = floor($date / (60*60));
            $hours < 10 ? $hours = "0".$hours : $hours;
            $mins = floor(($date - ($hours*60*60)) / (60));
            $mins < 10 ? $mins = "0".$mins : $mins;
            $secs = floor(($date - (($hours*60*60)+($mins*60))));
            $secs < 10 ? $secs = "0".$secs : $secs;
            $date = $hours.":".$mins.":".$secs;
            $response = array(
                "variation" => 0,
                "subject" => $subject,
                "number_of_questions" => $question_count,
                "date" => $date,
                "points" => $points
            );
            echo json_encode($response);
        }else{
            add_log(
                $_SESSION['lang']['logs']['show_quiz']['title'],
                $_SESSION['lang']['logs']['show_quiz']['relaunch_decline_error'],
                $_SESSION['email'],
                "../logs/",
                $subject
            );
        }
    }else{
        echo json_encode($response);
        add_log(
            $_SESSION['lang']['logs']['show_quiz']['title'],
            $_SESSION['lang']['logs']['show_quiz']['relaunch_success'],
            $_SESSION['email'],
            "../logs/",
            $subject
        );
    }
}
?>