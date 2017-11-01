<?php
/**
 * 功能：显示某种样品类型下的每个项目的默认检验方法
 * 作者：tielong zhangdengsheng
 * 日期：2014-03-18
 * 描述：对其样品类型下进行项目方法添加
*/
include '../../temp/config.php';
#######################导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'检验方法配置','href'=>'system_settings/assay_method/assay_method_list.php')
);
$trade_global['daohang'] = $daohang;
get_int($_GET['lxid']);
$fzx_id= FZX_ID;//中心
if($_GET['lxid']=='')
{
	$_GET['lxid'] = 1;
}
//echo $_GET['lxid'];
if($_GET['xm'] != '')
{
	$xm = "and xmid=$_GET[xm]";
}
$lxlist=get_syleibie($_GET['lxid']);//获取水样类型
#####################搜索已配方法的项目
$get_value_C=1;
if($_GET['get_value_C']||$get_value_C)
{
	$sql_assay_value = $DB->query("SELECT DISTINCT xmid,value_C FROM xmfa JOIN `assay_value` AS av ON xmfa.xmid=av.id WHERE lxid='$_GET[lxid]' AND xmfa.fzx_id='$fzx_id'AND mr='1' ORDER BY av.seq,av.value_C,xmid");
	while($rs_assay_value=$DB->fetch_assoc($sql_assay_value))
	{
		$valueOption .= "<option value='$rs_assay_value[xmid]' name='$rs_assay_value[value_C]'>$rs_assay_value[value_C]</option>";
	}
	
	if($_GET['get_value_C'])
	{
		echo $valueOption;
		exit();
	}
}
#####################查询某水样类型下的所有项目及其默认方法
$csql="SELECT  xmid,xmfa.id AS id,yiqi,fangfa,value_C,userid,userid2 FROM xmfa  JOIN `assay_value` AS av ON xmfa.xmid=av.id  WHERE  lxid='$_GET[lxid]' AND xmfa.fzx_id='$fzx_id' AND mr='1'  GROUP BY av.seq,av.value_C,xmid";
$crs = $DB->query($csql);
$jc_range_arr = array(); 
$j=1;
while($cr = $DB->fetch_assoc($crs)){
	$jc_range_arr[$cr['xmid']][] = $cr;
	$j++;
}
#############
foreach($jc_range_arr as $k1=>$v1)
{
	//$tr_nums=count($v1);
	$i=1;
	foreach($v1 as $k2=>$v2)
	{
		$cs = $DB->query("SELECT count(fangfa) AS sl FROM xmfa WHERE lxid='$_GET[lxid]' AND  xmfa.act='1' AND xmfa.xmid=$v2[xmid] AND xmfa.fzx_id='$fzx_id' ORDER BY xmid");//已启用方法的数量
		$css = $DB->fetch_assoc($cs);
		$valuefangfa=get_act_fangfa($v2[xmid],$v2[fangfa],$_GET[lxid]);
		$valueyiqi=get_yiqi($v2[yiqi]);
		$valueuser=get_hyqx_user($v2[userid]);
		$valueuser2=get_hyqx_user($v2[userid2]);
		if($i==1)
		{
			$yiyxm .="<tr  height=\"22\" id=tr".$k1."_".$i." class=tr".$k1." >
			<td ><a href=\"./assay_method_edit.php?xmid=$v2[xmid]&lxid=$_GET[lxid]\" title=\"点击配置检验方法\" >$v2[value_C]</a>&nbsp;(已启用$css[sl]种方法)</td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" name=\"fangfa\" style='width:400px;'>$valuefangfa</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" class=\"chosen-select\" name=\"yiqi\" style=\"width:15%;\">$valueyiqi</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\"  name=\"userid\">$valueuser</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" name=\"userid2\">$valueuser2</select></td>
			<td ><a href=\"assay_method_edit_xx.php?id=$v2[id]&value_C=$v2[value_C]&bs='liebiao'\">详细修改</a></td>
			</tr>";
		}else{//style='padding-left:230px;'style='padding-left:330px;'
			$yiyxm.="<tr  height=\"22\" id=tr".$k1."_".$i." class=tr".$k1." >
			<td ><a href=\"./assay_method_edit.php?xmid=$v2[xmid]&lxid=$_GET[lxid]\" title=\"点击配置检验方法\" >$v2[value_C]</a>&nbsp;(已启用$css[sl]种方法)</td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" name=\"fangfa\" style='width:400px;'>$valuefangfa</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" class=\"chosen-select\" name=\"yiqi\" style=\"width:15%;\">$valueyiqi</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\"  name=\"userid\">$valueuser</select></td>
			<td ><select id=\"$v2[id]\" onchange=\"upuser(this)\" name=\"userid2\">$valueuser2</select></td>
			<td ><a href=\"assay_method_edit_xx.php?id=$v2[id]&value_C=$v2[value_C]&bs='liebiao'\">详细修改</a></td>
			</tr>";
		}
		$i++;
	}
}
disp('assay_method_list');
?>
