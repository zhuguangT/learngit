<?php
include "../temp/config.php";
if($_POST['action']=='wtdw'){
	$wt = $DB->fetch_one_assoc("select * from kehu where id='".$_POST['wtdw']."'");
	$wtstr = JSON($wt);
	echo $wtstr;
}

