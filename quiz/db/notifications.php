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
    if(isset($_POST['id'])){
        //reset all notifications
        if($_POST['id']=="all"){
            $sql="UPDATE notification SET textread=0 WHERE email='".$_SESSION['email']."'";
            if(mysqli_query($conn, $sql)){
                add_log(
                    $_SESSION['lang']['logs']['notification']['title'],
                    $_SESSION['lang']['logs']['notification']['all_success'],
                    $_SESSION['email'],
                    "../logs/"
                );
            }else{
                add_log(
                    $_SESSION['lang']['logs']['notification']['title'],
                    $_SESSION['lang']['logs']['notification']['all_error'],
                    $_SESSION['email'],
                    "../logs/"
                );
            }
        }
        //reload bell icon and notifications
        if($_POST['id']=="bellreload"){
            $sql_notifications = "SELECT count(*) FROM notification WHERE textread=1 AND email = '" . $_SESSION['email']."'";
            $result_notifications = mysqli_query($conn, $sql_notifications);
            $row_notifications = mysqli_fetch_array($result_notifications);
            $sql = "DELETE FROM notification WHERE textread=0 AND email = '" . $_SESSION['email']."'";
            mysqli_query($conn, $sql);
            if (@$row_notifications['count(*)']>0) {
                echo "<i data-count='".@$row_notifications['count(*)']."' class='fa-solid fa-bell bell_animation'></i>";
            } else {
                echo "<i class='fa-regular fa-bell'></i>";
            }
        //reset one notification
        }else{
            $sql="UPDATE notification SET textread=0 WHERE id='".$_POST['id']."' AND email='".$_SESSION['email']."'";
            if(mysqli_query($conn, $sql)){
                add_log(
                    $_SESSION['lang']['logs']['notification']['title'],
                    $_SESSION['lang']['logs']['notification']['one_success'],
                    $_SESSION['email'],
                    "../logs/"
                );
            }else{
                add_log(
                    $_SESSION['lang']['logs']['notification']['title'],
                    $_SESSION['lang']['logs']['notification']['one_error'],
                    $_SESSION['email'],
                    "../logs/"
                );
            }
        }
    }else if(isset($_POST['email'])){
        if(isset($_POST['remove'])){
            //remove all notifications from one user
            if($_POST['remove'] == 'all'){
                $sql = "DELETE FROM notification WHERE textread=1 AND email = '" . $_POST['email']."'";
                if(mysqli_query($conn, $sql)){
                    add_log(
                        $_SESSION['lang']['logs']['notification']['title'],
                        $_SESSION['lang']['logs']['notification']['all_success']." ".$_SESSION['lang']['logs']['notification']['by_admin'],
                        $_SESSION['email'],
                        "../logs/",
                        $_POST['email']
                    );
                }else{
                    add_log(
                        $_SESSION['lang']['logs']['notification']['title'],
                        $_SESSION['lang']['logs']['notification']['all_error']." ".$_SESSION['lang']['logs']['notification']['by_admin'],
                        $_SESSION['email'],
                        "../logs/",
                        $_POST['email']
                    );
                }
            //remove one notification from one user
            } else {
                $sql = "DELETE FROM notification WHERE textread=1 AND id='".$_POST['remove']."' AND email='".$_POST['email']."'";
                if(mysqli_query($conn, $sql)){
                    add_log(
                        $_SESSION['lang']['logs']['notification']['title'],
                        $_SESSION['lang']['logs']['notification']['one_success']." ".$_SESSION['lang']['logs']['notification']['by_admin'],
                        $_SESSION['email'],
                        "../logs/",
                        $_POST['email']
                    );
                }else{
                    add_log(
                        $_SESSION['lang']['logs']['notification']['title'],
                        $_SESSION['lang']['logs']['notification']['one_error']." ".$_SESSION['lang']['logs']['notification']['by_admin'],
                        $_SESSION['email'],
                        "../logs/",
                        $_POST['email']
                    );
                }
            }
        }else{
            echo "<a class='close_score_show'></a>";
            echo "<ul class='notification_menu'>";
            $sql="SELECT * FROM notification WHERE email='".$_POST['email']."' AND textread=1 ORDER BY id DESC";
            $result=mysqli_query($conn, $sql);
            if(mysqli_num_rows($result)>0){
                echo "<li><p class='clear_all_notification'>".$_SESSION['lang']['quiz']['notification']['clear']." (".mysqli_num_rows($result).")</p></li>";
                while($row=mysqli_fetch_assoc($result)){
                    echo "<li>
                    <a><p>".$row['title']."</p>".$row['text']."</a>
                    <i data-id='".$row['id']."' class='fa-solid fa-flag'></i></li>";
                }
            }else{
                echo "<li><a><p>".$_SESSION['lang']['quiz']['notification']['empty']."</p></a></li>";
            }
            echo "</ul>";
        }
    }else{
        $sql="SELECT * FROM notification WHERE email='".$_SESSION['email']."' AND textread=1 ORDER BY id DESC";
        $result=mysqli_query($conn, $sql);
        if(mysqli_num_rows($result)>0){
            echo "<li><p class='clear_all_notification'>".$_SESSION['lang']['quiz']['notification']['clear']."</p></li>";
            while($row=mysqli_fetch_assoc($result)){
                echo "<li>
                <a><p>".$row['title']."</p>".$row['text']."</a>
                <i data-id='".$row['id']."' class='fa-solid fa-flag'></i></li>";
            }
        }else{
            echo "<li><a><p>".$_SESSION['lang']['quiz']['notification']['empty']."</p></a></li>";
        }
    }
    $conn->close();
}