<?php
// Change this to your servername
$servername = '';
// Change this to your username
$username = '';
// Change this to your password
$password= '';
// Change this to your database name
$dbname = '';
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception($_SESSION['lang']['database']['error']);
    }
    $conn->close();
} catch (Exception $e) {
    die("<div style='width:100%;height:100vh;display:flex;justify-content:center;align-items:center;font-size:clamp(1.2rem, 2vh, 8rem);'><span>".$_SESSION['lang']['database']['error']."</span></div>");
}
?>