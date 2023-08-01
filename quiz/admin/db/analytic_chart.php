<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    //analytic chart in admin panel for single user
    if(isset($_POST['email'])){
        $email = $_POST['email'];
        $sql = "SELECT * FROM scores WHERE email='".$email."' AND end_date > DATE_SUB(NOW(), INTERVAL 45 DAY)";
        if($result = $conn->query($sql)){
            $response = array(
                'avg_score' => [],
                'subject' => []
            );
            $subject = [];
            $avg_score = [];
            foreach ($result as $row) {
                $subjectName = $row['subject'];
                $score = $row['score'];
                $all_questions = $row['question_count'];
                if (isset($subject[$subjectName])) {
                    $subject[$subjectName] += 1;
                    $avg_score[$subjectName] += round(($score/$all_questions)*100,2);
                } else {
                    $subject[$subjectName] = 1;
                    $avg_score[$subjectName] = round(($score/$all_questions)*100,2);
                }
            }
            foreach ($subject as $subjectName => $count) {
                $average = $avg_score[$subjectName] / $count;
                array_push($response['subject'], $subjectName);
                array_push($response['avg_score'], round($average,2));
            }
            echo json_encode($response);
        }
    }else{
        //analytic chart in admin panel for all quiz views
        if(isset($_POST['term']) && $_POST['term'] != null){
            $sql = "SELECT subject, loaded FROM subjects WHERE term = '".$_POST['term']."' AND loaded <> ''";
        }else{
            $sql = "SELECT subject, loaded FROM subjects WHERE term = (SELECT MAX(term) FROM subjects) AND loaded <> ''";
        }
        if($result = $conn->query($sql)){
            $response = array(
                'labels' => [],
                'datas' => [
                    'label' => [],
                    'data' => []
                ]
            );
            foreach ($result as $row) {
                $labels = json_decode($row['loaded'], true);
                $labels = array_keys($labels);
                array_push($response['labels'], $labels);
            }
            $response['labels'] = call_user_func_array('array_merge', $response['labels']);
            $response['labels'] = array_unique($response['labels']);
            sort($response['labels']);
            foreach ($result as $row) {
                array_push($response['datas']['label'], $row['subject']);
                $label_temp = json_decode($row['loaded'], true);
                $label = array_fill(0, count($response['labels']), 0);
                foreach ($label_temp as $key => $value) {
                    $index = array_search($key, $response['labels']);
                    if ($index !== false) {
                        $label[$index] = $value;
                    }
                }
                array_push($response['datas']['data'], $label);
            }
            echo json_encode($response);
        }
    }
    $conn->close();
}