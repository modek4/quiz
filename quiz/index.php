<?php
session_start();
if($_SESSION['logined']==false){
    header("Location: login");
}
if (!is_writable(session_save_path())) {
    echo 'Session path "'.session_save_path().'" is not writable for PHP!';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']['language'] ?>">
    <head>
    <title><?php echo $_SESSION['config']['main_title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo $_SESSION['config']['description']; ?>"/>
    <meta name="author" content="<?php echo $_SESSION['config']['author']; ?>"/>
    <meta name="copyright" content="Copyright Kacper Grodzki 2022-now"/>
    <meta name="robots" content="nofollow"/>
    <meta name="referrer" content="no-referrer">
    <meta name="referrer" content="never">
    <meta name="google-site-verification" content="<?php echo $_SESSION['config']['google_site_verification']; ?>"/>
    <meta http-equiv="expires" content="43200"/>
    <meta property="og:site_name" content="<?php echo $_SESSION['config']['site_name']; ?>" />
    <meta property="og:title" content="<?php echo $_SESSION['config']['site_name']; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo $_SESSION['config']['site_url']; ?>" />
    <meta property="og:image" content="<?php echo $_SESSION['config']['site_banner']; ?>" />
    <meta property="og:description" content="<?php echo $_SESSION['config']['description']; ?>" />
    <?php echo $_SESSION['config']['favicons']; ?>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="<?php echo $_SESSION['config']['fontawesome_token']; ?>" crossorigin="anonymous"></script>
    <?php
    if($_SESSION['dark']==1){
      echo "<script>sessionStorage.setItem(\"dark\", \"1\");</script>";
    }else{
      echo "<script>sessionStorage.setItem(\"dark\", \"0\");</script>";
    }
    @require_once("log.php");
    try {
      @require_once("db/connect.php");
      $conn = mysqli_connect($servername, $username, $password, $dbname);
      if (!$conn) {
        add_log(
          $_SESSION['lang']['logs']['database']['title'],
          $_SESSION['lang']['logs']['database']['error'],
          $_SESSION['email'],
          "./logs/"
        );
        throw new Exception($_SESSION['lang']['database']['error']);
      }
      if(!isset($_SESSION['email'])){
        $_SESSION['email'] = "N/A";
        header("Location: ./check_answer.php");
        exit();
      }
      $conn->set_charset("utf8");
      $sql = "SELECT count(*) FROM notification WHERE textread=1 AND email = '".$_SESSION['email']."'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $count_notifications = $row['count(*)']>99 ? "99+" : $row['count(*)'];
      echo "<script>var lang_text = ".json_encode($_SESSION['lang']).";</script>";
      if ((isset($_SESSION['admin']) && $_SESSION['admin']==true) || (isset($_SESSION['mod']) && $_SESSION['mod']==true)) {
        echo "<link rel='stylesheet' type='text/css' href='./admin/style.css'>";
      }else{
        $_SESSION['admin']=false;
        $_SESSION['mod']=false;
      }
    } catch (Exception $e) {
      add_log(
        $_SESSION['lang']['logs']['database']['title'],
        $_SESSION['lang']['logs']['database']['error'],
        $_SESSION['email'],
        "./logs/"
      );
      echo $_SESSION['lang']['database']['error'];
      exit();
    } catch (Error $e) {
      add_log(
        $_SESSION['lang']['logs']['database']['title'],
        $_SESSION['lang']['logs']['database']['error'],
        $_SESSION['email'],
        "./logs/"
      );
      echo $_SESSION['lang']['database']['error'];
      exit();
    }
    ?>
    </head>
    <?php
    if ($_SESSION['admin']==true || $_SESSION['mod']==true) {
      echo "<body class='no-scroll'>";
    }else{
      echo "<body class='no-scroll' oncontextmenu='return false'>";
    }
    if(@$_SESSION['question_relaunch'] == 1){
      $search_pattern_file = "./analytic/";
      $matching_files = scandir($search_pattern_file);
      array_splice($matching_files, 0, 2);
      $matching_files = preg_grep("/".$_SESSION['code']."_score/", $matching_files);
      if($matching_files){
        $file_name = $search_pattern_file.$matching_files[2];
        foreach($matching_files as $file){
          if(strpos($file, $_SESSION['code']."_score")!==false){
            $file_name = $search_pattern_file.$file;
          }
        }
        $file_read = null;
        if(file_exists($file_name)){
          $file = fopen($file_name, "r");
          $file_read = json_decode(fread($file, filesize($file_name)), true);
          fclose($file);
        }
        if($file_read=="[]" || $file_read==null || $file_read=='' || $file_read==false || $file_read==[]){
          unlink($file_name);
        }else{
          $progress_save_session = 0;
          $progress_save_session_all = 0;
          foreach($file_read as $question){
            if($question['answers']!=0){
              $progress_save_session++;
            }
            $progress_save_session_all++;
          }
          $progress_save_session_all = $progress_save_session_all == 0 ? 1 : $progress_save_session_all;
          $progress_save_session = round($progress_save_session/$progress_save_session_all*100, 2);
          echo "<div class='load_session_quiz'>
            <div class='load_session_quiz_content'>
              <h3>Czy chcesz wznowić quiz:<br/><span id='session_quiz_name' data-code='".$_SESSION['code']."'>".$file_read[0]['subject']."</span></h3>
              <p>Postęp: ".$progress_save_session."%</p>
              <div class='load_session_quiz_content_buttons'>
                <button class='load_session_quiz_content_yes'>Tak</button>
                <button class='load_session_quiz_content_no'>Nie</button>
              </div>
            </div>
          </div>";
        }
      }
    }
    ?>
    <div id="menu">
      <ul class="notification_list">
        <li class="notification">
          <a data-toggle="notification" class="notification_bell" id="notification_bell">
            <?php
            if ($count_notifications>0) {
              echo "<i data-count=\"".$count_notifications."\" class='fa-solid fa-bell bell_animation'></i>";
            } else {
              echo "<i class='fa-regular fa-bell'></i>";
            }
            ?>
          </a>
          <ul class="notification_menu"></ul>
        </li>
        <?php
        if ($_SESSION['mod']==true || $_SESSION['admin']==true) {
          echo "
          <li class='admin'>
            <a data-toggle='admin' class='admin_icon' id='admin_icon'>
              <i class='fa-solid fa-screwdriver-wrench'></i>
            </a>
            <div class='admin_menu'>
              <div class='admin_menu_close'>";
              if($_SESSION['mod']){
                echo "<span>Moderator panel</span>";
              }else if($_SESSION['admin']){
                echo "<span>Admin panel</span>";
              }
                echo "<i class='fa-solid fa-xmark'></i>
              </div>
              <div class='admin_menu_content'></div>
            </div>
          </li>";
        }
        ?>
        <li class="notification_background"></li>
      </ul>
      <ul class="navbar_list">
        <li class="score" id="show_score_button"><i class="fa-solid fa-table-list"></i></li>
        <li class="settings" id="show_settings_button"><i class="fa-solid fa-gear"></i></li>
        <li class="darkmode"><i class="fa-solid fa-lightbulb"></i></li>
        <li class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i></li>
      </ul>
      <div class="settings_menu"></div>
      <div class="select_subject">
        <div class="select_subject_wrapper">
          <div class="select_subject_select_btn">
            <span><?php echo $_SESSION['lang']['quiz']['select_quiz']['text']; ?></span>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <div class="select_subject_content">
            <div class="select_subject_search">
              <input spellcheck="false" type="text" placeholder="Szukaj">
            </div>
            <ul class="select_subject_options"></ul>
          </div>
        </div>
        <div class="select_subject_wrapper select_subject_numberOfQuestions">
          <span><?php echo $_SESSION['lang']['quiz']['select_quiz']['number_of_questions']; ?></span>
          <div class="select_subject_select_btn radio_buttons">
            <input type="radio" id="numberOfQuestions_5" name="numberOfQuestions" value="1">
            <input type="radio" id="numberOfQuestions_10" name="numberOfQuestions" value="10">
            <input type="radio" id="numberOfQuestions_20" name="numberOfQuestions" value="20">
            <input type="radio" id="numberOfQuestions_40" name="numberOfQuestions" value="40">
            <input type="radio" id="numberOfQuestions_free" name="numberOfQuestions" value="">
          </div>
          <div class="select_subject_select_btn radio_buttons_text">
              <span>1+</span>
              <span>10</span>
              <span>20</span>
              <span>40</span>
              <span id='load_all_questions'>???</span>
            </div>
          <div class="select_subject_select_btn select_subject_numberOfQuestions_free">
            <span><?php echo $_SESSION['lang']['quiz']['select_quiz']['number_of_questions']; ?></span>
            <input spellcheck="false" type="number" id="custom_numberOfQuestions"step='1' min='0' max='0' value=''>
            <p class='select_subject_numberOfQuestions_max'></p>
          </div>
        </div>
        <div class="select_subject_wrapper select_subject_submit">
          <span><?php echo $_SESSION['lang']['quiz']['select_quiz']['start_quiz']; ?></span>
        </div>
      </div>
      <div class="quiz_score">
        <span class='quiz_score_item_1'><i class="fa-regular fa-clock"></i> <span id="quiz_timer">00:00:00</span></span>
        <span id="save_score" class="save_score_animation quiz_score_item_2"><i class="fa-regular fa-floppy-disk"></i></span>
        <span class='quiz_score_item_3'><span id="score_quiz">0.00</span><span> / </span><span id="score_quiz_all">0.00</span></span>
      </div>
      <div id="show_table"></div>
      <div id="close_save">
        <img src="../assets/arrow.svg" alt="arrow close icon">
      </div>
    </div>
    <div id="quiz_load"></div>
  </body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="script.js"></script>
<script src="notify.js"></script>
<?php
if ($_SESSION['admin']==true || $_SESSION['mod']==true) {
  echo "<script src='./admin/admin.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js'></script>";
}else{
  echo "<script>
  console.log('%cLEAVE ME HERE', 'color: red; font-size: 12em;');
  document.addEventListener('contextmenu', (e) => e.preventDefault());
  function ctrlShiftKey(e, keyCode) {
    return e.ctrlKey && e.shiftKey && e.keyCode === keyCode.charCodeAt(0);
  }
  document.onkeydown = (e) => {
    if (
      event.keyCode === 123 ||
      ctrlShiftKey(e, 'I') ||
      ctrlShiftKey(e, 'J') ||
      ctrlShiftKey(e, 'C') ||
      (e.ctrlKey && e.keyCode === 'U'.charCodeAt(0))
    )
    return false;
  };
  </script>";
}
$conn->close();
?>
</html>