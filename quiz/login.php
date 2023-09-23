<?php
session_start();
if ((isset($_SESSION['logined'])) && ($_SESSION['logined']==true))
{
	header('Location: ../quiz');
    exit();
}
require_once("db/connect.php");
require_once("log.php");
$_SESSION['config'] = include('config.php');
$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$preferred_language = '';
if (isset($accept_language)) {
    if(strpos($accept_language, "-") !== false){
        $preferred_language = explode('-',trim(explode(',', $accept_language)[0]))[0];
    }else if(strpos($accept_language, ";") !== false){
        $preferred_language = explode(';',trim(explode(',', $accept_language)[0]))[0];
    }else if(strpos($accept_language, ",") !== false){
        $preferred_language = explode(',', $accept_language)[0];
    }else{
        $preferred_language = $accept_language;
    }
}
$files = scandir("./lang");
foreach($files as $file){
    if(strpos($file, ".json") !== false){
        $lang = explode(".", $file)[0];
        if($lang == $preferred_language){
            $lang = $preferred_language;
            break;
        }else{
            $lang = "en";
        }
    }
}
$_SESSION['lang'] = json_decode(file_get_contents("lang/".$lang.".json"), true);
$lang_text = $_SESSION['lang']['login'];
$log_text = $_SESSION['lang']['logs']['login'];
echo "<script>var lang_text = ".json_encode($lang_text['texts']).";</script>";
if ((isset($_POST['email_login'])) && (isset($_POST['password_login']))){
	$conn = new mysqli($servername,$username,$password,$dbname);
	if ($conn->connect_errno!=0){
		echo "Error: ".$conn->connect_errno;
	}else{
        $conn->set_charset("utf8");
		$user_password = $_POST['password_login'];
		$user_email = htmlentities($_POST['email_login'], ENT_QUOTES, "UTF-8");
		if ($result = @$conn->query(
		sprintf("SELECT * FROM `quiz_users` WHERE email='%s'",
		mysqli_real_escape_string($conn, $user_email)))){
			if($result->num_rows==1){
                $row_login = $result->fetch_assoc();
                if(password_verify($user_password,$row_login['password'])){
                    if($row_login['dark'] == -1){
                        $_SESSION['error_login'] = '<span style="color:red">'.$lang_text['texts']['account_blocked'].'</span>';
                        add_log(
                            $log_text['title'],
                            $log_text['account_blocked'],
                            $user_email,
                            "./logs/"
                        );
                        header('Location: login');
                        exit();
                    }else{
                        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
                        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
                            $ip = $_SERVER['HTTP_X_REAL_IP'];
                        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                            $ip = $_SERVER['HTTP_CLIENT_IP'];
                        } else {
                            $ip = $_SERVER['REMOTE_ADDR'];
                        }
                        $agent_mappings = array(
                            'Windows NT 10.0' => 'Windows 10',
                            'Windows NT 6.3'  => 'Windows 8.1',
                            'Windows NT 6.2'  => 'Windows 8',
                            'Windows NT 6.1'  => 'Windows 7',
                            'Windows NT 6.0'  => 'Windows Vista',
                            'Windows NT 5.1'  => 'Windows XP',
                            'Windows NT 5.0'  => 'Windows 2000',
                            'Android' => array(
                                'samsung'    => 'Samsung',
                                'xiaomi'     => 'Xiaomi',
                                'huawei'     => 'Huawei',
                                'oppo'       => 'Oppo',
                                'vivo'       => 'Vivo',
                                'realme'     => 'Realme',
                                'oneplus'    => 'OnePlus',
                                'asus'       => 'Asus',
                                'sony'       => 'Sony',
                                'nokia'      => 'Nokia',
                                'lg'         => 'LG',
                                'htc'        => 'HTC',
                                'lenovo'     => 'Lenovo',
                                'motorola'   => 'Motorola',
                                'google'     => 'Google',
                                'blackberry' => 'BlackBerry',
                                'acer'       => 'Acer',
                                'alcatel'    => 'Alcatel',
                                'amazon'     => 'Amazon',
                                'archos'     => 'Archos',
                                'benq'       => 'BenQ',
                                'xiaoxin'    => 'Xiaoxin',
                                'zopo'       => 'ZOPO',
                                'meizu'      => 'Meizu',
                                'lenovo'     => 'Lenovo',
                                'sharp'      => 'Sharp',
                                'tecno'      => 'Tecno',
                                'leeco'      => 'LeEco',
                                'gionee'     => 'Gionee',
                                'infinix'    => 'Infinix',
                                'panasonic'  => 'Panasonic',
                                'wiko'       => 'Wiko',
                                'micromax'   => 'Micromax',
                                'blu'        => 'BLU',
                                'verykool'   => 'Verykool',
                                'mobiistar'  => 'Mobiistar',
                                'vodafone'   => 'Vodafone',
                                'meitu'      => 'Meitu',
                                'zuk'        => 'ZUK',
                                'vsmart'     => 'Vsmart',
                                'energizer'  => 'Energizer',
                                'fairphone'  => 'Fairphone',
                                'essential'  => 'Essential',
                                'honor'      => 'Honor',
                                'tcl'        => 'TCL',
                                'toshiba'    => 'Toshiba',
                                'zte'        => 'ZTE',
                                'cat'        => 'CAT',
                                'landvo'     => 'Landvo',
                                'bluboo'     => 'Bluboo',
                                'doogee'     => 'Doogee',
                                'elephone'   => 'Elephone',
                                'homtom'     => 'HomTom',
                                'umidigi'    => 'UMIDIGI',
                                'vernee'     => 'Vernee',
                                'wileyfox'   => 'Wileyfox',
                                'xolo'       => 'Xolo',
                                'yota'       => 'Yota',
                                'zte'        => 'ZTE',
                                'maxwest'    => 'Maxwest',
                                'aligator'   => 'Alligator',
                                'plum'       => 'Plum',
                                'microsoft'  => 'Microsoft',
                                'fujitsu'    => 'Fujitsu',
                                'sharp'      => 'Sharp',
                                'vaio'       => 'VAIO',
                                'hisense'    => 'Hisense',
                                'symphony'   => 'Symphony',
                                'tecno'      => 'Tecno',
                                'maxcom'     => 'Maxcom',
                                'gigabyte'   => 'Gigabyte',
                                'yandex'     => 'Yandex',
                                'hp'         => 'HP',
                                'lenovo'     => 'Lenovo',
                                'zte'        => 'ZTE',
                                'acool'      => 'ACOOL',
                                'blackshark' => 'Black Shark',
                                'nubia'      => 'Nubia',
                                'realme'     => 'Realme',
                                'infinix'    => 'Infinix',
                                'palm'       => 'Palm',
                                'redmagic'   => 'RedMagic',
                                'sugar'      => 'Sugar',
                                'vernee'     => 'Vernee',
                                'bq'         => 'BQ',
                                'irbis'      => 'Irbis',
                                'maxcom'     => 'Maxcom',
                                'neffos'     => 'Neffos',
                                'wiko'       => 'Wiko',
                                'umidigi'    => 'UMIDIGI',
                                'alcatel'    => 'Alcatel',
                                'casio'      => 'Casio',
                                'cherry'     => 'Cherry',
                                'crosscall'  => 'Crosscall',
                                'cubot'      => 'Cubot',
                                'dexp'       => 'DEXP',
                                'digma'      => 'Digma',
                                'elephone'   => 'Elephone',
                                'energizer'  => 'Energizer',
                                'evolveo'    => 'Evolveo',
                                'fairphone'  => 'Fairphone',
                                'freetel'    => 'Freetel',
                                'garmin'     => 'Garmin',
                                'geecoo'     => 'Geecoo',
                                'gigabyte'   => 'Gigabyte',
                                'gigaset'    => 'Gigaset',
                                'google'     => 'Google',
                                'hammer'     => 'Hammer',
                                'hp'         => 'HP',
                                'htc'        => 'HTC',
                                'inew'       => 'iNew',
                                'jolla'      => 'Jolla',
                                'karbonn'    => 'Karbonn',
                                'kazam'      => 'Kazam',
                                'leagoo'     => 'Leagoo',
                                'leeco'      => 'LeEco',
                                'leotec'     => 'Leotec',
                                'maxwest'    => 'Maxwest',
                                'medion'     => 'Medion',
                                'meitu'      => 'Meitu',
                                'meizu'      => 'Meizu',
                                'micromax'   => 'Micromax',
                                'microsoft'  => 'Microsoft',
                                'mobiistar'  => 'Mobiistar',
                                'myphone'    => 'MyPhone',
                                'nec'        => 'NEC',
                                'nexian'     => 'Nexian',
                                'nokia'      => 'Nokia',
                                'nubia'      => 'Nubia',
                                'nuu'        => 'NUU',
                                'oneplus'    => 'OnePlus',
                                'onn'        => 'Onn',
                                'oukitel'    => 'Oukitel',
                                'palm'       => 'Palm',
                                'panasonic'  => 'Panasonic',
                                'plum'       => 'Plum',
                                'posh'       => 'Posh',
                                'qmobile'    => 'QMobile',
                                'razer'      => 'Razer',
                                'redmagic'   => 'RedMagic',
                                'ruggear'    => 'RugGear',
                                'sagem'      => 'Sagem',
                                'sendo'      => 'Sendo',
                                'sharp'      => 'Sharp',
                                'siemens'    => 'Siemens',
                                'silentcircle' => 'Silent Circle',
                                'sirinlabs'  => 'SIRIN LABS',
                                'spice'      => 'Spice',
                                'tecno'      => 'Tecno',
                                'thl'        => 'THL',
                                'toshiba'    => 'Toshiba',
                                'tp-link'    => 'TP-Link',
                                'ulefone'    => 'Ulefone',
                                'umi'        => 'UMI',
                                'vertu'      => 'Vertu',
                                'verykool'   => 'Verykool',
                                'vsmart'     => 'Vsmart',
                                'vodafone'   => 'Vodafone',
                                'voto'       => 'Voto',
                                'walton'     => 'Walton',
                                'wileyfox'   => 'Wileyfox',
                                'xgody'      => 'Xgody',
                                'yota'       => 'Yota',
                                'zopo'       => 'ZOPO',
                                'zte'        => 'ZTE',
                                'zuk'        => 'ZUK',
                                'zuum'       => 'Zuum'
                            ),
                            'iPhone'            => 'iPhone',
                            'iPad'              => 'iPad',
                            'Apple TV'          => 'Apple TV',
                            'Apple Watch'       => 'Apple Watch',
                            'Macintosh'         => 'Macintosh',
                            'SmartTV'           => array(
                                'samsung'       => 'Samsung Smart TV',
                                'lg'            => 'LG Smart TV',
                                'sony'          => 'Sony Smart TV'
                            ),
                            'Linux'             => array(
                                'Ubuntu'        => 'Ubuntu Linux',
                                'Debian'        => 'Debian Linux',
                                'Fedora'        => 'Fedora Linux',
                                'Red Hat'       => 'Red Hat Linux',
                                'CentOS'        => 'CentOS Linux',
                                'Mint'          => 'Linux Mint',
                                'Arch'          => 'Arch Linux'
                            )
                        );
                        $result_agent = 'Unknown';
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        foreach ($agent_mappings as $key => $value) {
                            if (strpos($user_agent, $key) !== false) {
                                if (is_array($value)) {
                                    foreach ($value as $subKey => $subValue) {
                                        if (strpos(strtolower($user_agent), $subKey) !== false) {
                                            $result_agent = $subValue;
                                            break 2;
                                        }
                                    }
                                } else {
                                    $result_agent = $value;
                                    break;
                                }
                            }
                        }
                        $devices_info = $result_agent." | ".$ip;
                        $email_devices = $row_login["email"];
                        $devices_correct = false;
                        $sql = "SELECT * FROM `devices` WHERE `email`='$email_devices'";
                        $result_devices = $conn->query($sql);
                        $date_last_login = date("Y-m-d H:i:s");
                        if ($result_devices->num_rows > 0) {
                            $sql = "UPDATE `devices` SET `open`='0' WHERE `email`='$email_devices'";
                            $conn->query($sql);
                            foreach ($result_devices as $row) {
                                if ($row['udevices'] == $devices_info) {
                                    $sql = "UPDATE `devices` SET `open`='1', `last_login`='$date_last_login' WHERE `email`='$email_devices' AND `udevices`='$devices_info'";
                                    $conn->query($sql);
                                    $devices_correct = true;
                                }
                            }
                        } else {
                            $sql = "INSERT INTO `devices` VALUES ('NULL', '$devices_info', '1', '$email_devices', '$date_last_login')";
                            $conn->query($sql);
                            $devices_correct = true;
                        }
                        if($devices_correct == false){
                            $sql = "INSERT INTO `devices` VALUES ('NULL', '$devices_info', '1', '$email_devices','$date_last_login')";
                            $conn->query($sql);
                            $sql = "INSERT INTO `notification` VALUES ('NULL', '".$row_login['email']."', '".$lang_text['texts']['new_login_notification']."', '".$lang_text['texts']['new_login_device_notification_text']."<br/>$date_last_login', '1')";
                            $conn->query($sql);
                        }
                        $sql = "UPDATE `quiz_users` SET `last_login`=NOW() WHERE `id`=".$row_login['id'];
                        $conn->query($sql);
                        $_SESSION['logined'] = true;
                        $_SESSION['id'] = $row_login['id'];
                        $_SESSION['device'] = $devices_info;
                        $_SESSION['open'] = 1;
                        $_SESSION['status'] = "online";
                        $_SESSION['email'] = $row_login['email'];
                        $_SESSION['dark'] = $row_login['dark'];
                        $_SESSION['code'] = $row_login['code'];
                        $_SESSION['last_login'] = $row_login['last_login'];
                        $result->free_result();
                        $sql = "SELECT * FROM `codes` WHERE `code`='".$row_login['code']."'";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        $_SESSION['question_order'] = $row['question_order'];
                        $_SESSION['question_analytic'] = $row['question_analytic'];
                        $_SESSION['question_relaunch'] = $row['question_relaunch'];
                        $result->free_result();
                        $sql = "SELECT count(*), moderator FROM `users` WHERE `email`='".$_SESSION['email']."' GROUP BY moderator";
                        $result = $conn->query($sql);
                        if($result->num_rows == 1){
                            $row = $result->fetch_assoc();
                            if($row['moderator'] == 1){
                                $_SESSION['mod'] = true;
                                $_SESSION['admin'] = false;
                            }else if($row['moderator'] == 0){
                                $_SESSION['admin'] = true;
                                $_SESSION['mod'] = false;
                            }else{
                                $_SESSION['admin'] = false;
                                $_SESSION['mod'] = false;
                            }
                        }
                        unset($_SESSION['error_login']);
                        add_log(
                            $log_text['title'],
                            $log_text['user_login'],
                            $user_email,
                            "./logs/"
                        );
                        $result->free_result();
                        if(isset($_SESSION['email']) && $_SESSION['email'] != ""){
                            header('Location: ../quiz');
                            exit();
                        }else{
                            $_SESSION['error_login'] = '<span style="color:red">'.$lang_text['texts']['incorrect_password'].'</span>';
                            add_log(
                                $log_text['title'],
                                $log_text['incorrect_login'],
                                $user_email,
                                "./logs/"
                            );
                            header('Location: login');
                        }
                    }
                }else{
                    $_SESSION['error_login'] = '<span style="color:red">'.$lang_text['texts']['incorrect_password'].'</span>';
                    add_log(
                        $log_text['title'],
                        $log_text['incorrect_login'],
                        $user_email,
                        "./logs/"
                    );
				    header('Location: login');
                }
			} else {
				$_SESSION['error_login'] = '<span style="color:red">'.$lang_text['texts']['incorrect_password'].'</span>';
                add_log(
                    $log_text['title'],
                    $log_text['incorrect_login'],
                    $user_email,
                    "./logs/"
                );
				header('Location: login');
			}
		}
		$conn->close();
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']['language'] ?>">
<head>
    <title><?php echo $_SESSION['config']['login_title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo $_SESSION['config']['description']; ?>"/>
    <meta name="author" content="<?php echo $_SESSION['config']['author']; ?>"/>
    <meta name="copyright" content="Copyright Kacper Grodzki 2022-now"/>
    <meta name="robots" content="nofollow"/>
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
</head>
<body id="login">
    <?php
    $conn = new mysqli($servername,$username,$password,$dbname);
	if ($conn->connect_errno!=0){
		echo "";
	}else{
        $sql = "SELECT count(*) FROM questions";
        $result = mysqli_query($conn, $sql);
        $all_count = mysqli_fetch_array($result);
        $all_count = $all_count['count(*)'];
        $conn->close();
    }
    if($all_count){
        echo "<div class='counter_questions_login' data-count=".$all_count.">0</div>";
    }else{
        echo "<div class='counter_questions_login' data-count='0'>0</div>";
    }
    ?>
    <section class="login_wrapper">
        <ul class="tabs">
            <li class="active"><?php echo $lang_text['login_nav_text']; ?></li>
            <li><?php echo $lang_text['register_nav_text']; ?></li>
            <li><?php echo $lang_text['faq_nav_text']; ?></li>
            <li style="display:none"><?php echo $lang_text['reset_password_nav_text']; ?></li>
        </ul>
        <ul class="tab_content">
            <li class="active">
                <div class="content_wrapper">
                    <form method="POST" action="" id="login_form">
                        <label><?php echo $lang_text['login_menu']['email']; ?></label>
                        <input type="text" name="email_login">
                        <label><?php echo $lang_text['login_menu']['password']; ?></label>
                        <div class="password_con">
                        <input id="pass1" type="password" name="password_login">
                        <span class="password_eye" onclick="showPass('pass1')">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        </div>
                        <div class="reset_password">
                            <a><?php echo $lang_text['login_menu']['forgot_password']; ?></a>
                        </div>
                        <?php
                        isset($_SESSION['error_login']) ? $_SESSION['error_login'] : $_SESSION['error_login'] = null;
                        echo "<label>".$_SESSION['error_login']."</label>";
                        ?>
                        <input class='submit_form_button' type="submit" value="<?php echo $lang_text['login_menu']['login_text']; ?>" name="<?php echo $lang_text['login_menu']['login_text']; ?>">
                    </form>
                </div>
            </li>
            <li>
                <div class="content_wrapper">
                    <form method="POST" action="" id="register_form">
                        <label><?php echo $lang_text['register_menu']['email']; ?></label>
                        <input type="text" name="email_register">
                        <div class="error"><p id="email_text"></p></div>
                        <label><?php echo $lang_text['register_menu']['password']; ?></label>
                        <div class="password_con">
                        <input id="pass2" type="password" name="password_register">
                        <span class="password_eye" onclick="showPass('pass2')">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        </div>
                        <div class="error"><p id="password_text"></p></div>
                        <label><?php echo $lang_text['register_menu']['repeat_password']; ?></label>
                        <div class="password_con">
                        <input id="pass3" type="password" name="repassword_register">
                        <span class="password_eye" onclick="showPass('pass3')">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        </div>
                        <div class="error"><p id="repassword_text"></p></div>
                        <label><?php echo $lang_text['register_menu']['activate_code']; ?>*</label>
                        <input type="text" name="code_register">
                        <div class="error"><p id="code_text"></p></div>
                        <label>*<?php echo $lang_text['register_menu']['register_sub_text']; ?></label>
                        <input class='submit_form_button' type="submit" value="<?php echo $lang_text['register_menu']['register_text']; ?>" name="<?php echo $lang_text['register_menu']['register_text']; ?>">
                    </form>
                </div>
            </li>
            <li>
                <div class="content_wrapper">
                    <ul id="faq_list">
                        <li class="border"><?php echo $lang_text['faq']['about']; ?></li>
                        <li><p><?php echo $lang_text['faq']['about_text']; ?></p></li>
                        <li class="border"><?php echo $lang_text['faq']['how_it_works']; ?></li>
                        <li><p><?php echo $lang_text['faq']['how_it_works_text']; ?></p></li>
                        <li class="border"><?php echo $lang_text['faq']['where_to_see']; ?></li>
                        <li><p><?php echo $lang_text['faq']['where_to_see_text']; ?> <a href="https://github.com/modek4/quiz">(Link)</a></p></li>
                        <li class="border"><?php echo $lang_text['faq']['access']; ?></li>
                        <li><p><?php echo $lang_text['faq']['access_text']; ?></p></li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="content_wrapper">
                    <form method="POST" action="" id="reset_form">
                        <label><?php echo $lang_text['reset_password']['email']; ?></label>
                        <input type="text" name="email_reset">
                        <div class="error"><p id="email_reset_text"></p></div>
                        <label><?php echo $lang_text['reset_password']['activate_code']; ?></label>
                        <input type="text" name="code_reset">
                        <div class="error"><p id="code_reset_text"></p></div>
                        <label><?php echo $lang_text['reset_password']['new_password']; ?></label>
                        <div class="password_con">
                        <input id="pass2_reset" type="password" name="password_reset">
                        <span class="password_eye" onclick="showPass('pass2_reset')">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        </div>
                        <div class="error"><p id="password_reset_text"></p></div>
                        <label><?php echo $lang_text['reset_password']['repeat_password']; ?></label>
                        <div class="password_con">
                        <input id="pass3_reset" type="password" name="repassword_reset">
                        <span class="password_eye" onclick="showPass('pass3_reset')">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        </div>
                        <div class="error"><p id="repassword_reset_text"></p></div>
                        <input class='submit_form_button' type="submit" value="<?php echo $lang_text['reset_password']['reset_password_text']; ?>" name="<?php echo $lang_text['reset_password']['reset_password_text']; ?>">
                    </form>
                </div>
            </li>
        </ul>
    </section>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script>
$('.counter_questions_login').each(function() {
    var $this = $(this), count_to = $this.attr('data-count');
    var count_speed = 0;
    if(count_to*10 < 1000) {
        count_speed = 1000;
    } else if (count_to*10 > 10000){
        count_speed = 10000;
    } else{
        count_speed = count_to*10;
    }
    $({ count_num: $this.text()}).animate({
        count_num: count_to
    },
    {
        duration: count_speed,
        easing:'swing',
        step: function() {
            $this.text(Math.floor(this.count_num));
        },
        complete: function() {
            $this.text(this.count_num);
        }
    });
});
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
}
$(document).ready(function(){
    $(".reset_password a").click(function(){
        $(".tabs > li").eq(3).trigger("click");
    });
    var clicked_tab = $(".tabs > .active");
    var tab_wrapper = $(".tab_content");
    var active_tab = tab_wrapper.find(".active");
    var active_tab_height = active_tab.outerHeight();
    active_tab.show();
    tab_wrapper.height(active_tab_height);
    $(".tabs > li").on("click", function() {
        if($(this).hasClass("active")){
            return;
        }
        $(".tabs > li").removeClass("active");
        $(this).addClass("active");
        clicked_tab = $(".tabs .active");
        active_tab.fadeOut(250, function() {
            $(".tab_content > li").removeClass("active");
            var clicked_tab_index = clicked_tab.index();
            $(".tab_content > li").eq(clicked_tab_index).addClass("active");
            active_tab = $(".tab_content > .active");
            active_tab_height = active_tab.outerHeight();
            tab_wrapper.stop().delay(50).animate({
                height: active_tab_height
            }, 500, function() {
                active_tab.delay(50).fadeIn(250);
            });
        });
    });
});
$(document).ready(function(){
    $('#reset_form').submit(function(e) {
        if($('#reset_form input[name="email_reset"]').val() == '' || $('#pass2_reset').val() == '' || $('#pass3_reset').val() == '' || $('#reset_form input[name="code_reset"]').val() == ''){
            return false;
        }
        e.preventDefault();
        var formDataReset = new FormData(this);
        var validCorrectReset = true;
        var pass2Reset = $('#pass2_reset').val();
        var pass3Reset = $('#pass3_reset').val();
        if(pass2Reset.match(/[!@#$%^&*(),.?":{}|<>]/)){
            if(pass2Reset.match(/[0-9]/)){
                if(pass2Reset.match(/[A-Z]/)){
                    if(pass2Reset.match(/[a-z]/)){
                        if(pass2Reset.length >= 8){
                            $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                            document.getElementById("password_reset_text").innerHTML = "";
                            if(pass2Reset != pass3Reset){
                                $('#reset_form input[name="repassword_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                                document.getElementById("repassword_reset_text").innerHTML = lang_text['password_not_match'];
                                validCorrectReset = false;
                            } else{
                                $('#reset_form input[name="repassword_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                                document.getElementById("repassword_reset_text").innerHTML = "";
                            }
                        }else{
                            $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                            document.getElementById("password_reset_text").innerHTML = lang_text['password_length'];
                            validCorrectReset = false;
                        }
                    }else{
                        $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                        document.getElementById("password_reset_text").innerHTML = lang_text['password_uppercase_length_not_match'];
                        validCorrectReset = false;
                    }
                }else{
                    $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("password_reset_text").innerHTML = lang_text['password_uppercase'];
                    validCorrectReset = false;
                }
            }else{
                $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                document.getElementById("password_reset_text").innerHTML = lang_text['password_uppercase_length_not_match'];
                validCorrectReset = false;
            }
        }else{
            $('#reset_form input[name="password_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
            document.getElementById("password_reset_text").innerHTML = lang_text['password_uppercase_length_not_match'];
            validCorrectReset = false;
        }
        var email = $('#reset_form input[name="email_reset"]').val();
        if(email.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)){
            $('#reset_form input[name="email_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
            document.getElementById("email_reset_text").innerHTML = "";
        }else{
            $('#reset_form input[name="email_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
            document.getElementById("email_reset_text").innerHTML = lang_text['email_not_valid'];
            validCorrectReset = false;
        }
        if(!validCorrectReset){
            return false;
        }
        $.ajax({
            url: 'reset_password.php',
            type: 'POST',
            data: formDataReset,
            success: function(data){
                var dataValid = data;
                if(dataValid == ""){
                    dataValid = "error";
                }
                $('#reset_form input[name="code_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                document.getElementById("code_reset_text").innerHTML = "";
                $('#reset_form input[name="email_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                document.getElementById("email_reset_text").innerHTML = "";
                if(dataValid == "error"){
                    $('#reset_form input[name="email_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("email_reset_text").innerHTML = lang_text['no_valid_data'];
                    $('#reset_form input[name="code_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("code_reset_text").innerHTML = lang_text['no_valid_data'];
                }
                if(dataValid == "emailnocode"){
                    $('#reset_form input[name="code_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("code_reset_text").innerHTML = lang_text['email_not_valid_no_code'];
                    $('#reset_form input[name="email_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("email_reset_text").innerHTML = lang_text['email_not_valid_no_code'];
                }
                if(dataValid == "success"){
                    $('#reset_form input[name="repassword_reset"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                    document.getElementById("repassword_reset_text").innerHTML = "<font color='green'>"+ lang_text['password_change_success'] +"</font>";
                }
            },
            processData: false,
            contentType: false
        });
    });
    $('#register_form').submit(function(e) {
        if($('#register_form input[name="email_register"]').val() == '' || $('#pass2').val() == '' || $('#pass3').val() == '' || $('#register_form input[name="code_register"]').val() == ''){
            return false;
        }
        e.preventDefault();
        var formData = new FormData(this);
        var validCorrect = true;
        var pass2 = $('#pass2').val();
        var pass3 = $('#pass3').val();
        if(pass2.match(/[!@#$%^&*(),.?":{}|<>]/)){
            if(pass2.match(/[0-9]/)){
                if(pass2.match(/[A-Z]/)){
                    if(pass2.match(/[a-z]/)){
                        if(pass2.length >= 8){
                            $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                            document.getElementById("password_text").innerHTML = "";
                            if(pass2 != pass3){
                                $('#register_form input[name="repassword_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                                document.getElementById("repassword_text").innerHTML = lang_text['password_not_match'];
                                validCorrect = false;
                            } else{
                                $('#register_form input[name="repassword_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                                document.getElementById("repassword_text").innerHTML = "";
                            }
                        }else{
                            $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                            document.getElementById("password_text").innerHTML = lang_text['password_length'];
                            validCorrect = false;
                        }
                    }else{
                        $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                        document.getElementById("password_text").innerHTML = lang_text['password_uppercase_length_not_match'];
                        validCorrect = false;
                    }
                }else{
                    $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("password_text").innerHTML = lang_text['password_uppercase'];
                    validCorrect = false;
                }
            }else{
                $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                document.getElementById("password_text").innerHTML = lang_text['password_uppercase_length_not_match'];
                validCorrect = false;
            }
        }else{
            $('#register_form input[name="password_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
            document.getElementById("password_text").innerHTML = lang_text['password_uppercase_length_not_match'];
            validCorrect = false;
        }
        var email = $('#register_form input[name="email_register"]').val();
        if(email.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)){
            $('#register_form input[name="email_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
            document.getElementById("email_text").innerHTML = "";
        }else{
            $('#register_form input[name="email_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
            document.getElementById("email_text").innerHTML = lang_text['email_not_valid'];
            validCorrect = false;
        }
        if(!validCorrect){
            return false;
        }
        $.ajax({
            url: 'register.php',
            type: 'POST',
            data: formData,
            success: function(data){
                try {
                    var tablica = JSON.parse(data);
                } catch (error) {
                    console.log("Login");
                    window.location.href = "../quiz";
                }
                var emailValid = false;
                var codeValid = false;
                var nocodeValid = false;
                tablica.forEach(function(element) {
                    if(element === "email"){
                        emailValid = true;
                    }
                    if(element === "nokey"){
                        codeValid = true;
                    }
                    if(element === "nocode"){
                        nocodeValid = true;
                    }
                });
                if(emailValid){
                    $('#register_form input[name="email_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("email_text").innerHTML = lang_text['email_occupied'];
                }else{
                    $('#register_form input[name="email_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                    document.getElementById("email_text").innerHTML = "";
                }
                if(codeValid){
                    $('#register_form input[name="code_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("code_text").innerHTML = lang_text['not_valid_code'];
                }
                if(nocodeValid){
                    $('#register_form input[name="code_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid red');
                    document.getElementById("code_text").innerHTML = lang_text['code_used'];
                }
                if(!codeValid && !nocodeValid){
                    $('#register_form input[name="code_register"]').css('border-bottom', 'calc(var(--padding-min)/8) solid green');
                    document.getElementById("code_text").innerHTML = "";
                }
            },
            processData: false,
            contentType: false
        });
    });
    $('#login_form').submit(function(e) {
        if($('#login_form input[name="email_login"]').val() == '' || $('#pass1').val() == ''){
            return false;
        }
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: formData,
            success: function(data){
                window.location.href = "../quiz";
            },
            processData: false,
            contentType: false
        });
    });
});
function showPass(e){
    var x = document.getElementById(e);
    var icon = document.querySelector('#' + e + ' ~ .password_eye i');
    if (x.type === "password") {
        x.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        x.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
dayNight();
function dayNight(){
    var date = new Date();
    var hour = date.getHours();
    var month = date.getMonth() + 1;
    let minH = 0;
    let maxH = 0;
    if(month == 1 || month == 2 || month == 12){
        minH = 7;
        maxH = 18;
    } else if(month == 3 || month == 4 || month == 5){
        minH = 6;
        maxH = 20;
    } else if(month == 6 || month == 7 || month == 8){
        minH = 5;
        maxH = 21;
    } else if(month == 9 || month == 10 || month == 11){
        minH = 6;
        maxH = 19;
    }
    if(hour >= minH && hour < maxH){
        document.body.classList.remove("dark-mode");
        document.body.classList.add("light-mode");
    }else{
        document.body.classList.remove("light-mode");
        document.body.classList.add("dark-mode");
    }
}
</script>
</html>