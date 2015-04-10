<?php
$message = "";
$error = "";
if (isset($_POST['submit'])){
	$p1 = $_POST['password1'];
	$p2 = $_POST['password2'];
	if($p1 != $p2) {
		$error = "Passwords doesn't match!";
	} else if(empty($p1)){
		$error = "Password cannot be empty!";
	} else {
		$newPass = sha1($user->getUserName().sha1($p1));
		$db->update("users", array("password" => $newPass), "`id`='" . $db->escape($user->getUserId()) . "'");
		$message = "Password updated";
	}
} elseif(!$user->isLoggedIn()){
	header("Location: login.php");
	exit;
}
?>
<center><br /><br />
<form method="post"> 
	<table style="width:auto;" cellspacing="8">
		<tr>
		  <td colspan="2"><h3 style="text-align:center;margin:0;"><a href="index.php">Homepage</a></h3></td>
		</tr>
		<tr>
			<td colspan="2">
				<?php if(!empty($error)) { ?>
				<p style="color:red;text-align:center"><?php echo $error?></p>
				<?php } else if(!empty($message)) { ?>
				<p style="color:green;text-align:center"><?php echo $message?></p>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td style="text-align:right">Username:</td>
			<td class="input-group" style="padding:5px 0">
				<?php echo $user->getUserName(); ?>
			</td>
		</tr>
		<tr>
			<td style="text-align:right">New password:</td>
			<td class="input-group" style="padding:5px 0">
				<input type="password" name="password1" class="form-control" size="25" placeholder="Password" />
			</td>
		</tr>
		<tr>
			<td style="text-align:right">New password again:</td>
			<td class="input-group" style="padding:5px 0">
				<input type="password" name="password2" class="form-control" size="25" placeholder="Password" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="padding:10px 0">
				<input type="submit" value="Save" name="submit" class="btn btn-success" />
			</td>
		</tr>
	</table>
</form>
<br /><br />
</center>