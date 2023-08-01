<?php
session_start();
require_once("connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    if(!isset($_SESSION['code'])){
        $sql = "SELECT subjects.subject AS subject_name, subjects.term AS term, COUNT(questions.id_question) AS number_of_questions FROM subjects LEFT JOIN questions ON subjects.subject = questions.subject GROUP BY subjects.subject, subjects.term";
    }else{
        $sql = "SELECT term FROM codes WHERE code = '".$_SESSION['code']."'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $code = $row['term'];
        $sql = "SELECT subjects.subject AS subject_name, subjects.term AS term, COUNT(questions.id_question) AS number_of_questions FROM subjects LEFT JOIN questions ON subjects.subject = questions.subject WHERE subjects.term in (".$code.") GROUP BY subjects.subject, subjects.term";
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $subjects = array();
        $subjects_questions = array();
        if(!isset($_SESSION['code'])){
            while ($row = $result->fetch_assoc()) {
                $subject = $row['subject_name']." - ".$row['number_of_questions']." ".$_SESSION['lang']['quiz']['select_quiz']['question_text'];
                $term = (int) $row['term'];
                $question_count = (int) $row['number_of_questions'];
                $subjects[] = array($subject, $term);
                $subjects_questions[] = array($subject, $question_count);
            }
        }else{
            while ($row = $result->fetch_assoc()) {
                $subject = $row['subject_name'];
                $term = (int) $row['term'];
                $question_count = (int) $row['number_of_questions'];
                $subjects[] = array($subject, $term);
                $subjects_questions[] = array($subject, $question_count);
            }
        }
        usort($subjects, function ($a, $b) {
            return $b[1] <=> $a[1];
        });
        $response = array(
            'subjects' => $subjects,
            'subject_questions' => $subjects_questions
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        $response = array(
            'subjects' => array(),
            'subject_questions' => $_SESSION['lang']['quiz']['select_quiz']['question_no_amount']
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    $conn->close();
}
?>