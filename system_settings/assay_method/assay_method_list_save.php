<?php
/**
 * 功能：更改某种水样类型下项目默认的检验方法及信息
 * 作者：zhangdengsheng
 * 日期：2014-10-22
 
*/
include '../../temp/config.php';
//$zt='失败';
$fzx_id= FZX_ID;//中心
if($_GET[id]!=''&&$_GET[name]!=''){
	switch($_GET[name])
	{
	case 'fangfa':
	$DB->query("update xmfa set mr='0' where id='$_GET[id]'");
	$x = $DB->fetch_one_assoc("SELECT xmid FROM `xmfa` WHERE id='$_GET[id]'");
	$DB->query("update xmfa set mr='1' where $_GET[name]='$_GET[value]' AND lxid='$_GET[lxid]' AND xmid='$x[xmid]' AND fzx_id='$fzx_id'");
	//$zt='成功';
	break;
	case 'yiqi':
	case 'userid':
	case 'userid2':
	$DB->query("update xmfa set `$_GET[name]`='$_GET[value]' where id='$_GET[id]'");
	//$zt='成功';
	break;
	default:
	}
	
}
//echo json_encode(array("zt"=>$zt));
gotourl( "$rooturl/system_settings/assay_method/assay_method_list_save.php?lxid="+$_GET[name] );
?>
