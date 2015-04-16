<?php
if(!$user->isLoggedIn()) {
	header("Location: login.php");
	exit;
}
$table = $conf['table'];
if(getvar('id')) {
	$id                       = getvar('id', TRUE);
	$row                      = $db->first("SELECT * FROM `" . $table . "` WHERE `id`= " . $id);
	$othervars['begin']   	  = $row['begin'];
	$othervars['starttime']   = $row['starttime'];
	$othervars['description'] = $row['description'];
	$othervars['name']        = $row['name'];
	$othervars['price']       = $row['price'];
	$othervars['charged']     = $row['charged'];
	$othervars['duration']    = $row['duration'] / 3600;
	$othervars['user_id']	  = $row['user_id'];
}
if(getvar('ok')) {
	$date = new DateTime(getvar('date'));
	$begin		 = $date->getTimestamp();
	$start       = getvar('starttime');
	$duration    = getvar('duration');
	$price       = getvar('price');
	$name        = trim(getvar('name'));
	$description = trim(getvar('description'));
	$charged     = getvar('charged') == "on" ? 1 : 0;
	$user_id 	 = getvar('user_id', false, $user->getUserId());

	$data = array(
		"begin"		  => $begin,
		"starttime"   => $start,
		"duration"    => str_replace(",", ".", $duration) * 3600,
		"price"       => str_replace(",", ".", $price),
		"name"        => $name,
		"description" => $description,
		"charged"     => $charged,
		"user_id"	  => $user_id
	);
	if(getvar('id')) {
		$db->update($table, $data, "`id`='" . $db->escape(getvar('id')) . "'");
		header("Location: ".$indexUrl."?update=1");
	}
	else {
		$id = $db->insert($table, $data);
		header("Location: ".$indexUrl."?new=1");
	}
	die();
}
?>
<center>
	<div class="vid">
		<br />

		<h2>Add a effort.</h2>

		<form action="add.php" method="post">
			<table cellspacing="0" cellpadding="4" style="width:auto;" class="add-table">
				<tr>
					<td style="text-align:right;">Date:</td>
					<td colspan="2">
						<?php
							$date = date("d.m.Y", (getvar("begin") ? getvar("begin") : time()));
						?>
						<input class="form-control date" type="text" name="date" value="<?php echo $date?>" />
						<script>
							$(".date").datepicker($.datepicker.regional["fi"]);
						</script>
					</td>
				</tr>
				
				<tr>
					<td style="text-align:right;">Duration:</td>
					<td>
						<?php
						$h = getvar('duration') ? number_format(getvar('duration'), 1, ".", " ") : 1;
						?>
						<select id="duration" class="form-control" name="duration" style="text-align:center;" onchange="saadaAlotusta(this);">
							<?php
							foreach (range(1, 30) as $val) {
								$val = $val * 0.5;
								echo '<option value="' . $val . '" ' . ($val."" == $h ? 'selected' : "") . ' >' . number_format($val, 1, ",", " ") . '</option>';
							}
							?>
						</select>
						<script type="text/javascript">
						$(function(){
							var val = $("#starttime").val();
							var tmp = val.split(":");
							var houry = tmp[0], minute = tmp[1];
							saadaAlotusta = function(el){
							<?php if(!getvar("id")){ ?>
								var dur = parseFloat($(el).val());
								var plusH = Math.floor(dur);
								var plusM = dur - plusH;
								var min = Math.abs(minute-(plusM*60))+"";
								var hour = (houry-plusH)+"";
								var sel = (hour.length < 2 ? "0"+hour : hour)+":"+(min.length < 2 ? "0"+min : min);
								$("#starttime option[value='"+sel+"']").prop("selected", true);
							<?php } ?>
							};
						});
						</script>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:right;">Started:</td>
					<td colspan="2">
						<?php
						$today = mktime(0, 0, 0);
						$list = array("" => "");
						for ($i = 0; $i < 48; $i++) {
							$time        = $today + $i * 1800;
							$list[date("H:i", $time)] = date("H:i", $time);
						}
						$h = getvar('starttime') ? getvar('starttime') : date("H:i", round(time() / 1800) * 1800);
						?>
						<select class="form-control" name="starttime" id="starttime" style="text-align:center;">
							<?php
							foreach ($list as $val => $display) {
								echo '<option value="' . $val . '" ' . ($val . "" == $h ? 'selected="selected"' : "") . ' >' . $display . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td style="text-align:right;">Customer:</td>
					<td colspan="2">
						<select class="form-control" onchange="$('input[name=name]').val($(this).find('option:selected').text());$('input[name=price]').val($(this).val());;" style="width:220px">
							<?php
							echo "<option>&nbsp;</option>";
							// 8035200 sec = 3months
							if($user->getUsername() == "admin") {
								$rows = $db->get("SELECT `name`, price FROM `" . $table . "` GROUP BY name ORDER BY `charged` ASC, `begin` DESC");
							} else {
								$rows = $db->get("SELECT `name`, price FROM `" . $table . "` WHERE begin >= '". (time()-8035200) . "' GROUP BY name ORDER BY `charged` ASC, `begin` DESC");
							}
							foreach ($rows as $r) {
								echo '<option value="' . number_format((empty($r["price"]) ? 60 : $r["price"]),1,",","") . '" ' . ($r["name"] == getvar('name') ? 'selected' : "") . ' >' . $r["name"] .'</option>';
							}
							?>
						</select>
						<?php if($user->getUsername() == "admin") { ?>
							<input class="form-control" name="name" placeholder="Customer" value="<?php echo  (getvar('name') ? getvar('name') : "") ?>" />
						<?php } else {
							$p = getvar('price') ? number_format(getvar('price'), 1, ",", " ") : "0,0";
							echo '<input type="hidden" name="name" value="' . getvar('name', false, "") . '" />';
							echo '<input type="hidden" name="price" value="' . $p . '" />';
						}
						?>
					</td>
				</tr>
				<?php if($user->getUsername() == "admin") { ?>
				<tr>
					<td style="text-align:right;">Price:</td>
					<td>
						<?php
						$p = getvar('price') ? number_format(getvar('price'), 1, ",", " ") : "0,0";
						?>
						<input class="form-control" type="text" name="price" size="2" value="<?php echo $p; ?>">
					</td>
					<td>&nbsp;</td>
				</tr>
				<?php } ?>
				<tr>
					<td style="text-align:right;" valign="top">Description:</td>
					<td colspan="2">
						<textarea placeholder="Description" class="form-control" rows="5" name="description"><?php echo  (getvar('description') ? getvar('description') : "") ?></textarea>
					</td>
				</tr>
				<?php if($user->getUsername() == "admin") { ?>
				<tr>
					<td style="text-align:right;" >Charged:</td>
					<td colspan="2">
						<input type=checkbox name="charged" <?php echo  (getvar('charged') ? 'checked="checked"' : "") ?>>
					</td>
				</tr>
				<?php } ?>
			</table>
			<br>
			<br>
			<input class="btn btn-success btn-lg"  type="submit" name="ok" value="Save">
			<?php
			if(getvar('id')) {
				echo '<input type="hidden" name="id" value="' . getvar('id') . '" />';
				echo '<input type="hidden" name="user_id" value="' . getvar('user_id') . '" />';
			}
			?>
		</form>

		<?php

		if(getvar('ok') && getvar("id")) {
			echo '<p class="green">Changed to list<br /></p>';
		} else if(getvar("new")) {
			echo '<p class="green">Added to list<br /></p>';
		}
		echo '<p><a class="btn btn-info" style="margin-top:10px" href="'.$indexUrl.'">Your list</a></p>';

		?>
	</div>
</center>