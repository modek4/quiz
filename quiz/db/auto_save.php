<?php
session_start();
if(!isset($_SESSION['code']) || !isset($_SESSION['last_answer_question'])){
    exit();
}
$code = $_SESSION['code'];
$last_answer_question = $_SESSION['last_answer_question'];
$file_href = "../analytic/".$code."_score.json";
if(file_exists($file_href)){
    $score = json_decode(file_get_contents($file_href), true);
    $found = true;
    $found_id = false;
    $last_answer_question_content = array();
    foreach($score as $item){
        if($item['date'] == null){
            $found = false;
        }
        if($item['id_question'] == $last_answer_question){
            $last_answer_question_content[0] = str_split(str_replace(';','',$item['correct_answers']));
            $last_answer_question_content[1] = str_split(str_replace(';','',$item['answers']));
            $found_id = true;
        }
        if($found == false && $found_id == true){
            break;
        }
    }
    if($found == true && $found_id == true){
        // Check if the count of elements in both arrays is equal or if there are differences
        if (count($last_answer_question_content[0]) == count($last_answer_question_content[1])) {
            echo "success";
            exit();
        }elseif(!empty(array_diff($last_answer_question_content[0], $last_answer_question_content[1]))){
            echo "success";
            exit();
        }
    }else{
        exit();
    }
}else{
    exit();
}
?>