<?php
session_start();
require_once("db/connect.php");
require_once("log.php");
$connect=mysqli_connect($servername,$username,$password,$dbname);
if ($connect->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    if(isset($_POST['email_register']) && isset($_POST['password_register']) && isset($_POST['code_register'])){
        $email=$_POST['email_register'];
        $password=$_POST['password_register'];
        $code=$_POST['code_register'];
        $is_valid = true;
        $is_valid_array = array();
        $result=mysqli_query($connect, "SELECT * FROM quiz_users WHERE email='$email'");
        if(mysqli_num_rows($result)>0){
            array_push($is_valid_array, "email");
            $is_valid = false;
            add_log(
                $_SESSION['lang']['logs']['register']['title'],
                $_SESSION['lang']['logs']['register']['same_email'],
                $email,
                "./logs/"
            );
        }
        $result=mysqli_query($connect, "SELECT * FROM codes WHERE code='$code'");
        if(mysqli_num_rows($result)==0){
            array_push($is_valid_array, "nokey");
            $is_valid = false;
            add_log(
                $_SESSION['lang']['logs']['register']['title'],
                $_SESSION['lang']['logs']['register']['no_code'],
                $email,
                "./logs/"
            );
        }
        $result=mysqli_query($connect, "SELECT * FROM codes WHERE code='$code' and code_use=0");
        if(mysqli_num_rows($result)>0){
            array_push($is_valid_array, "nocode");
            $is_valid = false;
            add_log(
                $_SESSION['lang']['logs']['register']['title'],
                $_SESSION['lang']['logs']['register']['same_code'],
                $email,
                "./logs/"
            );
        }
        if($is_valid){
            $password_hash=password_hash($password, PASSWORD_DEFAULT);
            $result=mysqli_query($connect, "INSERT INTO quiz_users VALUES ('NULL', '$email', '$password_hash', '0', '$code', NOW(), 'online')");
            $result_code=mysqli_query($connect, "UPDATE codes SET code_use='0' WHERE code='$code'");
            if($result && $result_code){
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
                $devices_info = $result." | ".$ip;
                $email_devices = $email;
                $date_last_login = date("Y-m-d H:i:s");
                $sql = "INSERT INTO `devices` VALUES ('NULL', '$devices_info', '1', '$email_devices','$date_last_login')";
                $connect->query($sql);
                $date_notify = date("Y-m-d H:i");
                $sql = "INSERT INTO `notification` VALUES ('NULL', '$email', '".$_SESSION['lang']['notification']['register_title']."', '".$_SESSION['lang']['notification']['register_text']."<br/>$date_notify', '1')";
                $connect->query($sql);
                $_SESSION['logined'] = true;
                $_SESSION['device'] = $devices_info;
                $_SESSION['open'] = 1;
                $_SESSION['status'] = "online";
                $_SESSION['email'] = $email;
                $_SESSION['dark'] = 0;
                $_SESSION['code'] = $code;
                $_SESSION['admin'] = false;
                $_SESSION['mod'] = false;
                $_SESSION['question_order'] = 0;
                $_SESSION['question_analytic'] = 1;
                $_SESSION['question_relaunch'] = 1;
                add_log(
                    $_SESSION['lang']['logs']['register']['title'],
                    $_SESSION['lang']['logs']['register']['success'],
                    $email,
                    "./logs/"
                );
                unset($_SESSION['error_login']);
                header('Location: ../quiz');
                exit();
            }else{
                echo "error";
                exit();
            }
        }else{
            echo json_encode($is_valid_array);
            exit();
        }
    }
    $connect->close();
}
?>