<?php
require '../temp/config.php';
$fzx_id         = $u['fzx_id'];
	if(!empty($_POST['id']) && $_POST['px_id'] >= 0 && is_numeric($_POST['id']) && is_numeric($_POST['px_id'])){
		if($_POST['s_px']){
			$all = "select `id`,`px_id` from `yiqi` WHERE `fzx_id`='{$fzx_id}'";
			$re = $DB->query($all);
			while($r = $DB->fetch_assoc($re)){
				//后移
				if($_POST['px_id'] > $_POST['s_px']){
					if($r['px_id'] > $_POST['s_px'] && $r['px_id'] <= $_POST['px_id'])
						$DB->query("update `yiqi` set `px_id`=$r[px_id]-1 where `id`=$r[id] ");			
				}//前移
				elseif($_POST['px_id'] < $_POST['s_px']){
					if($r['px_id'] >= $_POST['px_id'] && $r['px_id'] < $_POST['s_px'])
						$DB->query("update `yiqi` set `px_id`=($r[px_id]+1) where `id`=$r[id] ");				
				} 
			}
		}
		$sql = 'update `yiqi` set `px_id`='.$_POST['px_id'].' where `id`='.$_POST['id'];
		if($DB->query($sql))
			echo 'ok';
		else
			echo 'wrong';
	}
?>
