<?php
session_start();
$file_href = "../active.json";
if(!isset($_SESSION['email']) || !isset($_POST['status'])){
    exit();
}
if(!file_exists($file_href)){
    $active = array();
    $active[] = array(
        'email' => $_SESSION['email'],
        'status' => $_POST['status'],
        'time' => date('Y-m-d H:i:s', time())
    );
    $fp = fopen($file_href, 'w');
    fwrite($fp, json_encode($active, JSON_PRETTY_PRINT));
    fclose($fp);
}else{
    $active = json_decode(file_get_contents($file_href), true);
    $found = false;
    foreach($active as $key => $value){
        if($value['email'] == $_SESSION['email']){
            $found = true;
            $active[$key]['status'] = $_POST['status'];
            $active[$key]['time'] = date('Y-m-d H:i:s', time());
            break;
        }
    }
    if(!$found){
        $active[] = array(
            'email' => $_SESSION['email'],
            'status' => $_POST['status'],
            'time' => date('Y-m-d H:i:s', time())
        );
    }
    $fp = fopen($file_href, 'w');
    fwrite($fp, json_encode($active, JSON_PRETTY_PRINT));
    fclose($fp);
}
?>