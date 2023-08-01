<?php
session_start();
require_once("../../db/connect.php");
$conn=mysqli_connect($servername,$username,$password,$dbname);
if ($conn->connect_error) {
    echo $_SESSION['lang']['database']['error'];
    exit();
}else{
    $conn->set_charset("utf8");
    $sql_subjects = "SELECT s.id, s.subject, s.share, s.term, s.loaded, COUNT(q.id) as question_count
    FROM subjects AS s
    LEFT JOIN questions AS q ON s.subject = q.subject
    GROUP BY s.id, s.subject, s.share, s.term, s.loaded
    UNION
    SELECT 0 as id, '~' as subject, 0 as share, term,
    GROUP_CONCAT(REPLACE(REPLACE(loaded , '}', '') , '{', '')) as loaded,
    SUM(total_count) AS total_count
        FROM (
            SELECT s.id, s.subject, s.share, s.term as term, s.loaded as loaded, COUNT(q.id) AS total_count
            FROM subjects AS s
            LEFT JOIN questions AS q ON s.subject = q.subject
            GROUP BY s.id, s.subject, s.share, s.term, s.loaded
        ) AS subquery
    GROUP BY term
    ORDER BY term DESC, subject ASC";
    if($result_subjects = mysqli_query($conn, $sql_subjects)){
        echo "<ul class='main_left_subjects_content_item_header'>
                    <li>".$_SESSION['lang']['admin']['main']['subjects']['subject']."</li>
                    <li>".$_SESSION['lang']['admin']['main']['subjects']['term']."</li>
                    <li></li>
                    <li>".$_SESSION['lang']['admin']['main']['subjects']['views']."</li>
                    <li>".$_SESSION['lang']['admin']['main']['subjects']['questions']."</li>
                </ul>";
        while ($row_subjects = mysqli_fetch_array($result_subjects)) {
            if($row_subjects['share']==1){
                $share_info = "✔️";
            }else{
                $share_info = "❌";
            }
            if($row_subjects['question_count']==1){
                $question_count = $row_subjects['question_count'];
            }else if($row_subjects['question_count']>=2 && $row_subjects['question_count']<=4){
                $question_count = $row_subjects['question_count'];
            }else{
                $question_count = $row_subjects['question_count'];
            }
            $views_count = 0;
            if($row_subjects['subject']=='~'){
                $views = $row_subjects['loaded'];
                $views = preg_replace('/\d{4}-\d{2}-\d{2}/', '', $views);
                $views = str_replace(['"',':'], ['',''], $views);
                $views_count = array_sum(array_map('intval',array_filter(explode(',', $views))));
                echo "<ul class='main_left_subjects_content_item_all'>
                    <li>".$_SESSION['lang']['admin']['main']['subjects']['all']."</li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['term']."'>".$row_subjects['term']."</li>
                    <li></li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['views']."' class='views_table'>".$views_count."</li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['questions']."' class='questions_table'>".$question_count."</li>
                </ul>";
            }else{
                $views = json_decode($row_subjects['loaded'], true);
                if($views != null){
                    foreach ($views as $view => $count) {
                        $views_count += $count;
                    }
                }
                echo "<ul class='main_left_subjects_content_item'>
                    <li>".$row_subjects['subject']."</li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['term']."'>".$row_subjects['term']."</li>
                    <li>".$share_info."</li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['views']."' class='views_table'>".$views_count."</li>
                    <li data-name='".$_SESSION['lang']['admin']['main']['subjects']['questions']."' class='questions_table'>".$question_count."</li>
                </ul>";
            }
        }
    }
    $conn->close();
}