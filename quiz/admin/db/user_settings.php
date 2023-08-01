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
                <div><span>".$_SESSION['lang']['admin']['users']['term']['text'].":</span><input type='text' class='term_list_input' value='".$row['term']."' data-id='".$row['code']."' placeholder='0,0,0...' disabled> <i class='fa-solid fa-pen-to-square'></i></div>
                <div class='user_menu'>
                    <span>".$row['email']."(".$user_code.")</span>";
                    if($row['dark'] == -1){
                        echo "<span class='block_user_button unblock_user' data-id='".$row['code']."'>".$_SESSION['lang']['admin']['users']['unblock']['text']."</span>";
                    }else{
                        echo "<span class='block_user_button block_user' data-id='".$row['code']."'>".$_SESSION['lang']['admin']['users']['block']['text']."</span>";
                    }
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
    $conn->close();
}