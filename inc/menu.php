<?php
/**
 * Created by Tyotunnit.
 * User: Juhni
 * Date: 4.9.2014
 * Time: 17:29
 */
?>
<ul class="navigation">
	<li><a class="btn btn-success" href="<?php echo $indexUrl ?>">Frontpage</a></li>
	<li><a class="btn btn-success" href="add.php">Add new</a></li>
	<?php if($user->getUsername() == "admin") { ?>
		<li><a class="btn btn-primary" href="stats.php">Stats</a></li>
		<li><a class="btn btn-primary" href="others.php">Others</a></li>
		<li><a class="btn btn-primary" href="summary.php">Summary</a></li>
		<li><a class="btn btn-warning" href="users.php">Users</a></li>
	<?php } ?>
	<li><a class="btn btn-warning" href="profile.php">Profile</a></li>
	<li><a class="btn btn-warning" href="?logout=1">Logout</a></li>
</ul>
