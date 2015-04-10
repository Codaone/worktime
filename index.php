<?php

require_once "library/mysql.class.php";
require_once "library/user.class.php";
require_once "library/functions.php";
require_once "library/mylist.php";


$indexUrl = "http://".$_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF'])."/";

$file = getvar('file') ? getvar('file') : "default.php";

if($user->isLoggedIn()){
	if(getvar('logout') != false){
		$user->logout();
		header("Location: ".$indexUrl);
		die();
	}
} else {
	$file = "login.php";
}

$onlyAdmin = array("stats.php", "others.php", "users.php", "summary.php", "excel.php");
if(!file_exists($file) || ($user->getUsername() != "admin" && in_array($file, $onlyAdmin)) || strpos($file, "/")!==false){
	header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}
if($file == "excel.php") {
	include $file;
	die();
}

addHeader("js","js/jquery.js");
addHeader("js","js/jquery-ui.min.js");
addHeader("js","js/jquery-ui-i18n.min.js");
addHeader("css","js/jquery-ui.min.css");
addHeader("css","js/jquery-ui.theme.min.css");
addHeader("css","css/bootstrap.min.css");
addHeader("css","css/css.css");
addHeader("title", "Work Time Management");

ob_start();
	include($file);;
	$value = ob_get_contents();
ob_end_clean();

include("inc/header.php");

echo $value;

include("inc/footer.php");

$db->close();

?>
