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
  if (isset($_POST['subject']) && $_POST['subject'] != '') {
    $subject = $_POST['subject'];
    $_SESSION['subject'] = $subject;
    $date = date('Y-m-d H:i:s');
    if($_POST['number_of_questions'] == ''){
      $number_of_questions = 0;
    } else {
      $number_of_questions = $_POST['number_of_questions'];
      if($_POST['number_of_questions'] == 1){
        $one_to_one = true;
        $number_of_questions=0;
      }else{
        $one_to_one = false;
      }
    }
    //count questions
    $sql_count = "SELECT COUNT(*) FROM questions WHERE subject = '$subject'";
    $result_count = mysqli_query($conn, $sql_count);
    $count = mysqli_fetch_array($result_count)[0];
    // $num_questions = $sql_count; => all questions
    if($number_of_questions == 0){
      $num_questions = $sql_count;
    } else {
      $num_questions = $number_of_questions;
    }
    //$num_questions = $sql_count; // questions number
    if ($num_questions > $count){
        $num_questions = $count;
    }
    //json file check
    $search_pattern_file = "../analytic/";
    $matching_files = scandir($search_pattern_file);
    array_splice($matching_files, 0, 2);
    $matching_files = preg_grep("/".$_SESSION['code']."-/", $matching_files);
    $update_analytic = true;
    foreach ($matching_files as $file) {
      $subjectToSave = substr($file, strpos($file, "-") + 1);
      $subjectToSave = substr($subjectToSave, 0, -5);
      $file_name = "../analytic/".$file;
      $file_read = fopen($file_name, "r");
      $data = fread($file_read, filesize($file_name));
      fclose($file_read);
      $sql_analytics = "UPDATE analytics SET analytic = '$data' WHERE code = '".$_SESSION['code']."' AND subject = '$subjectToSave'";
      if(!mysqli_query($conn, $sql_analytics)){
        $update_analytic = false;
      }else{
        unlink("../analytic/".$file);
      }
    }
    if($update_analytic){
      //json file with questions and points
      $sql_check_code = "SELECT * FROM analytics WHERE code = '".$_SESSION['code']."' AND subject = '$subject'";
      $result_check_code = mysqli_query($conn, $sql_check_code);
      $count_check_code = mysqli_num_rows($result_check_code);
      if($count_check_code == 0){
        $analytic = array();
        for($i=0; $i<$count; $i++){
          array_push($analytic, array(
            "id" => $i+1,
            "correct" => 0,
            "incorrect" => 0,
            "halfcorrect" => 0,
            "checked" => 0,
            "maxchecked" => 0,
            "count" => 0
          ));
        }
        $data = json_encode($analytic, JSON_UNESCAPED_UNICODE);
        $sql_analytics = "INSERT INTO analytics (code, subject, analytic) VALUES ('".$_SESSION['code']."', '$subject', '$data')";
        $result_analytics = mysqli_query($conn, $sql_analytics);
        $data = json_decode($data, true);
      }else{
        $row_analytics = mysqli_fetch_assoc($result_check_code);
        $data = json_decode($row_analytics['analytic'], true);
      }
      $file_name = "../analytic/".$_SESSION['code']."-".$subject.".json";
      $file = fopen($file_name, "w");
      fwrite($file, json_encode($data, JSON_UNESCAPED_UNICODE));
      fclose($file);
      //get random questions
      $question_ids = array();
      if($_SESSION['question_order']==0){
        if($_SESSION['question_analytic'] == 1){
          //calcutale points
          if(file_exists($file_name)){
            $points = array();
            $file = fopen($file_name, "r");
            $data = fread($file, filesize($file_name));
            fclose($file);
            $data = json_decode($data, true);
            $sql_points = "SELECT * FROM quiz_admin";
            if($result_points = mysqli_query($conn, $sql_points)){
              $row_points = mysqli_fetch_assoc($result_points);
              $points_correct = $row_points['points_correct'];
              $points_incorrect = $row_points['points_incorrect'];
              $points_halfcorrect = $row_points['points_halfcorrect'];
              foreach ($data as $id){
                $id_analytic = $id['id'];
                $correct_analytic = $id['correct'];
                $incorrect_analytic = $id['incorrect'];
                $halfcorrect_analytic = $id['halfcorrect'];
                $checked_analytic = $id['checked'];
                $max_checked_analytic = $id['maxchecked'];
                $count_analytic = $id['count'];
                if($count_analytic == 0){
                  $points_analytic = $points_incorrect;
                }else{
                  //points system
                  require_once("./points_system.php");
                  $points_analytic = calculate_points($points_correct, $points_incorrect, $points_halfcorrect, $correct_analytic, $incorrect_analytic, $halfcorrect_analytic, $checked_analytic, $max_checked_analytic, $count_analytic);
                }
                array_push($points, array($id_analytic, $points_analytic));
              }
              $question_ids_correct = array();
              $question_ids_incorrect = array();
              $question_ids_halfcorrect = array();
              foreach ($points as $point){
                if($point[1] < ($points_correct/$points_incorrect)){
                  array_push($question_ids_correct, $point);
                } else if($point[1] > $points_incorrect + ($points_correct/$points_incorrect)){
                  array_push($question_ids_incorrect, $point);
                } else {
                  array_push($question_ids_halfcorrect, $point);
                }
              }
              usort($question_ids_correct, function($a, $b) {
                return $b[1] <=> $a[1];
              });
              usort($question_ids_incorrect, function($a, $b) {
                return $b[1] <=> $a[1];
              });
              usort($question_ids_halfcorrect, function($a, $b) {
                return $b[1] <=> $a[1];
              });
              //count questions to show header
              $question_ids_correct_length = count($question_ids_correct);
              $question_ids_incorrect_length = count($question_ids_incorrect);
              $question_ids_halfcorrect_length = count($question_ids_halfcorrect);
              //count round questions
              $question_ids_correct_length_slice = round($question_ids_correct_length / ($count / $num_questions));
              $question_ids_incorrect_length_slice = round($question_ids_incorrect_length / ($count / $num_questions));
              $question_ids_halfcorrect_length_slice = round($question_ids_halfcorrect_length / ($count / $num_questions));
              $question_ids_sum_length_slice = $question_ids_correct_length_slice + $question_ids_incorrect_length_slice + $question_ids_halfcorrect_length_slice;
              if($question_ids_sum_length_slice > $num_questions){
                $question_ids_sum_length_slice_dif = $question_ids_sum_length_slice - $num_questions;
                if($question_ids_correct_length_slice > 0){
                  $question_ids_correct_length_slice-=$question_ids_sum_length_slice_dif;
                }else if ($question_ids_incorrect_length_slice > 0){
                  $question_ids_halfcorrect_length_slice-=$question_ids_sum_length_slice_dif;
                } else {
                  $question_ids_incorrect_length_slice-=$question_ids_sum_length_slice_dif;
                }
              }
              //slice main arrays
              $question_ids_correct = array_slice($question_ids_correct, 0, $question_ids_correct_length_slice);
              $question_ids_incorrect = array_slice($question_ids_incorrect, 0, $question_ids_incorrect_length_slice);
              $question_ids_halfcorrect = array_slice($question_ids_halfcorrect, 0, $question_ids_halfcorrect_length_slice);
              //shuffle arrays and merge
              shuffle($question_ids_correct);
              shuffle($question_ids_incorrect);
              shuffle($question_ids_halfcorrect);
              foreach ($question_ids_correct as $key => $val){
                $question_ids_correct[$key] = $val[0];
              }
              foreach ($question_ids_incorrect as $key => $val){
                  $question_ids_incorrect[$key] = $val[0];
              }
              foreach ($question_ids_halfcorrect as $key => $val){
                  $question_ids_halfcorrect[$key] = $val[0];
              }
              $question_ids = array_merge($question_ids_incorrect, $question_ids_halfcorrect, $question_ids_correct);
              $question_ids = array_slice($question_ids, 0, $num_questions);
            }else{
              add_log(
                $_SESSION['lang']['logs']['show_quiz']['title'],
                $_SESSION['lang']['logs']['show_quiz']['no_analytic_points'],
                $_SESSION['email'],
                "../logs/",
                $subject
              );
            }
          }else{
            add_log(
              $_SESSION['lang']['logs']['show_quiz']['title'],
              $_SESSION['lang']['logs']['show_quiz']['no_analytic_file'],
              $_SESSION['email'],
              "../logs/",
              $subject
            );
          }
        }else{
          while (count($question_ids) < $num_questions) {
            $random_id = rand(1, $count);
            if (!in_array($random_id, $question_ids)) {
                array_push($question_ids, $random_id);
            }
          }
        }
      }else{
        for($i=1; $i<=$num_questions; $i++){
          array_push($question_ids, $i);
        }
      }
      $analytic_score = array();
      //get questions
      $questions = array();
      $iter = 0;
      foreach ($question_ids as $id) {
          $sql_question = "SELECT * FROM questions WHERE subject = '$subject' AND id_question = $id";
          $result_question = mysqli_query($conn, $sql_question);
          $question = mysqli_fetch_assoc($result_question);
          array_push($questions, $question);
          array_push($analytic_score, array(
            "id" => $iter+1,
            "subject" => $subject,
            "id_question" => $id,
            "answers" => 0,
            "correct_answers" => 0
          ));
          $iter++;
      }
      $file_name_score = "../analytic/".$_SESSION['code']."_score.json";
      @unlink($file_name_score);
      $file_score = fopen($file_name_score, "w");
      fwrite($file_score, json_encode($analytic_score, JSON_UNESCAPED_UNICODE));
      fclose($file_score);
      //count loaded quiz
      $sql_count_subject = "SELECT loaded FROM subjects WHERE subject = '$subject'";
      $result_count_subject = mysqli_query($conn, $sql_count_subject);
      $row_result_count_subject = mysqli_fetch_assoc($result_count_subject);
      if ($row_result_count_subject && !empty($row_result_count_subject['loaded'])) {
        $loaded = json_decode($row_result_count_subject['loaded'], true);
      } else {
        $loaded = [];
      }
      $loaded_current_date = date('Y-m-d');
      if (array_key_exists($loaded_current_date, $loaded)) {
        $loaded[$loaded_current_date] += 1;
      } else {
        $loaded[$loaded_current_date] = 1;
      }
      $encodedLoaded = json_encode($loaded, JSON_UNESCAPED_UNICODE);
      $sql_update_loaded = "UPDATE subjects SET loaded = '$encodedLoaded' WHERE subject = '$subject'";
      if(mysqli_query($conn, $sql_update_loaded)){
        //load quiz
        echo "<div class='quiz'>
        <div id='lightbox'>
          <span class='close_lightbox' onclick='closeLightbox()'>&times;</span>
          <img src=''/>
        </div>
        ";
        if($_SESSION['question_order']==0){
          if($_SESSION['question_analytic'] == 1){
            echo "<div class='questions_stats'>
            <p class='number_of_all_questions'>".$_SESSION['lang']['quiz']['select_quiz']['analytic_title']." (".round(($question_ids_correct_length+($question_ids_halfcorrect_length/2))/($question_ids_correct_length+$question_ids_incorrect_length+$question_ids_halfcorrect_length)*100,2)."%)</p>
            <ul class='quiz_header_stats'>
              <li>".$_SESSION['lang']['quiz']['select_quiz']['correct']."<br/><span>".$question_ids_correct_length."</span></li>
              <li>".$_SESSION['lang']['quiz']['select_quiz']['incorrect']."<br/><span>".$question_ids_incorrect_length."</span></li>
              <li>".$_SESSION['lang']['quiz']['select_quiz']['halfcorrect']."<br/><span>".$question_ids_halfcorrect_length."</span></li>
            </ul>
          </div>
          ";
          }
        }
        echo "<div id='quiz_results'>";
        $number_of_all_questions=1;
        if($one_to_one){
          foreach ($questions as $question) {
            if($number_of_all_questions==1){
              echo "<div class='background_question' data-id='".$question['id_question']."'>";
            }else{
              echo "<div class='background_question hidden_question' data-id='".$question['id_question']."'>";
            }
            echo "<p class='number_of_all_questions'>$number_of_all_questions</p>";
            $number_of_all_questions++;
            $question_text = $question['id_question'] .'. '.$question['question'];
            $count = 0;
            @$correct_answers_length = count(explode(";", $question['correct_answers']))-1;
            $separator = '<br/>';
            $pos = strpos($question_text, $separator);
            if ($pos !== false) {
                $part_1_question = substr($question_text, 0, $pos);
                $part_2_question = substr($question_text, $pos + strlen($separator));
                echo "<h4>" . $part_1_question . " (".@$correct_answers_length.")<br/>".$part_2_question."</h4>";
            } else {
              echo "<h4>" . $question_text." (".@$correct_answers_length.")</h4>";
            }
            $answers = explode("♥", $question['answers']);
            foreach ($answers as $answersid){
              $letter = chr(97 + $count);
              $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
              $count++;
            }
            shuffle($answers);
            foreach ($answers as $answersid){
              echo $answersid;
            }
            echo "<p class='report_question' data-id='".$question['id_question']."' subject='".$question['subject']."'>".$_SESSION['lang']['quiz']['report']['title_send']."</p>";
            echo "</div>";
          }
          add_log(
            $_SESSION['lang']['logs']['show_quiz']['title'],
            $_SESSION['lang']['logs']['show_quiz']['one_to_one_success'],
            $_SESSION['email'],
            "../logs/",
            array(
              "subject" => $subject,
              "num_questions" => $num_questions
            )
          );
        }else{
          foreach ($questions as $question) {
            echo "<div class='background_question' data-id='".$question['id_question']."'>";
            echo "<p class='number_of_all_questions'>$number_of_all_questions / $num_questions</p>";
            $number_of_all_questions++;
            $question_text = $question['id_question'] .'. '.$question['question'];
            $count = 0;
            @$correct_answers_length = count(explode(";", $question['correct_answers']))-1;
            $separator = '<br/>';
            $pos = strpos($question_text, $separator);
            if ($pos !== false) {
                $part_1_question = substr($question_text, 0, $pos);
                $part_2_question = substr($question_text, $pos + strlen($separator));
                echo "<h4>" . $part_1_question . " (".@$correct_answers_length.")<br/>".$part_2_question."</h4>";
            } else {
              echo "<h4>" . $question_text." (".@$correct_answers_length.")</h4>";
            }
            $answers = explode("♥", $question['answers']);
            foreach ($answers as $answersid){
              $letter = chr(97 + $count);
              $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
              $count++;
            }
            shuffle($answers);
            foreach ($answers as $answersid){
              echo $answersid;
            }
            echo "<p class='report_question' data-id='".$question['id_question']."' subject='".$question['subject']."'>".$_SESSION['lang']['quiz']['report']['title_send']."</p>";
            echo "</div>";
          }
          add_log(
            $_SESSION['lang']['logs']['show_quiz']['title'],
            $_SESSION['lang']['logs']['show_quiz']['success'],
            $_SESSION['email'],
            "../logs/",
            array(
              "subject" => $subject,
              "num_questions" => $num_questions
            )
          );
        }
        echo "</div></div>";
      }else{
        add_log(
          $_SESSION['lang']['logs']['show_quiz']['title'],
          $_SESSION['lang']['logs']['show_quiz']['error'],
          $_SESSION['email'],
          "../logs/"
        );
      }
    }else{
      add_log(
        $_SESSION['lang']['logs']['show_quiz']['title'],
        $_SESSION['lang']['logs']['show_quiz']['analytic'],
        $_SESSION['email'],
        "../logs/"
      );
    }
  }
    echo "<script> var subject='".@$subject."' </script>";
    echo "<script> var question_count='".@$num_questions."' </script>";
    echo "<script> var quiz_start='".@$date."' </script>";
    echo "<script>
    //lightbox
    $('#lightbox').click(function() {
      closeLightbox();
    });
    function open_lightbox(image) {
      var lightbox = document.getElementById('lightbox');
      var img = lightbox.getElementsByTagName('img')[0];
      img.src = image.src;
      lightbox.style.display = 'block';
      body.classList.add('no-scroll');
    }
    function closeLightbox() {
      var lightbox = document.getElementById('lightbox');
      lightbox.style.display = 'none';
      body.classList.remove('no-scroll');
    }
    //timer
    var start = new Date().getTime();
    var timer = setInterval(function() {
      var now = new Date().getTime();
      var distance = now - start;
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      if (hours < 10) {
        hours = '0' + hours;
      }
      if (minutes < 10) {
        minutes = '0' + minutes;
      }
      if (seconds < 10) {
        seconds = '0' + seconds;
      }
      $('#quiz_timer').text(hours + ':' + minutes + ':' + seconds);
    }, 1000);
    $('#save_score').click(function() {
      clearInterval(timer);
    });
    //report
    var report_buttons = document.querySelectorAll('.report_question');
    var answers = [];
    report_buttons.forEach(function(button) {
      button.addEventListener('click', function() {
        var parent = button.parentNode;
        if (parent.classList.contains('report_question_active')) {
          parent.classList.remove('report_question_active');
          parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_send'];
          if (parent.querySelector('h3')) {
            parent.querySelector('h3').remove();
          }
          var all_checked = parent.querySelectorAll('.radio');
          all_checked.forEach(function(checked, index) {
            if(answers[index].status == 1) {
              checked.classList.add('checked');
            } else if(answers[index].status == 2){
              checked.classList.add('correct');
            }else if(answers[index].status == 3){
              checked.classList.add('incorrect');
            }else if(answers[index].status == 4){
              checked.classList.add('correct');
              checked.classList.add('checked');
            }else if(answers[index].status == 5){
              checked.classList.add('incorrect');
              checked.classList.add('checked');
            }else{
              checked.classList.remove('checked');
              checked.classList.remove('correct');
              checked.classList.remove('incorrect');
              checked.classList.remove('checked_report');
            }
          });
        } else {
          var all_checked = parent.querySelectorAll('.radio');
          answers = [];
          all_checked.forEach(function(checked) {
            var answer = {
              status: 0
            };
            if (checked.classList.contains('checked')) {
              answer.status = 1;
              if (checked.classList.contains('correct')) {
                answer.status += 3;
              } else if (checked.classList.contains('incorrect')) {
                answer.status += 4;
              }
            }else{
              if (checked.classList.contains('correct')) {
                answer.status = 2;
              } else if (checked.classList.contains('incorrect')) {
                answer.status = 3;
              }
            }
            answers.push(answer);
            checked.classList.remove('checked');
            checked.classList.remove('correct');
            checked.classList.remove('incorrect');
            checked.classList.remove('checked_report');
          });
          parent.classList.add('report_question_active');
          parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_decline'];
          parent.querySelector('h4').innerHTML += '<h3>'+lang_text['quiz']['report']['text']+'<br/><button class=\"send_report_button\">'+lang_text['quiz']['report']['button']+'</button></h3>';
          parent.querySelectorAll('.radio').forEach(function(checked) {
            checked.addEventListener('click', function() {
              checked.classList.add('checked_report');
            });
          });
        }
        var sendReportButton = parent.querySelector('.send_report_button');
        if(sendReportButton) {
          var all_checked = parent.querySelectorAll('.radio');
          sendReportButton.addEventListener('click', function() {
            var ListOfChecked = [];
            var SubjectNameReport = '';
            var QuestionIdReport = '';
            all_checked.forEach(function (checked){
              if(checked.classList.contains('checked_report')){
                ListOfChecked.push(checked.getAttribute('value'));
              }
              SubjectNameReport = checked.getAttribute('subject');
              QuestionIdReport = checked.getAttribute('name');
            });
            ListOfChecked.sort();
            var ListOfCheckedReport = ListOfChecked.join();
            QuestionIdReport = QuestionIdReport.substring(9);
            if(QuestionIdReport == '' || SubjectNameReport == '') {
              return false;
            }
            if(ListOfCheckedReport == '') {
              ListOfCheckedReport = 0;
            }
            $.ajax({
              type: 'POST',
              url: 'db/report_question.php',
              data: {selected_correct: ListOfCheckedReport, subject: SubjectNameReport, report_id: QuestionIdReport},
              success: function(response) {
                notifyshow(response, '');
                parent.classList.remove('report_question_active');
                parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_send'];
                if (parent.querySelector('h3')) {
                  parent.querySelector('h3').remove();
                }
                var all_checked = parent.querySelectorAll('.radio');
                all_checked.forEach(function(checked, index) {
                  if(answers[index].status == 1) {
                    checked.classList.add('checked');
                  } else if(answers[index].status == 2){
                    checked.classList.add('correct');
                  }else if(answers[index].status == 3){
                    checked.classList.add('incorrect');
                  }else if(answers[index].status == 4){
                    checked.classList.add('correct');
                    checked.classList.add('checked');
                  }else if(answers[index].status == 5){
                    checked.classList.add('incorrect');
                    checked.classList.add('checked');
                  }
                });
                $.ajax({
                  type: 'POST',
                  url: 'db/notifications.php',
                  data: {id: 'bellreload'},
                  success: function(response) {
                    $('.notification_bell').html(response);
                  },
                  error: function(xhr, status, error) {
                      add_log('index: reload bell after report question', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                      notifyshow(status+' ('+xhr.status+'): '+error, '');
                  }
                });
              },
              error: function(xhr, status, error) {
                  add_log('index: report question', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                  notifyshow(status+' ('+xhr.status+'): '+error, '');
              }
            });
          });
        }
      });
    });
    </script>";
    $conn->close();
}
?>