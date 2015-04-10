<?php
if(!$user->isLoggedIn() || $user->getUsername() != "admin"){
	header("Location: login.php");
	exit;
}
$table = $conf['table'];
if(getvar('id')){
	$id = getvar('id',true);
	$table = $conf['table'];
	// $row = $db->first("SELECT * FROM `".$table."` WHERE `id`= ".$id);
	$db->update($table, array("charged" => 1), "`id`='".$id."'");
}


header("Location: ".str_replace("charged.php","",$_SERVER["HTTP_REFERER"]));
