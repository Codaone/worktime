<center>
<?php
// ####### List all to table ###########
$table 	= $conf['table'];

$loggedUserOnly = "user_id = '".$user->getUserId()."'";

$cond = getvar("showonly") && getvar("showonly") != "" ? " WHERE name='".getvar("showonly",true)."'".(!getvar("charged") ? " AND charged = 0" : "") : "";
$page = "";
if(!getvar("showonly") && !getvar("showall")){
	$page = getvar("page") || getvar("page") == 1 ? (getvar("page")-1) * 20 : 0;
	$page = $db->escape(" LIMIT ".$page.",20");
}
$rows 	= $db->get("SELECT * FROM `".$table."` ".(empty($cond) ? "WHERE " : $cond." AND ").$loggedUserOnly." ORDER BY `begin` DESC ,starttime DESC".$page);
$count 	= $db->rows($table);
$total 	= 0;
$days	= array();
?>
<br />
<form action='index.php?id=' method='get'>
<?php if($user->isLoggedIn()){ ?>
<div style="width:900px;">
	<a style="float:left;font-size:18px;" href="?page=<?=(getvar("page")===FALSE || getvar("page") <= 0 ? 1 : getvar("page")-1)?>">«</a>
	<a style="margin:0 auto;font-size:18px;text-decoration:none;" href="?showall=1">==</a>
	<a style="float:right;font-size:18px;" href=?page=<?=(getvar("page")===FALSE ? 2 : getvar("page")+1)?>>»</a>
</div>
<table id="front_table" class="table table-striped table-condensed table-hover" cellspacing="0">
	<thead>
<tr>
	<th><b>&nbsp;</b></th>
	<th><b>Date</b></th>
	<th><b>Name</b></th>
	<th><b>Description</b></th>
	<th><b>Start</b></th>
	<th><b>End</b></th>
	<th><b>Sum</b></th>
	<?php if($user->getUsername() == "admin") { ?>
	<th><b>Price</b></th>
	<th><b>Total</b></th>
	<?php } ?>
	<th><?=getvar("showonly") ? "<a href='?showonly=".getvar("showonly")."&charged=1'>Show charged also</a>" : ""?></th>
</tr>
</thead>
	<tbody>
<?php
$weeklyeffort = 0;
$weeklyprice = 0;
foreach($rows as $i=>$row)
{ 
	$id 	= $row['id'];
	$begin 	= $row['begin'];
	$start 	= $row['starttime'];
	$duration	= $row['duration'];
	$end 	= date("H:i", strtotime(date("d.m.Y", $row['begin'])." ".$start)+$duration);
	$name 	= $row['name'];
	$desc	= $row['description'];
	$price	= $row['price'];
	$chr	= $row['charged'];
	$count	= $i+1+20*(getvar("page")>0 ? getvar("page")-1 : 0) . ".";

	$effort = $duration / 3600;

	if($i>0) $last = $rows[$i-1]["begin"];
	if($i==0 || (date("W", $begin) != date("W", $last))){
		if($weeklyprice != 0 || $weeklyeffort != 0) {
			echo "<tr>".
					"<td colspan='7' style='text-align: right;'>".number_format($weeklyeffort,1,",","")."h</td>";
					if($user->getUsername() == "admin") {
						echo "<td style='white-space: nowrap;'>".number_format($weeklyprice/$weeklyeffort,1,",","")."€&#47;h</td>".
						"<td>".number_format($weeklyprice,1,",","")."€</td>";
					}
				echo "<td colspan='99'>&nbsp;</td>".
				 "</tr></tbody><tbody>";
		 }
		echo "<tr><td colspan='99' class='week'>Week ".date('W', $begin)."</td></tr>";
		$weeklyeffort = 0;
		$weeklyprice = 0;
	}
	$weeklyeffort += $effort;
	$weeklyprice += $price*$effort;
	
	echo "<tr class='data".($chr ? " chr" : "")."'>
			<td id='center'>".$count."</td>
			<td>" .date("d.m.Y l", $begin)."</td>
			<td><a href='?showonly=$name'>".$name."</a></td>
			<td>".nl2br($desc)."</td>
			<td>".$start."</td>
			<td>".$end."</td>
			<td>".number_format($effort, 1, ",","&nbsp;")."&nbsp;h</td>
		";
		if($user->getUsername() == "admin") {
			echo "<td>".number_format($price, 0, ",","&nbsp;")."&nbsp;€/h</td>
			<td>".number_format($duration/3600*$price, 0, ",","&nbsp;")."&nbsp;€</td>";
		}
		echo "<td id='center'>
				<a href='add.php?id=".$id."'>Edit</a>&nbsp;"
				.($chr || $user->getUsername() != "admin" ? "" : "<a href='charged.php?id=".$id."' onclick='if(!confirm(\"Are you sure you want to mark this as charged?\"))return false;'>Charg</a>&nbsp;").
				"<a href='?del=1&id=".$id."' onclick='if(!confirm(\"Are you sure you want to delete?\"))return false;'>Del</a>
			</td>";

	echo "</tr>"; 
	$total += $duration/60;
	if(!in_array(date("dmY",$begin),$days)) $days[date("dmY",$begin)] = 1;
} 
echo "<tr>".
		"<td colspan='7' style='text-align: right;'>".number_format($weeklyeffort,1,",","")."h</td>";
		if($user->getUsername() == "admin") {
			echo "<td>".number_format($weeklyprice/$weeklyeffort,1,",","")."€&#47;h</td>".
			"<td>".number_format($weeklyprice,1,",","")."€</td>";
		}
		echo "<td colspan='99'>&nbsp;</td>".
	 "</tr></tbody>";
		
$hours 	= floor($total / 60);
$mins 	= round(($total % 60),1);
$days 	= count($days);

$avgt	= $total / $days;
$avgh 	= floor($avgt / 60);
$avg 	= round(($avgt % 60),1);
if($user->getUsername() == "admin") {
	$data = $db->get("SELECT
		SUM(duration)/3600 'Total (h)',
		SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
		SUM(IF(charged, duration, 0))/3600 'Charged (h)',
		SUM(duration/3600*price) 'Total amount (€)',
		SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
		SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
		FROM `work` WHERE FROM_UNIXTIME(`begin`, '%Y') = '".date("Y")."' AND ".$loggedUserOnly);
} else {
	$data = $db->get("SELECT
		SUM(duration)/3600 'Total (h)',
		SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
		SUM(IF(charged, duration, 0))/3600 'Charged (h)'
		FROM `work` WHERE FROM_UNIXTIME(`begin`, '%Y') = '".date("Y")."' AND ".$loggedUserOnly);
}
echo "
	<tr align='left'>
		<td colspan='3'>";
		echo "<b>Stats for ".date("Y").":</b><br />";
foreach(reset($data) as $name => $value){
	echo $name.": <b>".number_format($value, 1, ","," ")."</b><br />";
}

echo "<td colspan='2'>";

if(getvar("showonly")){
	echo "<b>Stats for ".getvar("showonly").":</b><br />";
	if($user->getUsername() == "admin") {
		$data = $db->get("SELECT
			SUM(duration)/3600 'Total (h)',
			SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
			SUM(IF(charged, duration, 0))/3600 'Charged (h)',
			SUM(duration/3600*price) 'Total amount (€)',
			SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
		  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
		FROM `work`
		WHERE name = '".getvar("showonly")."'".
		" AND FROM_UNIXTIME(`begin`, '%Y') = '".date("Y")."' AND ".$loggedUserOnly
		);
	} else {
		$data = $db->get("SELECT
			SUM(duration)/3600 'Total (h)',
			SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
			SUM(IF(charged, duration, 0))/3600 'Charged (h)'
		FROM `work`
		WHERE name = '".getvar("showonly")."'".
		" AND FROM_UNIXTIME(`begin`, '%Y') = '".date("Y")."' AND ".$loggedUserOnly
		);

	}
	foreach(reset($data) as $name => $value){
		echo $name.": <b>".number_format($value, 1, ","," ")."</b><br />";
	}
	
	echo "</td><td colspan='99'>";
	if($user->getUsername() == "admin") {
		echo "<b>All stats for ".getvar("showonly").":</b><br />";
		$data = $db->get("SELECT
			SUM(duration)/3600 'Total (h)',
			SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
			SUM(IF(charged, duration, 0))/3600 'Charged (h)',
			SUM(duration/3600*price) 'Total amount (€)',
			SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
		  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
		FROM `work`
		WHERE name = '".getvar("showonly")."'".
		" AND FROM_UNIXTIME(`begin`, '%Y') = '".date("Y")."'");
		foreach(reset($data) as $name => $value){
			echo $name.": <b>".number_format($value, 1, ","," ")."</b><br />";
		}
	}
} else {
	echo "</td><td colspan='99'>";
}

echo "</td>
</td>
	</tr>
	<tr align='left'>
		<td colspan='99'>
			Total ".$days." workday(s) and <b>".$hours." h ".$mins." min</b><br>
			your sprint avarage is ".$avgh." h ".$avg." min
		</td>
	</tr>
</table>";
}
echo "</form>";
// ####### Delete ######
if(getvar('del') && getvar('del')!="succeed"){
	$table = $conf['table'];
	$id = getvar('id');
	if(strlen($id) && $id > 0){
		$delete = "DELETE FROM `".$table."` WHERE `id` = ".$db->escape($id)." AND ".$loggedUserOnly." LIMIT 1";
		$db->query($delete);
		echo "<meta http-equiv='refresh' content='0;url=index.php?del=succeed' />";
	} else {
		echo "<script>alert('Error in delete');</script>";
	}
}elseif(getvar('del')=="succeed"){
	echo '<p class="green">Delete succeeded!</p>';
}


?>
