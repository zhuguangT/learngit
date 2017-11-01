<?php
include ('../temp/config.php');
$fzx_id = FZX_ID;

if($_GET['act']=='xiuxuhao'){
	if($_GET['bdid']&&$_GET['xuhao']){
		$sql =$DB->query("update bzwz_detail set xuhao='".$_GET['xuhao']."' where id='".$_GET['bdid']."'");
		if($sql){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}
}
