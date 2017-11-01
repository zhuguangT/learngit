<?php
/**
 * 功能：显示，增加，修改，某种样品类型下的某个项目的检验方法
 * 作者：tielong zhangdengsheng
 * 日期：2014-03-18
*/
include '../../temp/config.php';
##########################导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'检验方法列表','href'=>'system_settings/assay_method/assay_method_list.php?lxid='.$_GET['lxid']),
		array('icon'=>'','html'=>'配置检验方法列表','href'=>'system_settings/assay_method/assay_method_edit.php?xmid='.$_GET['xmid'].'&lxid='.$_GET['lxid'])
		//xmid=$x[xmid]&lxid=$x[lxid]&xmname=$x[value_C]
);
$trade_global['daohang'] = $daohang;

$fzx_id= FZX_ID;//中心
get_int($_GET['lxid']);
if($_GET['lxid'] == "")
{
	gotourl("assay_method_list.php");
}
get_int($_GET['xmid']);
#########################得到项目名
if($_GET['xmid'] != '')
{						
	 $xmname = $DB->fetch_one_assoc("SELECT id AS vid,value_C FROM `assay_value` WHERE id=$_GET[xmid]");
	 $_GET['xmname'] = $xmname['value_C'];
}
########################水样类型
$lxlist=get_syleibie($_GET['lxid']);
########################添加项目方法跳到该页面,项目法里没有数据跳到assay_method_list_save.php
$sql_nums=$DB->query("SELECT COUNT(*)as tot FROM `xmfa` WHERE lxid=$_GET[lxid] AND xmid=$_GET[xmid]  AND fzx_id='$fzx_id'");//
$sql_num=$DB->fetch_assoc($sql_nums);
$tot  = $sql_num['tot'];//总条数
if(0==$tot){
	gotourl("assay_method_list_save.php?lxid=$_GET[lxid]&xmid=$_GET[xmid]&add=55");
} 
///////////////////////////////////////////////////////////////////////////////////
###################新增方法检出限默认
$mr_jcx = $DB->fetch_one_assoc("SELECT xz FROM `assay_jcbz` JOIN n_set as n ON jcbz_bh_id=n.id where vid='$_GET[xmid]' AND n.module_value2=$_GET[lxid] AND module_name='jcbz_bh'  AND module_value3='1'");

if(isset($_GET[fangfa])){
	$mr_arr = $DB->fetch_one_assoc("SELECT * FROM `assay_method` WHERE id =$_GET[fangfa]"); 
}else{
	$mr_dw=$mr_bgline= "<option value=\"\"selected=\"selected\">未设置</option>";
}
$valueyiqi=get_yiqi();
$mr_user=get_hyqx_user();
$mr_user2=get_hyqx_user();
$mr_zzrz="<option  value='0'>未认证</option><option  value='1' >已认证</option>";
////////得到bt_muban表中所有模板名称
$btsql  = "SELECT id,table_name,table_cname FROM `bt_muban` WHERE act='1' ORDER BY CONVERT( `table_cname` USING gbk ) ASC ";
$table_name = $DB->query($btsql);
while($b=$DB->fetch_assoc($table_name))
{
	if($b[id]==$mr_arr[hyd_bg_id]){
		$mr_bgline .= "<option  value=\"$b[id]\" selected=selected>$b[table_cname]</option>";
	}else{
		$mr_bgline .= "<option  value=\"$b[id]\">$b[table_cname]</option>";
	}
}
//////默认保留位数
if(isset($_GET[fangfa])){
	for($i=0;$i<=5;$i++){
		$mrw1_line .=($i==$mr_arr[w1])?"<option selected=selected value='$i'>$i</option>":"<option  value='$i'>$i</option>";
		$mrw2_line .=($i==$mr_arr[w2])?"<option selected=selected value='$i'>$i</option>":"<option  value='$i'>$i</option>";
		$mrw3_line .=($i==$mr_arr[w3])?"<option selected=selected value='$i'>$i</option>":"<option  value='$i'>$i</option>";
		$mrw4_line .=($i==$mr_arr[w4])?"<option selected=selected value='$i'>$i</option>":"<option  value='$i'>$i</option>";
		$mrw5_line .=($i==$mr_arr[w5])?"<option selected=selected value='$i'>$i</option>":"<option  value='$i'>$i</option>";
	}
	if($mr_arr[w1]==null){$mrw1_line.= "<option value=\"\"selected=\"selected\">未设置</option>";}
	if($mr_arr[w2]==null){$mrw2_line.= "<option value=\"\"selected=\"selected\">未设置</option>";}
	if($mr_arr[w3]==null){$mrw3_line.= "<option value=\"\"selected=\"selected\">未设置</option>";}
	if($mr_arr[w4]==null){$mrw4_line.= "<option value=\"\"selected=\"selected\">未设置</option>";}
	if($mr_arr[w5]==null){$mrw5_line.= "<option value=\"\"selected=\"selected\">未设置</option>";}
}else{
	$mrw1_line=$mrw2_line=$mrw3_line=$mrw4_line=$mrw5_line= "<option value=\"\"selected=\"selected\">未设置</option>";
	for($i=0;$i<=5;$i++){
	$mrw1_line .="<option  value='$i'>$i</option>";
	$mrw2_line .="<option  value='$i'>$i</option>";
	$mrw3_line .="<option  value='$i'>$i</option>";
	$mrw4_line .="<option  value='$i'>$i</option>";
	$mrw5_line .="<option  value='$i'>$i</option>";
	}
}
///////数据单位
foreach($global[unit] as $key => $value)
{	
	if($value==$mr_arr[unit]){
		$mr_dw .= "<option selected=selected value='$value'>$value</option>";
	}else{
		$mr_dw .= "<option   value='$value'>$value</option>";
	}
}

#######################//循环方法，显示方法相关的默认信息
//$xmname		= $_SESSION['assayvalueC'][$_GET['xmid']];//得到项目名称	
$sql		= "SELECT *,xmfa.id as id,assay_method.id as fid FROM xmfa LEFT JOIN assay_method on xmfa.fangfa= assay_method.id WHERE lxid=$_GET[lxid] AND xmid=$_GET[xmid] AND xmfa.fzx_id=$fzx_id  ORDER BY mr ASC";//查看这个元素的所有检测方法
$fa			= $DB->query($sql); 
$j			= 1;
while($r = $DB->fetch_assoc($fa))//循环方法，显示方法相关的默认信息
{
	$faf[]=$r[fangfa];
	$valuefangfa=get_fangfa($r[fangfa]);
	$valueyq	 ="<select name='upyiqi'  onchange=\"location='./assay_method_edit_save.php?fid=$r[id]&lxid=$r[lxid]&xmid=$r[xmid]&item=upyiqi&xmname=$_GET[xmname]&value='+this.value;\"style=\"width:100%;\">";
	$valueyq	.=get_yiqi($r[yiqi]);
	$valueyq	.="</select>";
	$valueuser   ="<select name='upuser'  onchange=\"location='./assay_method_edit_save.php?fid=$r[id]&lxid=$r[lxid]&xmid=$r[xmid]&item=upuser&xmname=$_GET[xmname]&value='+this.value;\"style=\"width:100%;\">";
	$valueuser  .=get_hyqx_user($r[userid]); 
	$valueuser  .="</select>";
	$valueuser2  ="<select name='upuser2'  onchange=\"location='./assay_method_edit_save.php?fid=$r[id]&lxid=$r[lxid]&xmid=$r[xmid]&item=upuser2&xmname=$_GET[xmname]&value='+this.value;\"style=\"width:100%;\">";
	$valueuser2 .=get_hyqx_user($r[userid2]); 
	$valueuser2 .="</select>";
	$mr = ($r['mr'] != '1')?"<a title=\"点击修改默认\" href=\"assay_method_edit_save.php?item=upmr&mr=$r[mr]&act=$r[act]&lxid=$r[lxid]&xmid=$r[xmid]&fid=$r[id]&xmname=$_GET[xmname]&user1=$r[userid]&fangfa=$r[fangfa]\" >设为默认</a>":"已默认";//判断是否是默认的
	if($r['act'] == '0')
	{
		$act="<font color='red' >已停用</font>";  
	}else{
		$act="  使用中";
	}
	$biaotou='<thead>	
			<tr>
				<th style="width:3%;">编号</th>
				<th style="width:35%;">检验方法</th>
				<th style="width:15%;">仪器名称</th>
				<th style="width:8%;">人员</th>
				<th style="width:8%;">人员2</th>
				<th style="width:9%;">设置默认</th>
				<th style="width:8%;">状态</th>
				<th style="width:7%;">更多操作</th>
			  </tr>
			  </thead>';
	$falist		.= temp('assay_method_edit_line');
	$j++;
}
###################新增方法
$valueOption1=get_fangfa($_GET[fangfa],$faf);
disp('assay_method_edit');
?>
