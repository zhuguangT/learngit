<?php
include "../temp/config.php";
//if($u[gui_hua]!=1) noquanxian(gui_hua);
// print_rr($_GET);die;
if($_GET['handle'] == 'direct_save'){
	//目前不需要任何操作 直接存入数据库即可
	// print_rr($_GET);
}else{
	$_GET[site_id]=intval($_GET[site_id]);
	$_GET[data]=dfmto($_GET[data]); //检查非法字符
	$_GET[field]=get_str($_GET[field]); //检查非法字符
	$site=$DB->fetch_one_assoc("select  * from sites where id='$_GET[site_id]'");
	if($site[id]=='') goback();
	if($_GET[data]=='') goback();
	if($_GET[field]=='') goback();
}
// echo $site[id].'==>>'.$_GET[data].$_GET[field];die;
switch($_GET[field])
{
case 'jingdu':
case 'weidu':
case 'banjing':
// echo "update sites set `$_GET[field]`='$_GET[data]' where id='$site[id]'";die;
$DB->query("update sites set `$_GET[field]`='$_GET[data]' where id='$site[id]'");
break;
default:
}
goback();
?>

