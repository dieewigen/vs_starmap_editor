<?php
ignore_user_abort(true);

if(file_exists(__DIR__ . '/env.inc.php')){
	include 'env.inc.php';
}

if(!isset($db_host)){
	$db_host='localhost';
}

if(!isset($db_user)){
	$db_user='dbuser';
}

if(!isset($db_password)){
	$db_password='123456';
}

if(!isset($db_table)){
	$db_table='de';
}

$GLOBALS['dbi'] = mysqli_connect($db_host, $db_user, $db_password, $db_table) or die("Keine Verbindung zur Datenbank möglich.");
$GLOBALS['dbi']->set_charset("utf8");
?>