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
                $string = $_SERVER['HTTP_USER_AGENT'];
                $first_index = strpos($string, '(');
                $last_index = strpos($string, ')');
                if ($first_index !== false && $last_index !== false) {
                    $result = substr($string, $first_index + 1, $last_index - $first_index - 1);
                    $result = trim($result);
                } else {
                    $result ="";
                }
                if(strpos($result, "Windows") !== false){
                    if(strpos($result, "Windows NT 10.0") !== false){
                        $result = "Windows 10";
                    }elseif(strpos($result, "Windows NT 6.3") !== false){
                        $result = "Windows 8.1";
                    }elseif(strpos($result, "Windows NT 6.2") !== false){
                        $result = "Windows 8";
                    }elseif(strpos($result, "Windows NT 6.1") !== false){
                        $result = "Windows 7";
                    }elseif(strpos($result, "Windows NT 6.0") !== false){
                        $result = "Windows Vista";
                    }elseif(strpos($result, "Windows NT 5.1") !== false){
                        $result = "Windows XP";
                    }elseif(strpos($result, "Windows NT 5.0") !== false){
                        $result = "Windows 2000";
                    }else{
                        $result = "Windows";
                    }
                }elseif(strpos($result, "Android") !== false){
                    if(strpos(strtolower($result), "samsung") !== false){
                        $result = "Samsung";
                    }elseif(strpos(strtolower($result), "xiaomi") !== false){
                        $result = "Xiaomi";
                    }elseif(strpos(strtolower($result), "huawei") !== false){
                        $result = "Huawei";
                    }elseif(strpos(strtolower($result), "oppo") !== false){
                        $result = "Oppo";
                    }elseif(strpos(strtolower($result), "vivo") !== false){
                        $result = "Vivo";
                    }elseif(strpos(strtolower($result), "realme") !== false){
                        $result = "Realme";
                    }elseif(strpos(strtolower($result), "oneplus") !== false){
                        $result = "OnePlus";
                    }elseif(strpos(strtolower($result), "asus") !== false){
                        $result = "Asus";
                    }elseif(strpos(strtolower($result), "sony") !== false){
                        $result = "Sony";
                    }elseif(strpos(strtolower($result), "nokia") !== false){
                        $result = "Nokia";
                    }elseif(strpos(strtolower($result), "lg") !== false){
                        $result = "LG";
                    }elseif(strpos(strtolower($result), "htc") !== false){
                        $result = "HTC";
                    }elseif(strpos(strtolower($result), "lenovo") !== false){
                        $result = "Lenovo";
                    }elseif(strpos(strtolower($result), "motorola") !== false){
                        $result = "Motorola";
                    }elseif(strpos(strtolower($result), "google") !== false){
                        $result = "Google";
                    }elseif(strpos(strtolower($result), "blackberry") !== false){
                        $result = "BlackBerry";
                    }elseif(strpos(strtolower($result), "acer") !== false){
                        $result = "Acer";
                    }elseif(strpos(strtolower($result), "alcatel") !== false){
                        $result = "Alcatel";
                    }elseif(strpos(strtolower($result), "amazon") !== false){
                        $result = "Amazon";
                    }elseif(strpos(strtolower($result), "archos") !== false){
                        $result = "Archos";
                    }elseif(strpos(strtolower($result), "asus") !== false){
                        $result = "Asus";
                    }elseif(strpos(strtolower($result), "benq") !== false){
                        $result = "BenQ";
                    }else{
                        $result = "Android";
                    }
                }elseif(strpos($result, "iPhone") !== false){
                    $result = "iPhone";
                }elseif(strpos($result, "iPad") !== false){
                    $result = "iPad";
                }elseif(strpos($result, "Macintosh") !== false){
                    $result = "Macintosh";
                }else{
                    $result = "Unknown";
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
                add_log(
                    $_SESSION['lang']['logs']['register']['title'],
                    $_SESSION['lang']['logs']['register']['success'],
                    $email,
                    "./logs/"
                );
                unset($_SESSION['blad_login']);
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