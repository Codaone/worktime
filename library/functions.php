<?php
require("config.php");

// ######## Connect to MySQL ###########
$db = new Database($conf['server'], $conf['user'], $conf['pass'], $conf['db']);
$db->connect();
$user = new User($db);


$debugdata = array();
function debug($value,$function = false){
	if($function){
		if($function == "p") $function = 'print_r($value);';
		if($function == "d") $function = 'var_dump($value);';
		$function = str_replace("$","\$",$function);
		ob_start();
			eval($function);
			$value = ob_get_contents();
		ob_end_clean();
	}
	$value = str_replace(array("\n","\r","\t","\0","'"),array("\\n","\\r","\\t","null","\'"),$value);
	global $debugdata;
	$debugdata[] = $value;
}

$header = "";
$title = "Tyotuntienhallinta";
$othervars = array();
function addHeader($type, $fileortext){
	global $header;
	switch($type){
		case "css":
			if(is_file($fileortext)){
				$header .= '<link rel="stylesheet" type="text/css" href="'.$fileortext.'" />'."\n";
			} else {
				$header .= '<style>'.$fileortext."</style>";
			}
			break;
		case "js":
			if(is_file($fileortext)){
				$header .= '<script type="text/javascript" src="'.$fileortext.'"></script>'."\n";
			} else {
				$header .= "<script type='text/javascript'>".$fileortext."</script>";
			}
			break;
		case "title":
			$title = $fileortext;
			break;
	}
}
function getvar($name,$escape = false, $default = false){
	global $othervars,$db;
	$vars = array_merge($othervars,$_POST,$_GET);
	if(isset($vars[$name])){
		$temp = $vars[$name];
		if($escape) $temp = $db->escape($temp);
		return $temp;
	}
	else {
		return $default;
	}
}

function selectOpt($array){
	$html = "";
	foreach($array as $val){
		$html .= "<option>$val</option>";
	}
	return $html;
}

?>
