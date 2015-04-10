<?php
/**
 * Created to Tyotunni
 * Copyright: Codaone Oy
 * Created by: Juhani
 * Date: 15.7.2014 10:41
 */


$unCharged = $db->get("
	SELECT
		`name` 'Customer',
		SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
		SUM(IF(charged, 0, duration))/3600 'Uncharged (h)'
	FROM `work`
	GROUP BY `name`
	HAVING SUM(IF(charged, 0, duration))>0
");

if(isset($_POST["action"])) {
	$customer = $_POST["name"];
	$amount = str_replace(",",".",$_POST["amount"]);
	$markAll = false;
	foreach($unCharged as $row) {
		if($row["Customer"] == $customer) {
			if($row["Uncharged (h)"] == $amount) {
				$markAll = true;
			}
			break;
		}
	}
	if($markAll) {
		$db->query("UPDATE work SET charged = 1 WHERE name = '".$db->escape($customer)."'");
	} else {
		$cust = $db->get("
			SELECT
				id, duration/3600 'dur'
			FROM `work`
			WHERE charged = 0 AND `name` = '".$db->escape($customer)."'
			ORDER BY `begin` ASC, starttime ASC
		");
		foreach($cust as $row) {
			$tmp = $amount-$row["dur"];
			if($tmp > 0) {
				$db->update('work', array("charged" => 1), "id = '".$db->escape($row["id"])."'");
				$amount -= $row["dur"];
			} else {
				break;
			}
		}
	}
	header("Location: stats.php");
	die();
}

echo "<div style='margin: 0 auto;width: 960px'>";
echo "<br /><h2 style='text-align: center;'>Statistics</h2><br />";
$list = new myList();
$list->setTitle("Uncharged");
$first = reset($unCharged);

$list->setColumns(count($first)+1);
foreach($first as $name => $value) {
	$name = str_replace(" ", "&nbsp;", ucfirst($name));
	if(is_numeric($value)) {
		$list->addHeaderCol($name, 1, array("style" => "text-align:right;"));
	} else {
		$list->addHeaderCol($name);
	}
}
$list->addHeaderCol("Mark charged", 1, array("style" => "text-align:center;"));
$columns = array_fill(0, count($first)+1, 0);
foreach($unCharged as $row){
	$i = 0;
	foreach($row as $key => $col) {
		$attr = array();
		if($key == "Customer"){
			$col = "<a href='summary.php?showonly=$col&showall=1'>$col</a>";
		} elseif(is_numeric($col)) {
			$columns[$i] += $col;
			$col = number_format($col, 1, ",", " ");
			$attr["style"] = "text-align:right;";
		}
		$list->addCol($col, $attr);
		$i++;
	}
	$list->addCol("<form method='post'><input type='hidden' name='name' value='".HTMLENTS($row["Customer"])."' />
				<input type='text' name='amount' required='required' placeholder='Hours' style='text-align: center;width: 100px' />&nbsp;
				<input type='submit' name='action' value='Do it' /></form>",
		array("style" => "text-align:center;"));
}
foreach ($columns as $col) {
	$attr = array();
	if($col > 0) {
		$col           = number_format($col, 1, ",", " ");
		$attr["style"] = "text-align:right;font-weight:bold;";
	} else {
		$col = "";
	}
	$list->addFooterCol($col, 1, $attr);
}

echo $list->out();
echo "<p><i>If mark charged is not all hours it will fill until amount is under the amount and leave the last one marked.</i></p>";

echo "<br />";
$data = $db->get("SELECT
	'Total' as 'Month',
	SUM(duration)/3600 'Total (h)',
	SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
	SUM(IF(charged, duration, 0))/3600 'Charged (h)',
	SUM(duration/3600*price) 'Total amount (€)',
	SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
FROM `work`");

$list = new myList();
$list->setTitle("Total");
if(count($data)) {
	setdata($data, $list);
}
$data = $db->get("SELECT
	MONTHNAME(FROM_UNIXTIME(`begin`)) 'Month',
	SUM(duration)/3600 'Total (h)',
	SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
	SUM(IF(charged, duration, 0))/3600 'Charged (h)',
	SUM(duration/3600*price) 'Total amount (€)',
	SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
FROM `work`
WHERE FROM_UNIXTIME(`begin`) BETWEEN DATE_FORMAT(NOW() - INTERVAL 3 MONTH, '%Y-%m-01') AND NOW()
GROUP BY FROM_UNIXTIME(`begin`, '%m')
ORDER BY begin DESC
");
if(count($data)) {
	setdata($data, $list, true);
}
echo $list->out();

echo "<br />";
$data = $db->get("SELECT
	u.name 'Name',
	SUM(duration)/3600 'Total (h)',
	SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
	SUM(IF(charged, duration, 0))/3600 'Charged (h)',
	SUM(duration/3600*price) 'Total amount (€)',
	SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
FROM `work` w
JOIN users u ON u.id = w.user_id GROUP BY user_id");

$list = new myList();
$list->setTitle("User");
if(count($data)) {
	setdata($data, $list);
}

$data = $db->get("SELECT
	Concat(u.name, ' - ', MONTHNAME(FROM_UNIXTIME(`begin`))),
	SUM(duration)/3600 'Total (h)',
	SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
	SUM(IF(charged, duration, 0))/3600 'Charged (h)',
	SUM(duration/3600*price) 'Total amount (€)',
	SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
FROM `work` w
JOIN users u ON u.id = w.user_id
WHERE FROM_UNIXTIME(`begin`) BETWEEN DATE_FORMAT(NOW() - INTERVAL 3 MONTH, '%Y-%m-01') AND NOW()
GROUP BY FROM_UNIXTIME(`begin`, '%m'), user_id
ORDER BY `begin` DESC, u.name");

if(count($data)) {
	setdata($data, $list, true);
}

echo $list->out();
//foreach(reset($data) as $name => $value){
//	echo $name.": <b>".number_format($value, 1, ","," ")."</b><br />";
//}
echo "<br />";
$data = $db->get("SELECT
	`name` 'Customer',
	SUM(duration)/3600 'Total (h)',
	SUM(IF(charged, 0, duration))/3600 'Uncharged (h)',
	SUM(IF(charged, duration, 0))/3600 'Charged (h)',
	SUM(duration/3600*price) 'Total amount (€)',
	SUM(IF(charged, 0, duration/3600*price)) 'Uncharged amount (€)',
  SUM(IF(charged, duration/3600*price, 0)) 'Charged amount (€)'
FROM `work`
GROUP BY `name`
");

$list = new myList();
$list->setTitle("Project");
if(count($data)) {
	setdata($data, $list);
	echo $list->out();
}

echo '<br /><br /><p><a href="'.$indexUrl.'">Your list</a></p>';
echo "</div>";

function setdata($data, myList &$list, $noheaders = false) {
	$first = reset($data);
	if(!$noheaders) {
		$list->setColumns(count($first));
		foreach($first as $name => $value) {
			$name = str_replace(" ", "&nbsp;", ucfirst($name));
			if(is_numeric($value)) {
				$list->addHeaderCol($name, 1, array("style" => "text-align:right;"));
			} else {
				$list->addHeaderCol($name);
			}
		}
	}
	foreach($data as $row){
		foreach($row as $col) {
			$attr = array();
			if(is_numeric($col)) {
				$col = number_format($col, 1, ",", " ");
				$attr["style"] = "text-align:right;";
			}
			$list->addCol($col, $attr);
		}
	}
}
