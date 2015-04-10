
<footer>
<?php //include "menu.php"; ?>
</footer>

<?php
global $debugdata;
if(count($debugdata)>0){
	echo "<script type='text/javascript'>alert('";
	foreach($debugdata as $i=>$stuff){
		echo $i.": ".$stuff."\\n";
	}
	echo "');</script>";
}
?>
</body>
</html>