<?php
include ("../temp/config.php");
$fzx_id			= FZX_ID;//中心
$zt = '已存在该参数';
if(!empty($_GET['qufen'])&&$_GET['qufen']=='xiugai'){
	$queryN = $DB->query("select module_value1 from `n_set` where module_name='tjcs' AND  module_value1='".$_GET['name']."'");
	$numN   = $DB->num_rows($queryN);
	if($numN==0){
		$DB->query("update `n_set` set module_value1='".$_GET['name']."' where module_name='tjcs' AND id='".$_GET['id']."'");
		$yxrow=$DB->affected_rows();
		if($yxrow==1)$zt = '成功';
	}
}
elseif(!empty($_GET['lxName'])){
	$queryN = $DB->query("select id,fzx_id,module_value1 from `n_set` where   module_name='tjcs' AND module_value1='".$_GET['lxName']."'");
	$numN   = $DB->num_rows($queryN);
	if($numN==0){
		$insertLx = $DB->query("insert into `n_set` (fzx_id,module_name,module_value1,module_value3) values('$fzx_id','tjcs','".$_GET['lxName']."','1')");
		$newId=$DB->insert_id();
		if($newId!=''){
			$zt   = '成功';
			$fhid = $newId;
		}
	}
	else{
		$rsLx = $DB->fetch_assoc($queryN);
		if($rsLx['module_value3']=='0'){
			$updateLx = $DB->query("update `n_set` set module_value3='1' where id='".$rsLx['id']."'");
			$yxrow=$DB->affected_rows();
			if($yxrow>=1){
				$zt   = '成功';
				$fhid = $rsLx['id'];
			}
		}else{
			if($fzx_id=='1'&&$rsLx['fzx_id']!='1'){
				$zt   = '分中心中已存在该参数';
			}
			if($fzx_id!='1'&&$rsLx['fzx_id']=='1'){
				$zt   = '总中心中已存在该参数';
			}
		}
	}
}
$arr = array("zt"=>$zt,"labelId"=>$fhid);
echo json_encode($arr);
?>
