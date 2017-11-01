<?php
/**
 * 功能：处理用户更换模板请求
 * 作者：罗磊
 * 日期：2014-5-14
 * 描述：
*/
include '../temp/config.php';
$rec_id = get_int($_POST['rec_id']);
$te_id    = get_int($_POST['te_id']);
  
if($rec_id && $te_id){
	$s=$DB-> fetch_one_assoc( "SELECT * FROM `report` WHERE cy_rec_id ='".$rec_id."' ");
	if($s){
		$ar = $DB->query("UPDATE `report` SET te_id = '".$te_id."'  WHERE cy_rec_id ='".$rec_id."'");
	}else{
		$ar = $DB->query("INSERT INTO `report` SET cy_rec_id = '".$rec_id."', state = '9' ,bg_date = curdate(), te_id = '".$te_id."',tab_user ='".$u['userid']."'");
	}

	if($ar > 0 ){
		echo '1';   
	}else{
		echo '0';
	}
}else{
	echo '0';
}
exit();

?>