<?php
session_start();
require_once("../../db/connect.php");
require_once("../../log.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(isset($_POST['subject']) && isset($_POST['id'])){
        $lang_text = $_SESSION['lang']['admin']['reports'];
        $sql = "SELECT * FROM reports LEFT JOIN questions on (reports.question_id=questions.id_question AND reports.subject=questions.subject) WHERE reports.subject='".$_POST['subject']."' AND question_id='".$_POST['id']."' order by report_date desc";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_array($result);
            $date = explode("-", explode(" ", $row['report_date'])[0])[2]."/".explode("-", explode(" ", $row['report_date'])[0])[1]." ".explode(":", explode(" ", $row['report_date'])[1])[0].":".explode(":", explode(" ", $row['report_date'])[1])[1];
            echo "<i class='fa-solid fa-rotate-right' data-id='".$row['id_question']."' data-name='".$row['subject']."'></i>
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
                        <i class='fa-sharp fa-solid fa-trash'></i>
                    </div>";
                }else if($find_user){
                    echo "<div class='answer'>
                        <span>•</span>
                        <textarea type='text' class='user_answer' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                        <i class='fa-sharp fa-solid fa-trash'></i>
                    </div>";
                } else if($find_correct){
                    echo "<div class='answer'>
                        <span class='active'>•</span>
                        <textarea type='text' class='correct_answer' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                        <i class='fa-sharp fa-solid fa-trash'></i>
                    </div>";
                } else{
                    echo "<div class='answer'>
                        <span>•</span>
                        <textarea type='text' class='no_checked' data-letter='".$letter."' data-id='".$row['id_question']."' value='".$anwser."'>".$anwser."</textarea>
                        <i class='fa-sharp fa-solid fa-trash'></i>
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
            </div>";
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['reload_success'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $_POST['subject'],
                    "id" => $_POST['id']
                )
            );
        }else{
            add_log(
                $_SESSION['lang']['logs']['reports']['title'],
                $_SESSION['lang']['logs']['reports']['reload_error'],
                $_SESSION['email'],
                "../../logs/",
                array(
                    "subject" => $_POST['subject'],
                    "id" => $_POST['id']
                )
            );
        }
    }else{
        add_log(
            $_SESSION['lang']['logs']['reports']['title'],
            $_SESSION['lang']['logs']['reports']['reload_error'],
            $_SESSION['email'],
            "../../logs/"
        );
    }
    $conn->close();
}
?>