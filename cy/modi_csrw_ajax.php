<?php
include '../temp/config.php';
include(INC_DIR."cy_func.php");
$fzx_id	= $u['fzx_id'];
if($_POST['act'] == 'xiudate'){
	$oldtime = $_POST['oldtime'];
	$newtime = $_POST['newtime'];
	$oldcybh = $_POST['oldcybh'];
	$sql1 = $DB->query("select * from cy where cy_date='$oldtime' and cyd_bh='$oldcybh' and fzx_id='$fzx_id'");
	$cyd = $DB->fetch_assoc($sql1);
	$newcybh = new_cyd_bh($cyd['site_type'],$newtime);
	$upsql1 = $DB->query("update cy set cyd_bh='$newcybh',cy_date='$newtime' where id = '".$cyd['id']."'");

	$sql2 = $DB->query("select * from cy_rec where cyd_id='".$cyd['id']."'");
	while($row = $DB->fetch_assoc($sql2)){
		$newcode = new_bar_code($cyd['site_type'],$row['water_type'],$newtime);
		$upsql2 = $DB->query("update cy_rec set bar_code='$newcode' where id = '".$row['id']."'");
	}
	if($upsql1){
		echo $newcybh;
	}else{
		echo 'wrong';
	}
}
?> 