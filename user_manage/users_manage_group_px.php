<?php
include '../temp/config.php';
$fzx_id = $u['fzx_id'];
$old_px=$_POST['s_px'];
$change_px=$_POST['px_id'];
$id = $_POST['id'];
if($change_px!=0 && !empty($change_px)){
	if($old_px>$change_px){
		$sql="SELECT * FROM `users` WHERE `px`<=$old_px and `px`>=$change_px AND `fzx_id` = '$fzx_id' AND `group` = '0' ORDER BY `px` ";
		$re=$DB->query($sql);
		$i=$change_px;		
		while($data=$DB->fetch_assoc($re)){
			$i++;
			$sql_up="UPDATE `users` SET `px`=$i WHERE `id` = $data[id] AND `fzx_id` = '$fzx_id'";
			$DB->query($sql_up);
		}
		$sql_px="UPDATE `users` SET `px`=$change_px WHERE `id` =$id AND `fzx_id` = '$fzx_id'";
		if($DB->query($sql_px)){
			echo 'ok';
		}else{
			echo 'wronog';
		}
	}else if($change_px>$old_px){
		$sql="SELECT * FROM `users` WHERE `px`>$old_px AND `px`<=$change_px  AND `fzx_id` = '$fzx_id' AND `group` = '0'  ORDER BY `px`  ";
		$re=$DB->query($sql);
		if($old_px=='0'){
			$i=$change_px+1;
		}else{
			$i=$old_px;
		}
		while($data=$DB->fetch_assoc($re)){
			// print_rr($data);
			if($i!=$change_px){
				$sql_up="UPDATE `users` SET `px`=$i WHERE `id` = $data[id] AND `fzx_id` = '$fzx_id'";
				$DB->query($sql_up); 
			}
			// echo $sql_up.'<br>';
			$i++;
			
		}
		$sql_px="UPDATE `users` SET `px`=$change_px WHERE `id` = $id AND `fzx_id` = '$fzx_id'";
		if($DB->query($sql_px)){
			echo 'ok';
		}else{
			echo 'wronog';
		}
	}
}else{
	echo 'wrong';
}




?>