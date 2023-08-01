<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    $sql_count = "SELECT COUNT(*) as all_users,
        (SELECT COUNT(*) FROM quiz_users WHERE status='offline') as offline_users,
        (SELECT COUNT(*) FROM quiz_users WHERE status='online') as online_users,
        (SELECT COUNT(*) FROM subjects) as subject_count,
        (SELECT COUNT(*) FROM questions) as questions_count,
        (SELECT COUNT(*) FROM reports) as reports_count
        FROM quiz_users";
    if($result_count = mysqli_query($conn, $sql_count)){
        $row = mysqli_fetch_assoc($result_count);
        $all_users = $row['all_users'];
        $offline_users = $row['offline_users'];
        $online_users = $row['online_users'];
        $subject_count = $row['subject_count'];
        $questions_count = $row['questions_count'];
        $reports_count = $row['reports_count'];
        echo "<div class='main_right_users'>
            <div class='main_right_users_content'>
                <p>".$_SESSION['lang']['admin']['main']['users']['title']."</p>
                <span class='users_all' data-name='".$_SESSION['lang']['admin']['main']['users']['all']."'>".$all_users."</span>
                <span class='users_online' data-name='".$_SESSION['lang']['admin']['main']['users']['online']."'>".$online_users."</span>
                <span class='users_offline' data-name='".$_SESSION['lang']['admin']['main']['users']['offline']."'>".$offline_users."</span>
            </div>
        </div>
        <div class='main_right_questions'>
            <div class='main_right_questions_content'>
                <p>".$_SESSION['lang']['admin']['main']['questions']['title']."</p>
                <span class='questions_all' data-name='".$_SESSION['lang']['admin']['main']['questions']['questions']."'>".$questions_count."</span>
                <span class='subjects_all' data-name='".$_SESSION['lang']['admin']['main']['questions']['subjects']."'>".$subject_count."</span>
                <span class='reports_all' data-name='".$_SESSION['lang']['admin']['main']['questions']['reports']."'>".$reports_count."</span>
            </div>
        </div>
        <div class='main_right_logs'>
            <div class='main_right_logs_content'>
                <p>".$_SESSION['lang']['admin']['main']['logs']['title']."</p>
                <select class='select_logs'>";
                if($_SESSION['mod']==true){
                    echo "<option value=''>".$_SESSION['lang']['admin']['main']['logs']['no_permission']."</option>";
                }else{
                    echo "<option value=''>".$_SESSION['lang']['admin']['main']['logs']['select']."</option>";
                    $dir = scandir("../../logs/");
                    rsort($dir);
                    foreach($dir as $file){
                        if($file!="." && $file!=".."){
                            $file = explode(".", $file)[0];
                            echo "<option value='".$file."'>".$file."</option>";
                        }
                    }
                }
                echo "</select>
            </div>
        </div>";
    }
    $conn->close();
}