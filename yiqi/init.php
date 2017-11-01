<?php
	/*
		初始化时使用，初始化排序号的
	*/
	include '../temp/config.php';
	$fzx_id         = $u['fzx_id'];
	$sql = "select id,px_id from `yiqi` where `fzx_id`='{$fzx_id}' AND px_id=0 ";
	$res = $DB->query($sql);
	$i = 1;
	while($row = $DB->fetch_assoc($res)){
		$update = 'update `yiqi` set px_id='.$i.' where id='.$row['id'];
		$DB->query($update);
		$i++;	
	}
?>
