<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['user_code'])){
        $user_code = $_POST['user_code'];
        if(isset($_POST['user_watch'])){
            function refresh($user_code, $conn){
                $file_name_live = "../../analytic/".$user_code."_score.json";
                $questions = [];
                $subject_name = "";
                if(file_exists($file_name_live)){
                    $json = json_decode(file_get_contents($file_name_live), true);
                    foreach($json as $record){
                        if($record['date'] != null){
                            array_push($questions, $record);
                        }
                        $subject_name = $record['subject'];
                    }
                    usort($questions, function($a, $b) {
                        return $b['date'] <=> $a['date'];
                    });
                }
                if(!empty($questions)){
                    $questions_ids = array_values(array_unique(array_column($questions, 'id_question')));
                    $questions_ids = implode(',', array_map('intval', $questions_ids));
                    $sql = "SELECT * FROM questions WHERE subject='".$subject_name."' AND id_question IN (".$questions_ids.")";
                    $result = $conn->query($sql);
                    $questions_map = [];
                    foreach ($questions as $question) {
                        $questions_map[$question['id_question']] = $question;
                    }
                    while($row = $result->fetch_assoc()){
                        $questionId = $row['id_question'];
                        if(isset($questions_map[$questionId])){
                            $questions_map[$questionId]['question'][] = $row;
                        }
                    }
                    $questions = array_values($questions_map);
                    foreach ($questions as $key => $question) {
                        $points = 0;
                        $user_answers = str_replace(';', '', $question['answers']);
                        $correct_answers = str_split(str_replace(';', '', $question['correct_answers']));
                        $correct_answers_length = count($correct_answers);
                        foreach ($correct_answers as $correct_answer) {
                            if(strpos($user_answers, $correct_answer) !== false){
                                $points += round(1/$correct_answers_length,2);
                            }
                        }
                        $questions[$key]['points'] = $points;
                    }
                }
                return [$questions, $subject_name];
            }
            echo "<div class='users_user_settings_content_watch_user'>";
                $questions = refresh($user_code, $conn);
                @$subject_name = strval($questions[1]);
                @$questions = $questions[0];
                $date_watch_user = new DateTime();
                $date_watch_user = $date_watch_user->format('H:i:s');
                if($subject_name != ""){
                    $all_questions = count($questions);
                    $correct_questions = 0;
                    foreach ($questions as $question) {
                        $correct_questions += $question['points'];
                    }
                    echo "<div class='users_user_settings_content_watch_user_header'>
                        <div class='watch_user_score'>
                            <i class='fa-solid fa-star-half-stroke'></i>
                            <span>".$correct_questions."/".$all_questions."</span>
                        </div>
                        <div class='watch_user_subject'>
                            <span>".$subject_name."</span>
                        </div>
                        <div class='reload_users_user' data-code='".$user_code."'>
                            <i class='fa-solid fa-arrow-rotate-right'></i>
                            <span>".$date_watch_user."</span>
                        </div>
                    </div>
                    <div class='users_user_settings_content_watch_user_content'>";
                    if(!empty($questions)){
                        foreach ($questions as $question) {
                            echo "<div class='users_user_settings_content_watch_user_content_item' onclick='watch_user_more(this)'>
                            <div class='users_user_info'>
                                <span>".$question['points']."/1</span>
                                <span>".$question['date']."</span>
                            </div>";
                            $question_text = $question['id_question'] .'. '.$question['question'][0]['question'];
                            $count = 0;
                            @$correct_answers_length = count(explode(";", $question['correct_answers']))-1;
                            $separator = '<br/>';
                            $pos = strpos($question_text, $separator);
                            if ($pos !== false) {
                                $part_1_question = substr($question_text, 0, $pos);
                                $part_2_question = substr($question_text, $pos + strlen($separator));
                                echo "<h4>" . $part_1_question . " (".@$correct_answers_length.")<br/>".$part_2_question."</h4>";
                            } else {
                              echo "<h4>" . $question_text."</h4>";
                            }
                            $answers = explode("♥", $question['question'][0]['answers']);
                            if(!empty($question['answers'])){
                              $incorrect_detected = false;
                              $answers_to_correct = explode("♥", $question['question'][0]['answers']);
                            }
                            foreach ($answers as $answersid){
                              $letter = chr(97 + $count);
                              if(!empty($question['answers'])){
                                if(strpos(str_replace(';','',$question['answers']), $letter) !== false){
                                  if(strpos(str_replace(';','',$question['correct_answers']), $letter) !== false){
                                    $answers[$count] = "<div class='radio checked correct' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                                  }else{
                                    $answers[$count] = "<div class='radio checked incorrect' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                                    $incorrect_detected = true;
                                  }
                                }else{
                                  $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                                }
                              }else{
                                $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                              }
                              $count++;
                            }
                            if(!empty($question['answers']) && $incorrect_detected == true){
                              $count = 0;
                              foreach ($answers_to_correct as $answersid_correct){
                                $letter = chr(97 + $count);
                                if(strpos(str_replace(';','',$question['correct_answers']), $letter) !== false){
                                  $answers[$count] = "<div class='radio correct' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid_correct . "</div><br>";
                                }
                                $count++;
                              }
                              $incorrect_detected = false;
                            }
                            shuffle($answers);
                            echo "<div class='watch_user_answers'>";
                            foreach ($answers as $answersid){
                              echo $answersid;
                            }
                            echo "</div></div>";
                        }
                    }
                }else{
                    echo "<div class='users_user_settings_content_watch_user_header'>
                    <div class='watch_user_score'>
                        <i class='fa-solid fa-star-half-stroke'></i>
                        <span>0/0</span>
                    </div>
                    <div class='watch_user_subject'>
                        <span>---</span>
                    </div>
                    <div class='reload_users_user' data-code='".$user_code."'>
                        <i class='fa-solid fa-rotate-right'></i>
                        <span>00:00:00</span>
                    </div>
                </div>";
                }
            echo "</div></div>
            <script>
            function watch_user_more(e){
                e.children[2].style.display = e.children[2].style.display == 'flex' ? 'none' : 'flex';
            }
            </script>";
        }else{
            $sql = "SELECT quiz_users.*, codes.term, codes.question_order, codes.question_analytic FROM codes LEFT JOIN quiz_users on codes.code=quiz_users.code WHERE codes.code='".$user_code."'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            echo "<div class='users_user_settings_content_left'>
                <div class='users_user_settings_content_left_score'>
                    <ul class='users_user_settings_content_left_score_content'>
                        <li class='header_score_table'>
                            <span class='header_score_table_subject' data-name='subject' data-id='asc'>".$_SESSION['lang']['admin']['users']['score']['subject']."</span>
                            <span class='header_score_table_score' data-name='score' data-id='asc'>".$_SESSION['lang']['admin']['users']['score']['score']."</span>
                            <span class='header_score_table_end_date' data-name='endDate' data-id='desc'>".$_SESSION['lang']['admin']['users']['score']['date']."</span>
                            <span class='header_score_table_time' data-name='time' data-id='asc'>".$_SESSION['lang']['admin']['users']['score']['time']."</span>
                        </li>";
                        $sql_score = "SELECT * FROM scores WHERE email='".$row['email']."' order by end_date desc";
                        if($result_score = $conn->query($sql_score)){
                            if($result_score->num_rows == 0){
                                echo "<li class='score_table'>
                                    <span class='empty'>------</span>
                                    <span class='empty'>------</span>
                                    <span class='empty'>------</span>
                                    <span class='empty'>------</span>
                                </li>";
                            }
                            foreach ($result_score as $row_score){
                                echo "<li class='score_table'>
                                    <span data-id='subject'>".$row_score['subject']."</span>
                                    <span data-id='score' data-name='".round(($row_score['score']/$row_score['question_count'])*100,2)."'>".$row_score['score']."/".$row_score['question_count']."(".round(($row_score['score']/$row_score['question_count'])*100,2)."%)</span>
                                    <span data-id='end_date'>".$row_score['end_date']."</span>
                                    <span data-id='total_time'>".$row_score['total_time']."</span>
                                </li>";
                            }
                            $result_score->free_result();
                        }
                    echo "</ul>
                </div>
                <div class='users_user_settings_content_left_analytic'>
                    <div class='users_user_settings_content_left_analytic_content'>
                        <div class='users_user_settings_content_left_analytic_content_chart' data-id='".$row['email']."'>
                            <canvas id='analytic_chart'></canvas>
                        </div>
                    </div>
                </div>
                <div class='users_user_settings_content_left_actions'>
                    <div class='user_email'>
                        <span>".$row['email']."(".$user_code.")</span>
                    </div>
                    <div class='user_buttons'>";
                        if($_SESSION['admin']==true){
                            echo "<span class='change_user_term_access' data-term='".$row['term']."' data-code='".$row['code']."'><i class='fa-solid fa-school-flag'></i></span>";
                            if($row['dark'] == -1){
                                echo "<span class='block_user_button unblock_user' data-id='".$row['code']."'>".$_SESSION['lang']['admin']['users']['unblock']['text']."</span>";
                            }else{
                                echo "<span class='block_user_button block_user' data-id='".$row['code']."'>".$_SESSION['lang']['admin']['users']['block']['text']."</span>";
                            }
                        }
                        echo "<span class='watch_user' data-code='".$row['code']."'><i class='fa-solid fa-eye'></i></span>";
                    echo "</div>
                </div>
            </div>";
            $sql_devices = "SELECT * FROM devices WHERE email='".$row['email']."' order by udevices,last_login desc";
            if($result_devices = $conn->query($sql_devices)){
                $devices = [];
                $last_login_actual = 0;
                foreach ($result_devices as $row_devices) {
                    $device_name = substr($row_devices['udevices'], 0, strpos($row_devices['udevices'], '|') - 1);
                    $ip_address = substr($row_devices['udevices'], strpos($row_devices['udevices'], '|') + 2);
                    $last_login = $row_devices['last_login'];
                    if($last_login_actual < $last_login){
                        $last_login_actual = $last_login;
                    }
                    if (!isset($devices[$device_name])) {
                        $devices[$device_name] = [];
                    }
                    $devices[$device_name][$ip_address] = $last_login;
                }
                foreach ($devices as $device_name => $ip_address) {
                    arsort($devices[$device_name]);
                }
            echo "<div class='users_user_settings_content_right'>
                <div class='users_user_settings_content_right_content'>
                    <ul class='devices_list'>
                        <li class='devices_list_all'>
                            <p>".$_SESSION['lang']['admin']['users']['devices_text']."<br/><i class='fa-solid fa-display'></i> <span class='device_count'>0</span> <i class='fa-solid fa-location-arrow'></i> <span class='address_count'>0</span></p>
                            <button class='devices_list_button' data-id='".$row['email']."'>".$_SESSION['lang']['admin']['users']['reset']['all_devices']['title']."</button>
                        </li>";
                        foreach ($devices as $device_name => $ip_address) {
                            echo "<li class='device'>".$device_name;
                            echo "<ul class='addresses'>";
                            foreach ($ip_address as $ip_address => $last_login) {
                                echo "<li><div>";
                                if($last_login == $last_login_actual){
                                    echo "<p class='active'>".$ip_address."</p><p>".$last_login."</p>";
                                }else{
                                    echo "<p>".$ip_address."</p><p>".$last_login."</p>";
                                }
                                echo "
                                </div>
                                    <i data-id='".$row['email']."' data-name='".$device_name." | ".$ip_address."' class='fa-sharp fa-solid fa-trash-alt remove_device_button'></i>
                                </li>";
                            }
                            echo "</ul>
                            </li>";
                        }
                        echo "
                    </ul>
                </div>
            </div>";
                $result_devices->free_result();
            }
        }
    }
    $conn->close();
}