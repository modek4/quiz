<?php
	session_start();
	require_once("../admin/db/connect.php");
	require_once("log.php");
	$conn = new mysqli($servername,$username,$password,$dbname);
	if ($conn->connect_errno!=0){
		echo "Error: ".$conn->connect_errno;
	}else{
		$sql = "UPDATE devices SET open=0 WHERE email='".$_SESSION['email']."' AND udevices='".$_SESSION['device']."'";
		$conn->query($sql);
		$conn->close();
	}
	add_log(
		$_SESSION['lang']['logs']['logout']['title'],
		$_SESSION['lang']['logs']['logout']['user_logout'],
		$_SESSION['email'],
		"./logs/"
	);
	session_unset();
	header('Location: ../quiz');
?>