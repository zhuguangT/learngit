<?php
/**
 * 功能：标准溶液标定列表程序
 * 作者：Mr Zhou
 * 日期：2015-01-21
 * 描述：
*/
include "../../temp/config.php";
$fzx_id = FZX_ID;
$sql = "SELECT * FROM `jzry_bd` WHERE `id` = '$_GET[bzry_id]' AND `fzx_id`='$fzx_id'";
$r=$DB->fetch_one_assoc($sql);
switch($_GET['act']){
	case '停用':
		$DB->query("UPDATE `jzry_bd` SET `status`='已停用' WHERE `id`=$_GET[bzry_id]");
		break;
	case '启用':
		$DB->query("UPDATE `jzry_bd` SET `status`='正在使用' WHERE `id`=$_GET[bzry_id]");
		break;
    case '删除':
    	if(($r['fx_user']==$u['userid']&&''==$r['fx_qz_date'])||$u['system_admin']){
        	$DB->query("DELETE FROM `jzry_bd` WHERE `id`=$_GET[bzry_id]");
    	}else{
    		gotourl($last_url,'你不能删除该标定记录！');
    	}
        break;
}
gotourl($last_url);	
?>
