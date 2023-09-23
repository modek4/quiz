<?php
session_start();
require_once("connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['id'])){
        echo "<a class='close_score_show'></a>";
        $email = $_POST['id'];
        $sql = "SELECT * FROM scores WHERE email='$email' ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            echo "<h2>".$_SESSION['lang']['quiz']['stats']['table']['empty']."</h2>";
            exit;
        }else{
            echo "<table>
            <thead>
                <tr>
                    <th>".$_SESSION['lang']['quiz']['stats']['table']['subject']."</th>
                    <th>".$_SESSION['lang']['quiz']['stats']['table']['score']."</th>
                    <th>".$_SESSION['lang']['quiz']['stats']['table']['date']."</th>
                    <th>".$_SESSION['lang']['quiz']['stats']['table']['time']."</th>
                </tr>
            </thead>";
            while ($row = mysqli_fetch_array($result)) {
                echo "
                <tr>
                    <td data-column=''>".$row['subject']."</td>
                    <td data-column='".$_SESSION['lang']['quiz']['stats']['table']['score'].": '>".$row['score']."/".$row['question_count']." (".round(($row['score']/$row['question_count'])*100,2)."%)</td>
                    <td data-column='".$_SESSION['lang']['quiz']['stats']['table']['date'].": '>".date("d/m/Y H:i:s", strtotime($row['end_date']))."</td>
                    <td data-column='".$_SESSION['lang']['quiz']['stats']['table']['time'].": '>".$row['total_time']."</td>
               </tr>";
            }
            echo "</table>";
        }
    } else if(isset($_SESSION['email'])){
        if(isset($_POST['score_id'])){
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $halfcorrectAnswers = 0;
            $skippedAnswers = 0;
            $selectedAnswersArray = [];
            $sql = "SELECT * FROM scores WHERE id=".$_POST['score_id'];
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($result);
            $subject_stats_name = $row['subject'];
            $actual_score_percent = round(($row['score']/$row['question_count'])*100,2);
            $actual_score = $row['score']."/".$row['question_count'];

            $actual_time = strtotime($row['total_time'])-strtotime('TODAY');
            $actual_time_seconds_cmp = strtotime($row['total_time'])-strtotime('TODAY');
            $actual_time_hours = floor($actual_time_seconds_cmp / 3600)."h ";
            $actual_time_minutes = floor(($actual_time_seconds_cmp / 60) % 60)."m ";
            $actual_time_seconds = ($actual_time_seconds_cmp % 60)."s ";
            $actual_time_hours = $actual_time_hours == "0h " ? "" : $actual_time_hours;
            $actual_time_minutes = $actual_time_minutes == "0m " ? "" : $actual_time_minutes;

            $score = json_decode($row['answers'], true);
            $question_ids = array();
            foreach ($score as $item) {
                if($item['id'] <= $row['question_count']){
                    array_push($question_ids, $item['id_question']);
                    $num_questions = $item['id'];
                    if($item['answers'] == 0){
                        $skippedAnswers++;
                        $selectedAnswersArray[$item['id_question']] = 'skippedAnswers';
                    }else{
                        $answerUser = str_split($item['answers']);
                        $correct_answers_split = $item['correct_answers'];
                        $letterCount = 0;
                        foreach ($answerUser as $letter) {
                            $letterCount += substr_count($correct_answers_split, $letter);
                        }
                        if($letterCount == strlen($correct_answers_split)){
                            $correctAnswers++;
                            $selectedAnswersArray[$item['id_question']] = 'correctAnswers';
                        }else if($letterCount > 0){
                            $halfcorrectAnswers++;
                            $selectedAnswersArray[$item['id_question']] = 'halfcorrectAnswers';
                        }else{
                            $incorrectAnswers++;
                            $selectedAnswersArray[$item['id_question']] = 'incorrectAnswers';
                        }
                    }
                }
            }
        echo "<div class='specific_score quiz'>";
        echo "<a id='close_specific' data-orderby='".$_POST['orderby']."' data-page='".$_POST['page']."' class='close_score_show'></a>";
            $sql_stats = "SELECT * FROM analytics WHERE subject = '$subject_stats_name' and code = '".$_SESSION['code']."'";
            $result_stats = mysqli_query($conn, $sql_stats);
            $checked_stats = 0;
            $row_stats = mysqli_fetch_assoc($result_stats);
            $checked_stats_data = json_decode($row_stats['analytic'],true);
            foreach($checked_stats_data as $item){
                $checked_stats += $item['checked'];
            }
            $sql_stats = "SELECT * FROM `scores` WHERE `subject` = '$subject_stats_name' and email = '".$_SESSION['email']."'";
            $result_stats = mysqli_query($conn, $sql_stats);
            $answers_stats = 0;
            $time_stats = 0;
            $answersCount_stats = 0;
            while ($row_stats = mysqli_fetch_assoc($result_stats)) {
                $answers_stats += round($row_stats['score']/$row_stats['question_count']*100,2);
                $time_stats += strtotime($row_stats['total_time'])-strtotime('TODAY');
                $answersCount_stats++;
            }
            $answers_stats = round($answers_stats/$answersCount_stats,2);
            $time_stats = $time_stats/$answersCount_stats;
            $seconds = $time_stats;
            $time_stats_avg_per_question = $time_stats/$row['question_count'];
            $hours = floor($seconds / 3600)."h ";
            $minutes = floor(($seconds / 60) % 60)."m ";
            $seconds = ($seconds % 60)."s";
            $hours = $hours == "0h " ? "" : $hours;
            $minutes = $minutes == "0m " ? "" : $minutes;
            $time_stats_avg = $hours.$minutes.$seconds;
            $actual_time = $actual_time_hours.$actual_time_minutes.$actual_time_seconds;
            echo "<div class='container_graph'>
                <h2>".$_SESSION['lang']['quiz']['stats']['specific']['title']." ".$row['subject']."</h2>
                <h3>".$_SESSION['lang']['quiz']['stats']['specific']['sub_title']."</h3>
                <div class='container_graph_overview'>
                    <div class='container_graph_overview_item'>
                        <div class='container_graph_overview_item_icon'>
                            <i class='fa-solid fa-computer-mouse'></i>
                        </div>
                        <div class='container_graph_overview_item_text'>
                            <h3>".$_SESSION['lang']['quiz']['stats']['specific']['all_answers']."</h3>
                            <p>".$checked_stats."</p>
                        </div>
                    </div>
                    <div class='container_graph_overview_item'>
                        <div class='container_graph_overview_item_icon'>
                            <i class='fa-solid fa-hourglass-half'></i>
                        </div>
                        <div class='container_graph_overview_item_text'>
                            <h3>".$_SESSION['lang']['quiz']['stats']['specific']['avg_time']."</h3>
                            <p>".$time_stats_avg."</p>
                        </div>
                    </div>
                    <div class='container_graph_overview_item'>
                        <div class='container_graph_overview_item_icon'>
                            <i class='fa-solid fa-calculator'></i>
                        </div>
                        <div class='container_graph_overview_item_text'>
                            <h3>".$_SESSION['lang']['quiz']['stats']['specific']['avg_score']."</h3>
                            <p>".$answers_stats."%</p>
                        </div>
                    </div>
                </div>
                <h2>".$_SESSION['lang']['quiz']['stats']['specific']['specific_title']." ".$row['end_date']."</h2>
                <div class='container_graph_specific'>
                    <div class='container_graph_overview_item'>
                        <div class='container_graph_overview_item_icon'>
                            <i class='fa-solid fa-star-half-stroke'></i>
                        </div>
                        <div class='container_graph_overview_item_text'>
                            <h3>".$_SESSION['lang']['quiz']['stats']['specific']['score']."</h3>
                            <p>".$actual_score." (".$actual_score_percent."%)</p>
                        </div>";
                        if($actual_score_percent-$answers_stats > 0){
                            echo "<div class='container_graph_overview_item_rise'>
                            <i class='fa-solid fa-arrow-up'></i>
                            <span>".round($actual_score_percent-$answers_stats,2)."%</span>
                        </div>";
                        } else if($actual_score_percent-$answers_stats < 0){
                            echo "<div class='container_graph_overview_item_drop'>
                            <i class='fa-solid fa-arrow-down'></i>
                            <span>".round($actual_score_percent-$answers_stats,2)."%</span>
                        </div>";
                        }else{
                            echo "<div class='container_graph_overview_item_stay'>
                            <i>•</i>
                            <span>".round($actual_score_percent-$answers_stats,2)."%</span>
                            </div>";
                        }
                        echo "
                    </div>
                    <div class='container_graph_overview_item'>
                        <div class='container_graph_overview_item_icon'>
                            <i class='fa-solid fa-stopwatch'></i>
                        </div>
                        <div class='container_graph_overview_item_text'>
                            <h3>".$_SESSION['lang']['quiz']['stats']['specific']['time']."</h3>
                            <p>".$actual_time."</p>
                        </div>";
                        if($time_stats > $actual_time_seconds_cmp){
                            echo "
                            <div class='container_graph_overview_item_rise'>
                                <i class='fa-solid fa-arrow-up'></i>
                                <span>".round(($time_stats/$actual_time_seconds_cmp)*100,2)."%</span>
                            </div>";
                        } else if ($time_stats < $actual_time_seconds_cmp){
                            echo "
                            <div class='container_graph_overview_item_drop'>
                                <i class='fa-solid fa-arrow-down'></i>
                                <span>-".round(($actual_time_seconds_cmp/$time_stats)*100,2)."%</span>
                            </div>";
                        } else {
                            echo "
                            <div class='container_graph_overview_item_stay'>
                                <i>•</i>
                                <span>0%</span>
                            </div>";
                        }
                        echo "
                    </div>
                </div>
                <div class='chart'>";
                    $correctAnswersPercent = round($correctAnswers/$row['question_count']*100,2);
                    if($correctAnswers > 0){
                    echo "
                    <div class='part percent-".round($correctAnswersPercent)."'>
                        <div class='label'>".$_SESSION['lang']['quiz']['stats']['specific']['answers']['correct']."</div>
                        <div class='bar green_correct'>.
                            <div class='label'>".$correctAnswers." (".$correctAnswersPercent."%)</div>
                        </div>
                    </div>";
                    }
                    $incorrectAnswersPercent = round($incorrectAnswers/$row['question_count']*100,2);
                    if($incorrectAnswers > 0){
                    echo "
                    <div class='part percent-".round($incorrectAnswersPercent)."'>
                        <div class='label'>".$_SESSION['lang']['quiz']['stats']['specific']['answers']['incorrect']."</div>
                        <div class='bar red_incorrect'>.
                            <div class='label'>".$incorrectAnswers." (".$incorrectAnswersPercent."%)</div>
                        </div>
                    </div>";
                    }
                    $halfcorrectAnswersPercent = round($halfcorrectAnswers/$row['question_count']*100,2);
                    if($halfcorrectAnswers > 0){
                    echo "
                    <div class='part percent-".round($halfcorrectAnswersPercent)."'>
                        <div class='label'>".$_SESSION['lang']['quiz']['stats']['specific']['answers']['halfcorrect']."</div>
                        <div class='bar accent_halfcorrect'>.
                            <div class='label'>".$halfcorrectAnswers." (".$halfcorrectAnswersPercent."%)</div>
                        </div>
                    </div>";
                    }
                    if($skippedAnswers > 0){
                        $skippedAnswersPercent = round($skippedAnswers/$row['question_count']*100,2);
                        echo "<div class='part percent-".round($skippedAnswersPercent)."'>
                        <div class='label'>".$_SESSION['lang']['quiz']['stats']['specific']['answers']['skipped']."</div>
                        <div class='bar accent_skipped'>.
                            <div class='label'>".$skippedAnswers." (".$skippedAnswersPercent."%)</div>
                        </div>";
                    }
                    echo "
                </div>
            </div>
        </div>";
        echo "
        <div class='select_filter'>
            <div class='select_filter_main'>
                <span class='select_filter_main_menu'>
                    <input type='radio' name='sortType' value='all' id='sort-all-specific' checked>
                    <label for='sort-all-specific'>".$_SESSION['lang']['quiz']['stats']['specific']['sort']['all']."</label>";
                    if($correctAnswers > 0){
                        echo "<input type='radio' name='sortType' value='correctAnswers' id='sort-correct-specific'>
                        <label for='sort-correct-specific'>".$_SESSION['lang']['quiz']['stats']['specific']['sort']['correct']."</label>";
                    }
                    if($incorrectAnswers > 0){
                        echo "<input type='radio' name='sortType' value='incorrectAnswers' id='sort-incorrect-specific'>
                        <label for='sort-incorrect-specific'>".$_SESSION['lang']['quiz']['stats']['specific']['sort']['incorrect']."</label>";
                    }
                    if($halfcorrectAnswers > 0){
                        echo "<input type='radio' name='sortType' value='halfcorrectAnswers' id='sort-halfcorrect-specific'>
                        <label for='sort-halfcorrect-specific'>".$_SESSION['lang']['quiz']['stats']['specific']['sort']['halfcorrect']."</label>";
                    }
                    if($skippedAnswers > 0){
                        echo "<input type='radio' name='sortType' value='skippedAnswers' id='sort-skipped-specific'>
                        <label for='sort-skipped-specific'>".$_SESSION['lang']['quiz']['stats']['specific']['sort']['skipped']."</label>";
                    }
                echo "</span>
            </div>
        </div>
        ";
            echo "<div id='quiz_results_specific_score'>";
            $questions = array();
            $questions_temp = array();
            $subject = $row['subject'];
            $sql_question = "SELECT * FROM questions WHERE `subject` = '$subject'";
            $result_question = mysqli_query($conn, $sql_question);
            while($row_question = mysqli_fetch_assoc($result_question)){
                array_push($questions_temp, $row_question);
            }
            foreach ($question_ids as $id){
                $question = array();
                foreach ($questions_temp as $question_temp) {
                    if($question_temp['id_question'] == $id){
                        $question = $question_temp;
                        break;
                    }
                }
                array_push($questions, $question);
            }
            $number_of_all_questions=1;
            foreach ($questions as $question) {
                if(!in_array(@$question['id_question'], $question_ids)){
                    continue;
                }
                echo "<div class='background_question background_question-".$selectedAnswersArray[$question['id_question']]."' data-id='".$question['id_question']."' data-name='".$selectedAnswersArray[$question['id_question']]."'>";
                echo "<p class='number_of_all_questions'>$number_of_all_questions / $num_questions</p>";
                $number_of_all_questions++;
                $question_text = $question['id_question'] .'. '.$question['question'];
                $count = 0;
                @$correct_answers = explode(";", $question['correct_answers']);
                @$correct_answers_length = count($correct_answers)-1;
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
                    foreach($score as $item){
                        if($item['id_question'] == $question['id_question']){
                            $answersUserToCheck = str_split($item['answers']);
                        }
                    }
                    $letter = chr(97 + $count);
                    if ($selectedAnswersArray[$question['id_question']] != 'skippedAnswers'){
                        if(in_array($letter, $correct_answers)){
                            $answers[$count] = "<div class='radio correct' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                        }else{
                            if(in_array($letter, $answersUserToCheck)){
                                $answers[$count] = "<div class='radio incorrect' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                            }else{
                                $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                            }
                        }
                    }else{
                        $answers[$count] = "<div class='radio' subject='".$question['subject']."' name='question-" . $question['id_question'] . "' value='" . $letter . "'>" . $answersid . "</div><br>";
                    }
                    $count++;
                }
                shuffle($answers);
                foreach ($answers as $answersid){
                  echo $answersid;
                }
                echo "</div>";
            }
            echo "</div>";
            echo "<script>
            var selected_click = 'sort-all-specific';
            $('.select_filter_main_menu').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('expanded');
                $('#'+$(e.target).attr('for')).prop('checked',true);
                let sortTypeVar = $(e.target).attr('for');
                if(selected_click != sortTypeVar){
                    selected_click = sortTypeVar;
                    let selected_val = $('input#'+$(e.target).attr('for')).val();
                    if(selected_val == null){
                        selected_val = 'all';
                    }
                    if(selected_val == 'all'){
                        $('.background_question').show();
                    }else{
                        $('.background_question').hide();
                        $('.background_question-'+selected_val).show();
                    }
                }
            });
            $(document).click(function() {
                $('.select_filter_main_menu').removeClass('expanded');
            });
            $('#close_specific').click(function() {
                $('#show_table').css({'-webkit-transform':'translateX(-110%)'});
                var page = $('#close_specific').data('page');
                var orderby = $('#close_specific').data('orderby');
                setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        url: 'db/show_score.php',
                        data: {page: page, orderby: orderby},
                        success: function(data) {
                            $('#show_table').css({'-webkit-transform':'translateX(0%)'});
                            $('#show_table').html(data);
                            $('.close_score_show').click(function() {
                                $('#show_table').css({'-webkit-transform':'translateX(-110%)'});
                            });
                        },
                        error: function(xhr, status, error) {
                            add_log('index: close specific score', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                            notifyshow(status+' ('+xhr.status+'): '+error, '');
                        }
                    });
                }, 200);
            });
            </script>";
        }else{
            echo "<a class='close_score_show'></a>";
            $email = $_SESSION['email'];
            if (!isset($_POST['orderby'])) {
                $orderby = '6 DESC';
            } else {
                $orderby = $_POST['orderby'];
            }
            $orderby_array = explode(" ", $orderby);
            $orderby_array[2] = $orderby_array[1] == 'ASC' ? '<span>⬆</span>' : '<span>⬇</span>';
            if($orderby_array[0] == 4){
                $orderby = '(score/question_count)'.$orderby_array[1];
            }
            $sql = "SELECT * FROM scores WHERE email='$email' ORDER BY ".$orderby;
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) == 0){
                echo "<h2>".$_SESSION['lang']['quiz']['stats']['table']['empty']."</h2>";
                exit();
            }else{
                $results_per_page = 20; // Results per page
                $total_results = mysqli_num_rows($result);
                $total_pages = ceil($total_results / $results_per_page);
                if (!isset($_POST['page'])) {
                    $page = 1;
                } else {
                    $page = $_POST['page'];
                }
                $start_index = ($page - 1) * $results_per_page;
                $sql .= " LIMIT $start_index, $results_per_page";
                $result = mysqli_query($conn, $sql);
                echo "<table data-page='".$page."' data-orderby='".$orderby."'>";
                echo "<thead><tr>";
                $columns = [
                    ['data-sort' => '3', 'data-order' => 'ASC'],
                    ['data-sort' => '4', 'data-order' => 'ASC'],
                    ['data-sort' => '6', 'data-order' => 'ASC'],
                    ['data-sort' => '7', 'data-order' => 'ASC'],
                ];
                foreach ($columns as $index => $column) {
                    $sortData = $column['data-sort'];
                    $orderData = ($orderby_array[0] == $sortData) ? $orderby_array[1] : $column['data-order'];
                    $displayName = '';
                    switch ($sortData) {
                        case '3':
                            $displayName = $_SESSION['lang']['quiz']['stats']['table']['subject'];
                            break;
                        case '4':
                            $displayName = $_SESSION['lang']['quiz']['stats']['table']['score'];
                            break;
                        case '6':
                            $displayName = $_SESSION['lang']['quiz']['stats']['table']['date'];
                            break;
                        case '7':
                            $displayName = $_SESSION['lang']['quiz']['stats']['table']['time'];
                            break;
                        default:
                            break;
                    }
                    if ($orderby_array[0] == $sortData) {
                        $displayName .= ' ' . $orderby_array[2];
                    }
                    echo "<th data-sort='$sortData' data-order='$orderData'>$displayName</th>";
                }
                echo "<th>".$_SESSION['lang']['quiz']['stats']['table']['stats_title']."</th>";
                echo "</tr></thead>";
                while ($row = mysqli_fetch_array($result)) {
                    $date = date("d/m/Y H:i:s", strtotime($row['end_date']));
                    $score = $row['score']."/".$row['question_count']." (". round(($row['score']/$row['question_count'])*100,2) ."%)";
                    echo "<tr>";
                    echo "<td data-column=''>" .$row['subject']. "</td>";
                    echo "<td data-column='".$_SESSION['lang']['quiz']['stats']['table']['score'].": '>".$score."</td>";
                    echo "<td data-column='".$_SESSION['lang']['quiz']['stats']['table']['date'].": '>" . $date . "</td>";
                    echo "<td data-column='".$_SESSION['lang']['quiz']['stats']['table']['time'].": '>" . $row['total_time'] . "</td>";
                    if($row['answers'] != null){
                        echo "<td class='show_specific_score' data-id='" .$row['id']. "' data-column='".$_SESSION['lang']['quiz']['stats']['table']['stats_title'].": '>".$_SESSION['lang']['quiz']['stats']['table']['stats_text']."</td>";
                    }else{
                        echo "<td class='disabled' data-column='".$_SESSION['lang']['quiz']['stats']['table']['stats_title'].": '>".$_SESSION['lang']['quiz']['stats']['table']['stats_text_unavailable']."</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                echo "<div class='pagination'>";
                if ($page > 1) {
                    echo "<a class='pagination_arrow' data-page='" . ($page - 1) . "'><i class='fa-solid fa-chevron-left'></i></a>";
                    echo "<a data-page='1'>1</a>";
                } else {
                    echo "<a class='pagination_actual_page' data-page='1'>1</a>";
                }
                if ($page != 1 && $page != $total_pages) {
                    echo "<span>...</span>";
                    echo "<span class='pagination_actual_page'>$page</span>";
                }
                if ($total_pages != 1) {
                    echo "<span>...</span>";
                }
                if ($page < $total_pages) {
                    if ($total_pages > 1) {
                        echo "<a data-page='$total_pages'>$total_pages</a>";
                    }
                    echo "<a class='pagination_arrow' data-page='" . ($page + 1) . "'><i class='fa-solid fa-chevron-right'></i></a>";
                } else {
                    if ($total_pages > 1) {
                        echo "<a class='pagination_actual_page' data-page='$total_pages'>$total_pages</a>";
                    }
                }

                echo "</div>";
                echo "<script>
                $('table th').click(function() {
                    var sort = $(this).data('sort');
                    var order = $(this).data('order');
                    var page = $('table').data('page');
                    if(order == 'ASC'){
                        $(this).data('order', 'DESC');
                    }else{
                        $(this).data('order', 'ASC');
                    }
                    if(sort != undefined){
                        order = $(this).data('order');
                        var orderby = sort+' '+order;
                        $('table th span').remove();
                        if(order == 'ASC'){
                            $(this).html($(this).html()+' <span>⬆</span>');
                        }else{
                            $(this).html($(this).html()+' <span>⬇</span>');
                        }
                        $.ajax({
                            type: 'POST',
                            url: 'db/show_score.php',
                            data: {page: page, orderby: orderby},
                            success: function(data) {
                                $('#show_table').html(data);
                                $('.close_score_show').click(function() {
                                    $('#show_table').css({'-webkit-transform':'translateX(-110%)'});
                                });
                            },
                            error: function(xhr, status, error) {
                                add_log('index: sort table', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                                notifyshow(status+' ('+xhr.status+'): '+error, '');
                            }
                        });
                    }
                });
                $('.pagination a').click(function() {
                    var page = $(this).data('page');
                    var orderby = $('table').data('orderby');
                    $.ajax({
                        type: 'POST',
                        url: 'db/show_score.php',
                        data: {page: page, orderby: orderby},
                        success: function(data) {
                            $('#show_table').html(data);
                            $('.close_score_show').click(function() {
                                $('#show_table').css({'-webkit-transform':'translateX(-110%)'});
                            });
                        },
                        error: function(xhr, status, error) {
                            add_log('index: pagination', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                            notifyshow(status+' ('+xhr.status+'): '+error, '');
                        }
                    });
                });
                $('.show_specific_score').click(function() {
                    var score_id = $(this).data('id');
                    var page_back = $('table').data('page');
                    var orderby_back = $('table').data('orderby');
                    $('#show_table').css({'-webkit-transform':'translateX(-110%)'});
                    setTimeout(function() {
                        $.ajax({
                            type: 'POST',
                            url: 'db/show_score.php',
                            data: {score_id: score_id, page: page_back, orderby: orderby_back},
                            success: function(data) {
                                $('#show_table').animate({scrollTop:0}, 'fast');
                                $('#show_table').css({'-webkit-transform':'translateX(0%)'});
                                $('#show_table').html(data);
                            },
                            error: function(xhr, status, error) {
                                add_log('index: show specific score', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                                notifyshow(status+' ('+xhr.status+'): '+error, '');
                            }
                        });
                    }, 200);
                });
                </script>";
            }
        }
    }
    $conn->close();
}
?>