<?php
include ("../temp/config.php");
$fzx_id	= FZX_ID;//中心
if(!empty($_GET['lxName'])){
		$sgc ="SELECT * FROM `leixing` WHERE id='$_GET['lxName']'";
		$sgcc=$DB->fetch_one_assoc($sgc);
		if($sgcc[parent_id]!=0){//查询当子水样类型时的项目
			$sql= $DB->query("SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE   n.fzx_id='".$fzx_id."' AND n.module_name='jcbz_bh' AND n.module_value2 IN ($_GET['lxName'],$sgcc[parent_id]) AND n.module_value3='1' GROUP BY `vid`");
			while($sqll =$DB->fetch_assoc($sql)){
			$all_assay_value[]=$sqll[vid];}
			//该水样类型下有方法的
			$sql = $DB->query("SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj  INNER JOIN `xmfa` AS xf ON aj.vid=xf.xmid INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE  xf.fzx_id='".$fzx_id."' AND n.fzx_id='".$fzx_id."' AND n.module_value2 IN ($_GET['lxName'],$sgcc[parent_id]) AND n.module_value3='1' AND xf.lxid IN ($_GET['lxName'],$sgcc[parent_id]) AND xf.mr='1' AND xf.shows='1' AND xf.xs='0' GROUP BY `vid`");
			while($sqll = $DB->fetch_assoc($sql)){
			$sit[] = $sqll[vid];}
		}else{//查询当父水样类型时的项目
			$sql= $DB->query("SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE   n.fzx_id='".$fzx_id."' AND n.module_name='jcbz_bh' AND n.module_value2=$_GET['lxName'] AND n.module_value3='1' GROUP BY `vid`");
			while($sqll =$DB->fetch_assoc($sql)){
			$all_assay_value[]=$sqll[vid];}
			//该水样类型下有方法的
			$sql = $DB->query("SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj  INNER JOIN `xmfa` AS xf ON aj.vid=xf.xmid INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id WHERE xf.fzx_id='".$fzx_id."' AND n.fzx_id='".$fzx_id."' AND n.module_value2 = $_GET['lxName'] AND n.module_value3='1' AND xf.lxid = $_GET['lxName'] AND xf.mr='1' AND xf.shows='1' AND xf.xs='0' GROUP BY `vid`");
			while($sqll =$DB->fetch_assoc($sql)){
			$sit[] = $sqll[vid];}
		}
//---------------------------------------------
	$queryN = $DB->query("select id,module_value1 from `n_set` where   module_name='tjcs' AND module_value1='".$_GET['lxName']."'");
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
		if($rsLx['act']=='0'){
			$updateLx = $DB->query("update `baogao_rwlx` set act='1' where id='".$rsLx['id']."'");
			$yxrow=$DB->affected_rows();
			if($yxrow>=1){
				$zt   = '成功';
				$fhid = $rsLx['id'];
			}
		}
	}
}
$arr = array("zt"=>$zt,"labelId"=>$fhid);
echo json_encode($arr);
?>