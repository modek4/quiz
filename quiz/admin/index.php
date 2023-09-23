<?php
session_start();
if($_SESSION['admin'] == false){
    if($_SESSION['mod'] == false){
        header("Location: ../");
        exit();
    }
}
try {
    @require_once("../db/connect.php");
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        throw new Exception($_SESSION['lang']['database']['error']);
    }
    $conn->set_charset("utf8");
    function update_users_status($conn){
        $file_href = "../active.json";
        if(!file_exists($file_href)){
            $active = array();
            $fp = fopen($file_href, 'w');
            fwrite($fp, json_encode($active, JSON_PRETTY_PRINT));
            fclose($fp);
        }else{
            $active = json_decode(file_get_contents($file_href), true);
            $actual_date = date('Y-m-d H:i:s', time() - 60);
            foreach($active as $key => $value){
                if($value['time'] >= $actual_date){
                    $active[$key]['status'] = "online";
                    $active[$key]['time'] = date('Y-m-d H:i:s', time());
                }else{
                    $active[$key]['status'] = "offline";
                }
                $sql = "UPDATE quiz_users SET status = '".$active[$key]['status']."', last_login = '".$active[$key]['time']."' WHERE email = '".$active[$key]['email']."'";
                if(!mysqli_query($conn, $sql)){
                    throw new Exception($_SESSION['lang']['database']['error']);
                }
            }
            $fp = fopen($file_href, 'w');
            fwrite($fp, json_encode($active, JSON_PRETTY_PRINT));
            fclose($fp);
        }
    }
    update_users_status($conn);
    echo "<div class='navbar'>";
    if(isset($_POST['navbar'])){
        $sql_count_reports = "SELECT count(*) as count FROM reports";
        if(mysqli_query($conn, $sql_count_reports)){
            if(mysqli_fetch_assoc(mysqli_query($conn, $sql_count_reports))['count']>0){
                $reports_status = 'active_reports';
            }else{
                $reports_status = '';
            }
        }
        if($_POST['navbar']=="report"){
                echo "<i class='fa-solid fa-house' data-id='main'></i>
                <i class='fa-solid fa-question active ".$reports_status."' data-id='report'></i>";
                if($_SESSION['admin']){
                    echo "<i class='fa-solid fa-lock-open' data-id='menage'></i>";
                }
                echo "<i class='fa-solid fa-users' data-id='users'></i>";
                echo "<i class='fa-regular fa-folder-open' data-id='logs'></i>";
        }else if($_POST['navbar']=="menage"){
                echo "<i class='fa-solid fa-house' data-id='main'></i>
                <i class='fa-solid fa-question ".$reports_status."' data-id='report'></i>";
                if($_SESSION['admin']){
                    echo "<i class='fa-solid fa-lock-open active' data-id='menage'></i>";
                }
                echo "<i class='fa-solid fa-users' data-id='users'></i>";
                echo "<i class='fa-regular fa-folder-open' data-id='logs'></i>";
        }else if($_POST['navbar']=="users"){
                echo "<i class='fa-solid fa-house' data-id='main'></i>
                <i class='fa-solid fa-question ".$reports_status."' data-id='report'></i>";
                if($_SESSION['admin']){
                    echo "<i class='fa-solid fa-lock-open' data-id='menage'></i>";
                }
                echo "<i class='fa-solid fa-users active' data-id='users'></i>";
                echo "<i class='fa-regular fa-folder-open' data-id='logs'></i>";
        } else if($_POST['navbar']=="main"){
                echo "<i class='fa-solid fa-house active' data-id='main'></i>
                <i class='fa-solid fa-question ".$reports_status."' data-id='report'></i>";
                if($_SESSION['admin']){
                    echo "<i class='fa-solid fa-lock-open' data-id='menage'></i>";
                }
                echo "<i class='fa-solid fa-users' data-id='users'></i>";
                echo "<i class='fa-regular fa-folder-open' data-id='logs'></i>";
        } else if($_POST['navbar']=="logs"){
            echo "<i class='fa-solid fa-house' data-id='main'></i>
            <i class='fa-solid fa-question ".$reports_status."' data-id='report'></i>";
            if($_SESSION['admin']){
                echo "<i class='fa-solid fa-lock-open' data-id='menage'></i>";
            }
            echo "<i class='fa-solid fa-users' data-id='users'></i>";
            echo "<i class='fa-regular fa-folder-open active' data-id='logs'></i>";
        }
    }else{
            echo "<i class='fa-solid fa-house active' data-id='main'></i>
            <i class='fa-solid fa-question ".$reports_status."' data-id='report'></i>";
            if($_SESSION['admin']){
                echo "<i class='fa-solid fa-lock-open' data-id='menage'></i>";
            }
            echo "<i class='fa-solid fa-users' data-id='users'></i>";
            echo "<i class='fa-regular fa-folder-open' data-id='logs'></i>";
    }
    echo "</div>";
    if(isset($_POST['content'])){
        if($_POST['content']=='main'){
            echo "<div class='main'>
                    <div class='main_left'>
                        <div class='main_left_graph'>
                            <div class='main_left_graph_content'>
                                <select class='select_latest_term'>";
                                    $sql = "SELECT distinct term FROM subjects ORDER BY term DESC";
                                    if($result = mysqli_query($conn, $sql)){
                                        while($row = mysqli_fetch_assoc($result)){
                                            echo "<option value='".$row['term']."'>".$_SESSION['lang']['admin']['main']['chart']['term'].": ".$row['term']."</option>";
                                        }
                                    }
                                echo "</select>
                                <select class='select_latest_data'>
                                    <option value='7'>7 ".$_SESSION['lang']['admin']['main']['chart']['text']."</option>
                                    <option value='14'>14 ".$_SESSION['lang']['admin']['main']['chart']['text']."</option>
                                    <option value='30'>30 ".$_SESSION['lang']['admin']['main']['chart']['text']."</option>
                                    <option value='90'>90 ".$_SESSION['lang']['admin']['main']['chart']['text']."</option>
                                    <option value='all'>".$_SESSION['lang']['admin']['main']['chart']['all']."</option>
                                </select>
                                <div class='main_left_graph_content_chart'>
                                    <canvas id='main_analytic_chart'></canvas>
                                </div>
                            </div>
                        </div>
                        <div class='main_left_subjects'>
                            <div class='main_left_subjects_content'></div>
                        </div>
                    </div>
                    <div class='main_right'></div>
                </div>";
        }else if($_POST['content']=='report'){
            $lang_text = $_SESSION['lang']['admin']['reports'];
            $sql = "SELECT * FROM reports LEFT JOIN questions on (reports.question_id=questions.id_question AND reports.subject=questions.subject) order by report_date desc";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result)>0){
                echo "<div class='report'>
                <div class='report_content'>";
                while($row = mysqli_fetch_assoc($result)){
                    $date = explode("-", explode(" ", $row['report_date'])[0])[2]."/".explode("-", explode(" ", $row['report_date'])[0])[1]." ".explode(":", explode(" ", $row['report_date'])[1])[0].":".explode(":", explode(" ", $row['report_date'])[1])[1];
                    echo "<div class='report_content_item' id='report_item-".$row['id_question']."'>
                        <i class='fa-solid fa-rotate-right' data-id='".$row['id_question']."' data-name='".$row['subject']."'></i>
                        <i class='fa-solid fa-copy' data-id='".$row['id_question']."' data-name='".$row['subject']."'></i>
                        <h4>".$row['subject']." - ".$row['id_question']."</h4>";
                        $question = $row['question'];
                        $question = str_replace(
                            ["<br/>","<code><pre>","</pre></code>"],
                            ["\n","```\n","```"],
                        $question);
                        echo "<h5><textarea>".$question."</textarea></h5>";
                        $anwsers = explode("♥", $row['answers']);
                        $correct_anwsers = explode(";", $row['correct_answers']);
                        $user_anwsers = explode(",", $row['user_answers']);
                        $letter = "a";
                        echo "<div class='answers' data-id='question-".$row['id_question']."'>";
                        foreach($anwsers as $anwser){
                            $find_correct = false;
                            $find_user = false;
                            foreach($correct_anwsers as $correct_anwser){
                                if($correct_anwser==$letter){
                                    $find_correct = true;
                                }
                            }
                            foreach($user_anwsers as $user_anwser){
                                if($user_anwser==$letter){
                                    $find_user = true;
                                }
                            }
                            if($find_correct && $find_user){
                                echo "<div class='answer'>
                                    <span class='active'>•</span>
                                    <textarea type='text' class='correct_user_answer' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                                    <i class='fa-sharp fa-solid fa-trash-alt'></i>
                                </div>";
                            }else if($find_user){
                                echo "<div class='answer'>
                                    <span>•</span>
                                    <textarea type='text' class='user_answer' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                                    <i class='fa-sharp fa-solid fa-trash-alt'></i>
                                </div>";
                            } else if($find_correct){
                                echo "<div class='answer'>
                                    <span class='active'>•</span>
                                    <textarea type='text' class='correct_answer' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                                    <i class='fa-sharp fa-solid fa-trash-alt'></i>
                                </div>";
                            } else{
                                echo "<div class='answer'>
                                    <span>•</span>
                                    <textarea type='text' class='no_checked' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                                    <i class='fa-sharp fa-solid fa-trash-alt'></i>
                                </div>";
                            }
                            $letter++;
                        }
                        $letter = "a";
                        echo "
                        </div>
                        <i class='fa-sharp fa-solid fa-plus'></i>
                        <div class='report_content_item_buttons'>
                            <button class='report_content_item_update' data-id='".$row['id_question']."'>".$lang_text['update']['button']."</button>
                            <button class='report_content_item_decline' data-id='".$row['id_question']."'>".$lang_text['decline']['button']."</button>
                            <button class='report_content_item_remove' data-id='".$row['id_question']."'>".$lang_text['remove']['button']."</button>
                        </div>
                        <div class='report_content_item_info'>
                            <span>".$row['email']."</span>
                            <span>".$date."</span>
                        </div>
                    </div>";
                }
                echo "</div>
            </div>";
            }else{
                echo "<div class='report'>
                <div class='report_content'>
                    <div class='report_content_item'>
                        <h4>".$lang_text['no_reports']."</h4>
                    </div>
                </div>
            </div>";
            }
        }else if($_POST['content']=='menage'){
            $lang_text = $_SESSION['lang']['admin']['menage'];
            echo "<div class='menage'>
                <div class='menage_main'>
                    <div class='menage_main_add'>
                        <div class='menage_main_add_content'>
                            <form id='quiz_add_form' enctype='multipart/form-data'>
                            <div class='menage_main_add_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_add']['title']."</button>
                            </div>
                            <div class='menage_main_add_content_item' data-number='1'>
                                <label>".$lang_text['quiz_add']['subject']."</label>
                                <input type='text' name='subject' placeholder='".$lang_text['quiz_add']['subject_placeholder']."' >
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_add_content_item' data-number='2'>
                                <label>".$lang_text['quiz_add']['separator']."</label>
                                <input type='text' name='separator' placeholder='".$lang_text['quiz_add']['separator_placeholder']."' maxlength='5'>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='3'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_add_content_item' data-number='3'>
                                <label>".$lang_text['quiz_add']['term']."</label>
                                <input type='number' name='term' min='1' placeholder='".$lang_text['quiz_add']['term_placeholder']."'>
                                <button type='button' class='prev' data-number='2'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='4'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_add_content_item' data-number='4'>
                                <label>".$lang_text['quiz_add']['file']."</label>
                                <input type='file' name='quiz_file' data-text='".$lang_text['quiz_add']['file_placeholder']."'>
                                <button type='button' class='prev' data-number='3'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-upload'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_add_more'>
                        <div class='menage_main_add_more_content'>
                            <form id='quiz_add_more_form' enctype='multipart/form-data'>
                            <div class='menage_main_add_more_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_add_more']['title']."</button>
                            </div>
                            <div class='menage_main_add_more_content_item' data-number='1'>
                                <label>".$lang_text['quiz_add_more']['subject']."</label>
                                <select name='subject' class='subject_list_add_more'></select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_add_more_content_item' data-number='2'>
                                <label>".$lang_text['quiz_add_more']['separator']."</label>
                                <input type='text' name='separator' placeholder='".$lang_text['quiz_add_more']['separator_placeholder']."' maxlength='5'>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='3'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_add_more_content_item' data-number='3'>
                                <label>".$lang_text['quiz_add_more']['file']."</label>
                                <input type='file' name='quiz_file' data-text='".$lang_text['quiz_add']['file_placeholder']."'>
                                <button type='button' class='prev' data-number='2'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-upload'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_status'>
                        <div class='menage_main_status_content'>
                            <form id='quiz_main_status_form' enctype='multipart/form-data'>
                            <div class='menage_main_status_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_status']['title']."</button>
                            </div>
                            <div class='menage_main_status_content_item' data-number='1'>
                                <label>".$lang_text['quiz_status']['share']."</label>
                                <select name='share'>
                                    <option value='0'>".$lang_text['quiz_status']['public_notification']."</option>
                                    <option value='2'>".$lang_text['quiz_status']['public_no_notification']."</option>
                                    <option value='1'>".$lang_text['quiz_status']['private']."</option>
                                </select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_status_content_item' data-number='2'>
                                <label>".$lang_text['quiz_status']['subject']."</label>
                                <select name='subject' class='subject_list_status'></select>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-check'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_download'>
                        <div class='menage_main_download_content'>
                            <form id='quiz_main_download_form' enctype='multipart/form-data'>
                            <div class='menage_main_download_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_download']['title']."</button>
                            </div>
                            <div class='menage_main_download_content_item' data-number='1'>
                                <label>".$lang_text['quiz_download']['subject']."</label>
                                <select name='subject' class='subject_list_download'></select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_download_content_item' data-number='2'>
                                <label>".$lang_text['quiz_download']['format']."</label>
                                <select name='format'>
                                    <option value=''>".$lang_text['quiz_download']['format_text']."</option>
                                    <option value='pdf'>PDF</option>
                                    <option value='txt'>TXT</option>
                                    <option value='csv'>CSV</option>
                                    <option value='json'>JSON</option>
                                </select>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='3'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_download_content_item' data-number='3'>
                                <label>".$lang_text['quiz_download']['signed_type']."</label>
                                <select name='signed_type'>
                                    <option value=''>".$lang_text['quiz_download']['signed_type_text']."</option>
                                    <option value='allWith'>".$lang_text['quiz_download']['signed_type_all_with']."</option>
                                    <option value='allWithout'>".$lang_text['quiz_download']['signed_type_all_without']."</option>
                                    <option value='onlyCorrect'>".$lang_text['quiz_download']['signed_type_correct']."</option>
                                </select>
                                <button type='button' class='prev' data-number='2'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='4'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_download_content_item' data-number='4'>
                                <label>".$lang_text['quiz_download']['separator']."</label>
                                <input type='text' name='separator' placeholder='".$lang_text['quiz_download']['separator_placeholder']."'>
                                <button type='button' class='prev' data-number='3'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-download'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_delete'>
                        <div class='menage_main_delete_content'>
                            <form id='quiz_main_delete_form' enctype='multipart/form-data'>
                            <div class='menage_main_delete_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_delete']['title']."</button>
                            </div>
                            <div class='menage_main_delete_content_item' data-number='1'>
                                <label>".$lang_text['quiz_delete']['subject']."</label>
                                <select name='subject' class='subject_list_delete'></select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-trash-can'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_rename'>
                        <div class='menage_main_rename_content'>
                            <form id='quiz_main_rename_form' enctype='multipart/form-data'>
                            <div class='menage_main_rename_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_rename']['title']."</button>
                            </div>
                            <div class='menage_main_rename_content_item' data-number='1'>
                                <label>".$lang_text['quiz_rename']['subject']."</label>
                                <select name='subject' class='subject_list_rename'></select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_rename_content_item' data-number='2'>
                                <label>".$lang_text['quiz_rename']['subject_name']."</label>
                                <input type='text' name='new_name' placeholder='".$lang_text['quiz_rename']['subject_placeholder']."'>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-pen'></i></button>
                            </div>
                            </form>
                        </div>
                        </div>
                        <div class='menage_main_moderation'>
                            <div class='menage_main_moderation_content'>
                            <form id='quiz_main_moderation_form' enctype='multipart/form-data'>
                            <div class='menage_main_moderation_content_item left_show' data-number='0'>
                                <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_moderation']['title']."</button>
                            </div>
                            <div class='menage_main_moderation_content_item' data-number='1'>
                                <label>".$lang_text['quiz_moderation']['add_remove']."</label>
                                <select name='add_remove'>
                                    <option value='add'>".$lang_text['quiz_moderation']['add']."</option>
                                    <option value='remove'>".$lang_text['quiz_moderation']['remove']."</option>
                                </select>
                                <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                            </div>
                            <div class='menage_main_moderation_content_item' data-number='2'>
                                <label>".$lang_text['quiz_moderation']['users']."</label>
                                <select name='users' class='users_list_moderation'></select>
                                <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                <button type='submit' class='send'><i class='fa-solid fa-fingerprint'></i></button>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class='menage_main_code'>
                        <div class='menage_main_code_content'>
                            <div class='menage_main_code_content_left'>
                                <form id='quiz_main_code_remove_form' enctype='multipart/form-data'>
                                <div class='menage_main_code_content_left_item left_show' data-number='0'>
                                    <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_code']['list']['title']."</button>
                                </div>
                                <div class='menage_main_code_content_left_item' data-number='1'>
                                    <label>".$lang_text['quiz_code']['list']['code']."</label>
                                    <select name='code' class='code_list'></select>
                                    <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='submit' class='send'><i class='fa-solid fa-trash-can'></i></button>
                                </div>
                                </form>
                            </div>
                            <div class='menage_main_code_content_right'>
                                <form id='quiz_main_code_add_form' enctype='multipart/form-data'>
                                <div class='menage_main_code_content_right_item left_show' data-number='0'>
                                    <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_code']['add']['title']."</button>
                                </div>
                                <div class='menage_main_code_content_right_item' data-number='1'>
                                    <label>".$lang_text['quiz_code']['add']['count']."</label>
                                    <input type='number' name='count' min='1' placeholder='".$lang_text['quiz_code']['add']['count_placeholder']."'>
                                    <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                                </div>
                                <div class='menage_main_code_content_right_item' data-number='2'>
                                    <label>".$lang_text['quiz_code']['add']['term']."</label>
                                    <input type='text' name='term' placeholder='".$lang_text['quiz_code']['add']['term_placeholder']."'>
                                    <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='submit' class='send'><i class='fa-solid fa-plus'></i></button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class='menage_main_analytic'>
                        <div class='menage_main_analytic_content'>
                            <div class='menage_main_analytic_content_left'>
                                <div class='menage_main_analytic_content_left_random'>
                                    <button type='button' class='random' data-id='random'>".$lang_text['quiz_analytic']['random']."</button>
                                    <span class='analytic_random_showcase'>0 ".$_SESSION['lang']['admin']['menage']['quiz_analytic']['showcase']['points']."</span>
                                </div>
                                <ul class='menage_main_analytic_content_left_list'>
                                    <li>".$lang_text['quiz_analytic']['showcase']['correct'].": <span class='analytic_showcase_correct'>0</span></li>
                                    <li>".$lang_text['quiz_analytic']['showcase']['incorrect'].": <span class='analytic_showcase_incorrect'>0</span></li>
                                    <li>".$lang_text['quiz_analytic']['showcase']['halfcorrect'].": <span class='analytic_showcase_halfcorrect'>0</span></li>
                                    <li>".$lang_text['quiz_analytic']['showcase']['checked'].": <span class='analytic_showcase_checked'>0</span></li>
                                    <li>".$lang_text['quiz_analytic']['showcase']['maxchecked'].": <span class='analytic_showcase_maxchecked'>0</span></li>
                                    <li>".$lang_text['quiz_analytic']['showcase']['count'].": <span class='analytic_showcase_count'>0</span></li>
                                </ul>
                            </div>
                            <div class='menage_main_analytic_content_right'>
                                <form id='quiz_main_analytic_form' enctype='multipart/form-data'>
                                <div class='menage_main_analytic_content_right_item left_show' data-number='0'>
                                    <button type='button' class='next title_button' data-number='1'>".$lang_text['quiz_analytic']['title']."</button>
                                </div>
                                <div class='menage_main_analytic_content_right_item' data-number='1'>
                                    <label>".$lang_text['quiz_analytic']['text_correct']."</label>
                                    <input type='number' name='correct' step=0.03125 placeholder='".$lang_text['quiz_analytic']['text_correct_placeholder']."'>
                                    <button type='button' class='prev' data-number='0'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='button' class='next' data-number='2'><i class='fa-solid fa-arrow-right'></i></button>
                                </div>
                                <div class='menage_main_analytic_content_right_item' data-number='2'>
                                    <label>".$lang_text['quiz_analytic']['text_incorrect']."</label>
                                    <input type='number' name='incorrect' step=0.03125 placeholder='".$lang_text['quiz_analytic']['text_incorrect_placeholder']."'>
                                    <button type='button' class='prev' data-number='1'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='button' class='next' data-number='3'><i class='fa-solid fa-arrow-right'></i></button>
                                </div>
                                <div class='menage_main_analytic_content_right_item' data-number='3'>
                                    <label>".$lang_text['quiz_analytic']['text_halfcorrect']."</label>
                                    <input type='number' name='halfcorrect' step=0.03125 placeholder='".$lang_text['quiz_analytic']['text_halfcorrect_placeholder']."'>
                                    <button type='button' class='prev' data-number='2'><i class='fa-solid fa-arrow-left'></i></button>
                                    <button type='submit' class='send'><i class='fa-solid fa-chart-simple'></i></button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }else if($_POST['content']=='users'){
            $lang_text = $_SESSION['lang']['admin']['users'];
            $sql = "SELECT count(*) FROM quiz_users";
            $count_all_users = mysqli_fetch_array(mysqli_query($conn, $sql))[0];
            $sql = "SELECT * FROM quiz_users order by status desc, last_login desc";
            if($result = mysqli_query($conn, $sql)){
                echo "<div class='users'>
                <div class='users_user_settings'>
                    <div class='users_user_settings_close'>
                        <i class='fa-solid fa-chevron-left'></i>
                    </div>
                    <div class='users_user_settings_content'></div>
                </div>
                <div class='users_list'>
                    <div class='users_list_content'>
                    <div class='users_list_content_navbar'>
                        <h3>".$lang_text['text']." (".$count_all_users.")</h3>
                        <ul>
                            <li class='reset_all_devices_users'>".$lang_text['reset']['all_devices']['title']."</li>
                            <li class='notification_all_user'>".$lang_text['notification']['all_users']['title']."</li>
                            <li class='location_all_user'>".$lang_text['limit']['title']."</li>
                            <li><input type='text' id='search_user' placeholder='".$lang_text['search_placeholder']."'></li>
                        </ul>
                    </div>
                    ";
            while($row = mysqli_fetch_array($result)){
                $sql_devices = "SELECT udevices FROM `devices` WHERE email = '".$row['email']."'";
                if($result_devices = mysqli_query($conn, $sql_devices)){
                    $devices = [];
                    $count_devices = 0;
                    $count_location = 0;
                    foreach ($result_devices as $row_devices) {
                        $device_name = substr($row_devices['udevices'], 0, strpos($row_devices['udevices'], '|') - 1);
                        if (!isset($devices[$device_name])) {
                            $devices[$device_name] = [];
                            $count_devices++;
                        }
                        $count_location++;
                    }
                }
                $progress_quiz = 0;
                $progress_name = "";
                $file_name_live = "../analytic/".$row['code']."_score.json";
                if(file_exists($file_name_live)){
                    $json = json_decode(file_get_contents($file_name_live), true);
                    $temp_progress = 0;
                    foreach($json as $record){
                        if($record['date'] != null){
                            $progress_quiz++;
                        }
                        $progress_name = $record['subject'];
                        $temp_progress = $record['id'];
                    }
                    $progress_quiz = round($progress_quiz / $temp_progress * 100);
                }
                echo "<div class='users_list_content_item' data-id='".$row['id']."'>
                    <h3>
                        <span class='".$row['status']."'>•</span>
                        <span>".$row['email']."</span>
                    </h3>
                    <div class='users_list_content_item_progress'>";
                    echo "<h6>".$progress_name."</h6>
                        <div class='users_list_content_item_progress_bar'>
                            <div style='width:".$progress_quiz."%'></div>
                        </div>
                    </div>
                    <div class='users_list_content_item_devices'>
                        <i class='fa-solid fa-display'></i>";
                        $sql_limits = "SELECT * FROM quiz_admin";
                        $result_limits = mysqli_query($conn, $sql_limits);
                        $row_limits = mysqli_fetch_array($result_limits);
                        if($count_devices <= 0){
                            echo "<span>0</span>";
                        }else if ($count_devices <= $row_limits['device_limit_good'] && $count_devices > 0){
                            echo "<span class='good'>".$count_devices."</span>";
                        }else if ($count_devices <= $row_limits['device_limit_bad'] && $count_devices > $row_limits['device_limit_good']){
                            echo "<span class='warn'>".$count_devices."</span>";
                        }else{
                            echo "<span class='bad'>".$count_devices."</span>";
                        }
                        echo "<i class='fa-solid fa-location-arrow'></i>";
                        if($count_location <= 0){
                            echo "<span>0</span>";
                        }else if ($count_location <= $row_limits['location_limit_good'] && $count_location > 0){
                            echo "<span class='good'>".$count_location."</span>";
                        }else if ($count_location <= $row_limits['location_limit_bad'] && $count_location > $row_limits['location_limit_good']){
                            echo "<span class='warn'>".$count_location."</span>";
                        }else{
                            echo "<span class='bad'>".$count_location."</span>";
                        }
                        echo "</div>
                    <i class='fa-solid fa-ellipsis-vertical' data-id='".$row['code']."'></i>
                    <i class='fa-solid fa-paper-plane' data-id='".$row['email']."'></i>
                    <div class='users_list_content_item_bottom'>
                        <div>
                            <i class='fa-solid fa-eye'></i>";
                            $date = explode("-", explode(" ", $row['last_login'])[0])[2]."/".explode("-", explode(" ", $row['last_login'])[0])[1]." ".explode(":", explode(" ", $row['last_login'])[1])[0].":".explode(":", explode(" ", $row['last_login'])[1])[1];
                            echo "<span>".$date."</span>
                        </div>
                        <div>
                            <span>".$row['code']."</span>";
                            if($row['dark']==-1){
                                echo "<i class='fa-solid fa-shield-halved blocked'></i>";
                            }else{
                                echo "<i class='fa-solid fa-shield-halved'></i>";
                            }
                        echo "</div>
                    </div>
                </div>";
            }
            echo "
                    </div>
                </div>
            </div>";
            }
        }else if($_POST['content']=='logs'){
            $logs = scandir("../logs");
            unset($logs[0]);
            unset($logs[1]);
            usort($logs, function($a, $b) {
                return filemtime("../logs/" . $a) < filemtime("../logs/" . $b);
            });
            echo "<div class='logs'>
                <div class='logs_content'>
                <i class='fa-solid fa-arrows-rotate reload_logs'></i>
                <select class='logs_content_select'>";
                if(isset($_POST['log'])){
                    if($_POST['log'] == 0){
                        $log_selected = substr($logs[0], 0, -5);
                    }else{
                        if(file_exists("../logs/".$_POST['log'].".json")){
                            $log_selected = $_POST['log'];
                        }else{
                            $log_selected = substr($logs[0], 0, -5);
                        }
                    }
                }
                foreach($logs as $log){
                    $log = substr($log, 0, -5);
                    if($log == $log_selected){
                        echo "<option value='".$log."' selected>".$log."</option>";
                    }else{
                        echo "<option value='".$log."'>".$log."</option>";
                    }
                }
                echo "</select>";
                $json = file_get_contents("../logs/".$log_selected.".json");
                $json = json_decode($json, true);
                $json = array_reverse($json);
                foreach($json as $record){
                    if(isset($record['data'])){
                        if(is_numeric($record['data']) || $record['title'] == null || $record['text'] == null || $record['user'] == null || $record['date'] == null){
                            echo "<div class='logs_content_item logs_content_item_error'>";
                        }else{
                            echo "<div class='logs_content_item'>";
                        }
                    }else{
                        if($record['title'] == null || $record['text'] == null || $record['user'] == null || $record['date'] == null){
                            echo "<div class='logs_content_item logs_content_item_error'>";
                        }else{
                            echo "<div class='logs_content_item'>";
                        }
                    }
                    $title_log = $record['title'] == null ? 'N/A' : $record['title'];
                    $text_log = $record['text'] == null ? 'N/A' : $record['text'];
                    $user_log = $record['user'] == null ? 'N/A' : $record['user'];
                    $date_log = $record['date'] == null ? 'N/A' : $record['date'];
                    echo "<h3>".$record['id'].". ".$title_log."</h3>
                    <p>".$text_log."</p>";
                    if(isset($record['data'])){
                        echo "<div class='logs_content_item_data'>";
                        if (is_array($record['data']) || is_object($record['data'])){
                            foreach($record['data'] as $data => $value){
                                $data_log = $data == null ? 'N/A' : $data;
                                $value_log = $value == null ? 'N/A' : $value;
                                echo "<p>".$data_log.": ".$value_log."</p>";
                            }
                        }else{
                            $data_log = $record['data'] == null ? 'N/A' : $record['data'];
                            echo "<p>".$data_log."</p>";
                        }
                        echo "</div>";
                    }
                    echo "<span><span class='user'>".$user_log."</span> - ".$date_log."</span></div>";
                }
            echo "</div></div>";
        }
    }
    $conn->close();
} catch (Exception $e) {
    echo $_SESSION['lang']['database']['error'];
    exit();
} catch (Error $e) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}
?>