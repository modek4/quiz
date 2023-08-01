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
    //change question order
    if(isset($_POST['question_order'])){
        $question_order = $_POST['question_order'];
        $sql = "UPDATE codes SET question_order='".$question_order."' WHERE code='".$_SESSION['code']."'";
        if($result = $conn->query($sql)){
            $_SESSION['question_order'] = $question_order;
            add_log(
                $_SESSION['lang']['logs']['settings']['title'],
                $_SESSION['lang']['logs']['settings']['question_order_success'],
                $_SESSION['email'],
                "../logs/"
            );
        }else{
            add_log(
                $_SESSION['lang']['logs']['settings']['title'],
                $_SESSION['lang']['logs']['settings']['question_order_error'],
                $_SESSION['email'],
                "../logs/"
            );
        }
    //change question analytic
    } else if(isset($_POST['question_analytic'])){
        $question_analytic = $_POST['question_analytic'];
        $sql = "UPDATE codes SET question_analytic='".$question_analytic."' WHERE code='".$_SESSION['code']."'";
        if($result = $conn->query($sql)){
            $_SESSION['question_analytic'] = $question_analytic;
            add_log(
                $_SESSION['lang']['logs']['settings']['title'],
                $_SESSION['lang']['logs']['settings']['question_analytic_success'],
                $_SESSION['email'],
                "../logs/"
            );
        }else{
            add_log(
                $_SESSION['lang']['logs']['settings']['title'],
                $_SESSION['lang']['logs']['settings']['question_analytic_error'],
                $_SESSION['email'],
                "../logs/"
            );
        }
    }else{
        $sql = "SELECT term FROM codes WHERE code='".$_SESSION['code']."'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $term = $row['term'];
        echo "<div class='settings_menu_main'>";
        echo "<div class='settings_menu_main_title'>
                <h3>".$_SESSION['lang']['quiz']['settings']['text']."</h3>
                <a class='close_score_settings'></a>
            </div>
            <div class='settings_menu_main_content'>
                <ul>
                    <li>".$_SESSION['lang']['quiz']['settings']['sort_title']."</li>
                    <li><span>".$_SESSION['lang']['quiz']['settings']['sort_text'].":</span></li>";
                    if(@$_SESSION['question_order'] == 0){
                        echo "
                        <li class='radio'>
                            <input id='radio_1' name='radio' type='radio' value=0 checked>
                            <label for='radio_1' class='radio_label'>".$_SESSION['lang']['quiz']['settings']['sort_option_random']."</label>
                        </li>
                        <li class='radio'>
                            <input id='radio_2' name='radio' type='radio' value=1>
                            <label for='radio_2' class='radio_label'>".$_SESSION['lang']['quiz']['settings']['sort_option_numeric']."</label>
                        </li>
                        ";
                    }else{
                        echo "
                        <li class='radio'>
                            <input id='radio_1' name='radio' type='radio' value=0>
                            <label for='radio_1' class='radio_label'>".$_SESSION['lang']['quiz']['settings']['sort_option_random']."</label>
                        </li>
                        <li class='radio'>
                            <input id='radio_2' name='radio' type='radio' value=1 checked>
                            <label for='radio_2' class='radio_label'>".$_SESSION['lang']['quiz']['settings']['sort_option_numeric']."</label>
                        </li>
                        ";
                    }
                    echo "
                </ul>
                <ul>
                    <li>".$_SESSION['lang']['quiz']['settings']['algorithm_title']."</li>
                    <li><span>".$_SESSION['lang']['quiz']['settings']['algorithm_text'].":</span></li>";
                    if(@$_SESSION['question_order'] == 1){
                        echo "
                        <li class='checkbox_item'>
                            <input class='checkbox' id='checkbox_analytic' type='checkbox' disabled/>
                            <label class='checkbox_btn' for='checkbox_analytic'></label>
                        </li>";
                    }else{
                        if(@$_SESSION['question_analytic'] == 0){
                            echo "
                            <li class='checkbox_item'>
                                <input class='checkbox' id='checkbox_analytic' type='checkbox'/>
                                <label class='checkbox_btn' for='checkbox_analytic'></label>
                            </li>";
                        }else{
                            echo "
                            <li class='checkbox_item'>
                                <input class='checkbox' id='checkbox_analytic' type='checkbox' checked/>
                                <label class='checkbox_btn' for='checkbox_analytic'></label>
                            </li>";
                        }
                    }
                    echo "
                </ul>
                <ul>
                    <li>".$_SESSION['lang']['quiz']['settings']['term_title'].": ".$term."</li>
                    <li><span>".$_SESSION['lang']['quiz']['settings']['term_text']."</span></li>
                </ul>
                <ul>
                    <li>".$_SESSION['lang']['quiz']['settings']['code_title'].": ".$_SESSION['code']."</li>
                </ul>
            </div>";
        echo "</div>";
        echo "<div class='settings_menu_main_background'></div>";
        echo "<script>
        $(document).ready(function(){
            const random_option = document.getElementById('radio_1');
            const numeric_option = document.getElementById('radio_2');
            const checkbox = document.getElementById('checkbox_analytic');
            checkbox.addEventListener('change', handle_checkbox_change);
            function handle_checkbox_change(event) {
                var selected_value = event.target.checked;
                if(selected_value == true){
                    selected_value = 1;
                }else{
                    selected_value = 0;
                }
                $.ajax({
                    type: 'POST',
                    url: 'db/show_settings.php',
                    data: {question_analytic: selected_value},
                    success: function(data){
                        $.ajax({
                            type: 'POST',
                            url: 'db/show_settings.php',
                            success: function(response) {
                              $('.settings_menu').html(response);
                              $('.close_score_settings').click(function() {
                                $('.settings_menu').fadeOut(500);
                              });
                              $('.settings_menu_main_background').click(function() {
                                $('.close_score_settings').click();
                              });
                            },
                            error: function(xhr, status, error) {
                                add_log('index: reload settings after change analytic', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                                notifyshow(status+' ('+xhr.status+'): '+error, '');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        add_log('index: settings change question analytic', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                        notifyshow(status+' ('+xhr.status+'): '+error, '');
                    }
                });
            }
            if(random_option.checked){
                numeric_option.addEventListener('click', handleOptionClick);
            }else if(numeric_option.checked){
                random_option.addEventListener('click', handleOptionClick);
            }
            function handleOptionClick(event) {
                var selected_value = event.target.value;
                $.ajax({
                    type: 'POST',
                    url: 'db/show_settings.php',
                    data: {question_order: selected_value},
                    success: function(data){
                        $.ajax({
                            type: 'POST',
                            url: 'db/show_settings.php',
                            success: function(response) {
                              $('.settings_menu').html(response);
                              $('.close_score_settings').click(function() {
                                $('.settings_menu').fadeOut(500);
                              });
                              $('.settings_menu_main_background').click(function() {
                                $('.close_score_settings').click();
                              });
                            },
                            error: function(xhr, status, error) {
                                add_log('index: reload settings after order', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                                notifyshow(status+' ('+xhr.status+'): '+error, '');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        add_log('index: settings change questions order', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                        notifyshow(status+' ('+xhr.status+'): '+error, '');
                    }
                });
            }
        });
        </script>";
    }
    $conn->close();
}
?>