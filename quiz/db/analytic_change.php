<?php
session_start();
//change analytic
if(isset($_POST['analytic'])){
  $analytic = $_POST['analytic'];
  foreach ($analytic as $item) {
    $key = $item[0];
    $value = $item[1];
    switch ($key) {
      case 'subject':
        $subject = $value;
        break;
      case 'id_question':
        $id_question = $value;
        break;
      case 'checked':
        $checked = $value;
        break;
      case 'correct':
        $correct = $value;
        break;
      case 'incorrect':
        $incorrect = $value;
        break;
      case 'maxchecked':
        $maxchecked = $value;
        break;
    }
  }
  $code = $_SESSION['code'];

  $file_name_change = "../analytic/".$code."-".$subject.".json";
  if(file_exists($file_name_change)){
    $file = fopen($file_name_change, "r");
    $data = fread($file, filesize($file_name_change));
    fclose($file);
    $data = json_decode($data, true);

    $data[$id_question-1]['correct'] += $correct;
    $data[$id_question-1]['incorrect'] += $incorrect;
    if($incorrect == 1){
        $data[$id_question-1]['halfcorrect'] += round($correct/$maxchecked, 2);
    }
    $data[$id_question-1]['checked'] += $checked;
    $data[$id_question-1]['maxchecked'] += $maxchecked;
    $data[$id_question-1]['count']++;

    $file = fopen($file_name_change, "w");
    fwrite($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    fclose($file);
  }
//change score
}else if(isset($_POST['selected_answers'])){
  $selected_answers = $_POST['selected_answers'];
  foreach ($selected_answers as $item) {
    $key = $item[0];
    $value = $item[1];
    switch ($key) {
      case 'subject':
        $subject = $value;
        break;
      case 'id_question':
        $id_question = $value;
        break;
      case 'answers':
        $answers = $value;
        break;
      case 'correct_answers':
        $correct_answers = $value;
        break;
      case 'id':
        $answerId = $value;
        break;
    }
  }
  $code = $_SESSION['code'];

  $file_name_change = "../analytic/".$code."_score.json";
  if(file_exists($file_name_change)){
    $file = fopen($file_name_change, "r");
    $data = fread($file, filesize($file_name_change));
    fclose($file);
    $data = json_decode($data, true);

    $data[$answerId-1]['subject'] = $subject;
    $data[$answerId-1]['id_question'] = $id_question;
    $data[$answerId-1]['answers'] = $answers;
    $data[$answerId-1]['correct_answers'] = $correct_answers;

    $file = fopen($file_name_change, "w");
    fwrite($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    fclose($file);
  }
}
?>