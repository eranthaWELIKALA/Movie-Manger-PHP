<?php
$db_host = "localhost";
$db_port = "3307";
$db_name = "moviemanager";
$db_user = "root";
$db_pass = "12345678";

/* Create mysql object */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
//$mysqli->set_charset("utf8");
GLOBAL $mysqli;

if(mysqli_connect_errno()){
    $out[] = array('status' => 'failed');
    echo json_encode($out);
	exit();
}


$out[] = array('status' => 'success');
echo json_encode($out);

/*create varible for sever IP*/
$severIp = "moviemanager.welikala.com";
GLOBAL $severIp;
$serverFolder = "./";
GLOBAL $serverFolder ;

?>
