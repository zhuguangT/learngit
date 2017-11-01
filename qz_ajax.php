<?php
include ('./temp/config.php');
$fzx_id = $u['fzx_id'];
$qzarr = $qzarr1 = array();
$bzsql = $DB->query("select * from n_set where module_name='hzqz' and module_value2 ='".$_POST['qzdate']."' and fzx_id='$fzx_id'");
while($qzrow = $DB->fetch_assoc($bzsql)){
	$qzarr[$qzrow['module_value1']] = $qzrow['module_value3'];
}
if($_POST['bz']){
	
	if(!$qzarr[$_POST['bz']]){
		$qzinsert = $DB->query("insert into n_set (fzx_id,module_name,module_value1,module_value2,module_value3) values ('$fzx_id','hzqz','".$_POST['bz']."','".$_POST['qzdate']."','".$_POST['userid']."')");
	}
	if($qzinsert){
		echo 'ok';
	}else{
		echo 'wrong';
	}
}
if($_POST['act'] == 'jcqz'){
	if($qzarr){
		foreach($qzarr as $kk=>$qz){
			$qzstr .=','.$kk.":".$qz;
		}
		if($qzarr){
			echo $qzstr;
		}
	}else{
			echo 'wrong';
	}
}