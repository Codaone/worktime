<?php
/**
 * Created by Tyotunnit.
 * User: Juhni
 * Date: 4.9.2014
 * Time: 17:27
 */

if(isset($_POST["save"])) {
	$db->insert("work_payments", array("user_id" => $_POST["user_id"], "amount" => str_replace(array(" ",","), array("","."), $_POST["amount"])));
	// header("Location: ");
	header("Location: users.php");
	die();
}
 
echo "<br />";
$data = $db->get("
	SELECT
		u.id,
		user,
		u.name,
		last_ip,
		last_login,
		IFNULL(SUM(p.amount),0.0) 'Billed',
		ROUND(w.amount - IFNULL(SUM(p.amount),0), 2) 'Not billed',
		ROUND(w.amount, 2) 'Total'
	FROM
		users u
	LEFT JOIN work_payments p ON p.user_id = u.id
	LEFT JOIN (
		SELECT SUM(duration / 60 / 60) 'amount', user_id FROM `work` GROUP BY user_id
	) as w ON w.user_id = u.id
	GROUP BY u.id");
$list = new myList();
$list->setMainProps(array("style" => "width:960px;margin:0 auto"));
if(count($data)) {
	$first = reset($data);
	$list->setColumns(count($first));
	$list->addHeaders(array_keys($first));
	foreach($data as $row) {
		$list->addRow($row);
	}
}
echo $list->out();

?>
<br />
<form method="post" style="width:300px;margin:0 auto;">
	<select name="user_id" style="width: 100px">
		<?php
		foreach($data as $u) {
			echo "<option value='".$u["id"]."'>".$u["name"]."</option>";
		}
		?>
	</select>
	<input type="text"  style="width: 100px;text-align:center" name="amount" placeholder="Hours" required="required" />
	<input type="submit" name="save" value="Save" class="btn btn-success" />
</form>