<?php
function add_log($title, $text, $user, $destination="", $data = null){
    $date = date("Y-m-d");
    $file_path = $destination.$date.".json";
    if (file_exists($file_path) && filesize($file_path) > 0) {
        $file_content = file_get_contents($file_path);
        $json = json_decode($file_content, true);
    } else {
        $json = [];
    }
    if($data == null){
        $json[] = [
            "id" => count($json),
            "title" => $title,
            "text" => $text,
            "user" => $user,
            "date" => date("Y-m-d H:i:s")
        ];
    }else{
        $json[] = [
            "id" => count($json),
            "title" => $title,
            "text" => $text,
            "data" => $data,
            "user" => $user,
            "date" => date("Y-m-d H:i:s")
        ];
    }
    $json = array_map("unserialize", array_unique(array_map("serialize", $json)));
    $file = fopen($file_path, "w");
    fwrite($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fclose($file);
}
if(isset($_POST['title']) && isset($_POST['text']) && isset($_POST['user']) && isset($_POST['destination']) && isset($_POST['data'])){
    add_log($_POST['title'], $_POST['text'], $_POST['user'], $_POST['destination'], $_POST['data']);
}
?>