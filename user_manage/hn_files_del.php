<?php
	require '../temp/config.php';
	$fzx_id=$_SESSION['u']['fzx_id'];
	if(count($_GET) == 1){
		$all = explode('-', $_GET['all']);
		$id = $all[0];
		$uid = $all[1];
		$file = $all[2];
		$sql = "delete from user_files where fzx_id='$fzx_id' and `id` = $id  and `u_id`=$uid";
		if(!empty($file))
			if(file_exists('./upload/'.$file))
				@unlink('./upload/'.$file);
		$DB->query($sql);
		echo '<script>history.go(-1)</script>';
		// else
		// 	echo '<div style="text-align:center;margin-top:200px">删除失败,2秒后返回</div>','<script>setTimeout("history.go(-1)",2000);</script>';
	}
?>