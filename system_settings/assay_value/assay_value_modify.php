<?php
/**
 * 功能：化验项目保存页面（包括ajax修改 和 单独页面的修改和添加）
 * 作者：韩枫
 * 日期：2014-03-26
 * 描述：fzx_id 分中心Id
*/
include("../../temp/config.php");
$fzx_id  = "1";
$userOption = $user2Option = '';
if(empty($_GET['vid']))echo "<script>alert('未能识别项目，请重试');location.href='assay_value_list.php'</script>";
#############取出 项目的详细信息
$rsValue = $DB->fetch_one_assoc("select * from `assay_value` JOIN `xm` ON assay_value.vid=xm.id where assay_value.id='".$_GET['vid']."'");
#############导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'系统维护','href'=>'system_settings/assay_value/assay_value_list.php'),
        array('icon'=>'','html'=>'化验项目管理','href'=>'system_settings/assay_value/assay_value_list.php'),
		array('icon'=>'','html'=>"{{$rsValue[value_C]}}详细信息修改",'href'=>"system_settings/assay_value/assay_value_modify.php?vid={$_GET['vid']}")
);
$trade_global['daohang'] = $daohang;
############取出 所有化验员
$sql_hy_user = $DB->query("select id,userid from `users` where `group`!='测试组' and `group`!='0' and `hua_yan`='1'");
while($rs_hy_user = $DB->fetch_assoc($sql_hy_user)){
        if($rs_hy_user['userid']==$rsValue['userid'])$userOption .= "<option value='".$rs_hy_user['userid']."' selected>".$rs_hy_user['userid']."</option>";
	elseif($rs_hy_user['userid']==$rsValue['userid2'])$user2Option .= "<option value='".$rs_hy_user['userid']."' selected>".$rs_hy_user['userid']."</option>";
	else{
		$userOption .= "<option value='".$rs_hy_user['userid']."'>".$rs_hy_user['userid']."</option>";
		$user2Option .= "<option value='".$rs_hy_user['userid']."'>".$rs_hy_user['userid']."</option>";
	}
}
#####################项目信息的默认值
if($rsValue['fenlei']==''){
$rsValue['fenlei']='未分类';
}
$moRenInput  = "
		<input type='hidden' biaoZhi='w1' value='".$rsValue['w1']."' moren='selected'>
		<input type='hidden' biaoZhi='w2' value='".$rsValue['w2']."' moren='selected'>
		<input type='hidden' biaoZhi='w3' value='".$rsValue['w3']."' moren='selected'>
		<input type='hidden' biaoZhi='w4' value='".$rsValue['w4']."' moren='selected'>
		<input type='hidden' biaoZhi='w5' value='".$rsValue['w5']."' moren='selected'>
		<input type='hidden' biaoZhi='act' value='".$rsValue['act']."' moren='selected'>
		<input type='hidden' biaoZhi='fenlei' value='".$rsValue['fenlei']."' moren='selected'>";

disp('assay_value_modify.html');
?>
