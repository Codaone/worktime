<?php

// include the php-excel class
require (dirname (__FILE__) . "/library/class_xls.php");

// create a dummy array
$j=1;
$k=0;;
$xls = new Excel_XML;
$doc = array (
	$j => array (
		" ", 
		"Pvm.", 
		"Aloitus", 
		"Lopetus", 
		"Kesto", 
		"Nimi", 
		"Kuvaus"
	)
);
$xls->addArray($doc);
$cond = getvar("showonly") && getvar("showonly") != "" ? " WHERE name='".getvar("showonly",true)."'".(!getvar("charged") ? " AND charged = 0" : "") : "";

$rows = $db->get("SELECT * FROM work $cond ORDER BY begin DESC");
foreach($rows as $row){
	$begin 	= $row['begin'];
	$start 	= $row['starttime'];
	$duration	= $row['duration'];
	$end 	= date("H:i", strtotime(date("d.m.Y", $row['begin'])." ".$start)+$duration);
	$name 	= $row['name'];
	$desc	= $row['description'];
	$price	= $row['price'];
	$chr	= $row['charged'];

	$j++;
	$k++;
	$doc = array (
		$j => array(
			$k.".", 
			date("d.m.Y", $begin), 
			$start, 
			$end, 
			($duration/3600)." h", 
			$name, 
			$desc
		)   
	);
	$xls->addArray($doc);
}

// generate excel file
$xls->generateXML("Tyotunnit");

die();
?>