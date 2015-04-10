<?php
if (isset($_POST['submit'])){
	$u = $_POST['username'];
	$p = $_POST['password'];
	if($user->login($u,$p)){
		if(isset($_SERVER["HTTP_REFERER"])) {
			header("Location: ".$_SERVER["HTTP_REFERER"]);
		} else {
			header("Location: ".$_SERVER["REQUEST_URI"]);
		}
		exit;
	}
} elseif($user->isLoggedIn()){
	header("Location: ".$indexUrl);
	exit;
}
?>
<center><br />
<form method="post"> 
	<table style="width:auto;" cellspacing="8">
		<tr>
			<td colspan="2"><p style="color:red;text-align:center"><?php echo $user->error?></p></td>
		</tr>
		<tr>
			<td style="text-align:right">Login name:</td>
			<td class="input-group" style="padding:5px 0">
				<input type="text" name="username" class="form-control" size="25" placeholder="Username" />
			</td>
		</tr>
		<tr>
			<td style="text-align:right">Password:</td>
			<td class="input-group" style="padding:5px 0">
				<input type="password" name="password" class="form-control" size="25" placeholder="Password" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="padding:10px 0">
				<input type="submit" value="Login" name="submit" class="btn btn-success" />
			</td>
		</tr>
	</table>
</form>
<br /><br />
</center>