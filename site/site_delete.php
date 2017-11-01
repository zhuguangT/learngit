<?php
//删除一个站点,将删除有关该站点的所有数据
//请慎重
// $_REQUEST['sid'] 是一个数组
require_once "../temp/config.php";
if (!$u['xd_cy_rw'] && $u['xd_csrw']){
 die( '你没有权限' );
}
$_GET['sgname']	= urlsafe_b64decode($_GET['sgname']);
if($_GET['sid']>0)
{
	//$sql="UPDATE `sites` SET status ='0' WHERE `id`='$_GET[sid]'";
	//$DB->query($sql);
	$sqll="UPDATE `site_group` SET act ='0' WHERE `site_id`='$_GET[sid]' AND `group_name`='$_GET[sgname]'  AND `fzx_id`='$_GET[fid]'";
	$DB->query($sqll);
	if($_GET[site_type]=='0'){
		$rs_sites	= $DB->fetch_one_assoc("SELECT tjcs FROM `sites` WHERE id='$_GET[sid]'");
		if($rs_sites['tjcs']!=',,'){
			$site_tjcs=$rs_sites['tjcs'];
			$site_tjcs=Trim($site_tjcs,',');
			$bglxAr= explode(',',$site_tjcs);
			$sgname=array(0=>$_GET['sgname']);
			$sites_new_tjcs= array_diff($bglxAr, $sgname);
			$site_tj=implode(',',$sites_new_tjcs);
			$site_tj=','.$site_tj.',';
			$sqll="UPDATE `sites` SET tjcs ='$site_tj' WHERE `id`='$_GET[sid]' AND `fzx_id`='$_GET[fid]'";
			$DB->query($sqll);
		}
		gotourl("$rooturl/site/site_list_new.php?site_type={$_GET['site_type']}");
	}
	/*$rs_sites	= $DB->fetch_one_assoc("SELECT tjcs FROM `sites` WHERE id='{$_GET[sid]}'");

	$tjcs	= $DB->fetch_one_assoc("SELECT group_name FROM `site_group` WHERE id='{$_GET[sid]}'");
	$rs_sites['tjcs']	= str_replace($tjcs, ',', $rs_sites['tjcs']);
	*/
}
if($_GET[pi]>0)//说明要隐藏整批站点
{
	$sql= "UPDATE `site_group` SET act='0' WHERE `site_type`='$_GET[site_type]' AND `group_name`='$_GET[sgname]' AND `fzx_id`='$_GET[fid]' ";
	$DB->query($sql);
	/*$sq=$DB->query("SELECT `site_id` FROM `site_group` WHERE `site_type`='$_GET[site_type]' AND `group_name`='$_GET[sgname]' AND `fzx_id`='$_GET[fid]' ");
	while($yc = $DB->fetch_assoc($sq))
	{
		$sql="UPDATE `sites` SET status ='0' WHERE `id`='$yc[sid]'";
		$DB->query($sql);
	}*/
}
if($_GET['action']=='xd_cyrw'){
	gotourl("$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_GET['site_type']}");
}else{
	$back_url = explode( '&group_name',$_SESSION['back_url'] );
	gotourl( $back_url[0] );
}
?>
