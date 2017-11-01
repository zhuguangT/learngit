<?php
include '../../temp/config.php';

get_int($_GET['id']);
$fzx_id= FZX_ID;//中心
$f = $DB->fetch_one_assoc("SELECT *,x.id AS id FROM xmfa AS x JOIN leixing AS lx on x.lxid= lx.id WHERE x.id=$_GET[id] AND x.fzx_id=$fzx_id");
if($f[fangfa]=='0' || $f[fangfa]=='' || $f[fangfa]==NULL){
	$x = $f;
}else{
$x = $DB->fetch_one_assoc("SELECT *,x.id AS id,x.hyd_bg_id AS hyd_bg_id,x.unit AS unit,x.jcx AS jcx,x.w1 AS w1,x.w2 AS w2,x.w3 AS w3,x.w4 AS w4,x.w5 AS w5 FROM xmfa AS x LEFT JOIN assay_method AS am on x.fangfa= am.id JOIN leixing AS lx on x.lxid= lx.id WHERE x.id=$_GET[id] AND x.fzx_id=$fzx_id AND (lx.fzx_id=$fzx_id OR lx.fzx_id=0)");}
//导航
if($_GET['bs']='liebiao'){
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'检验方法列表','href'=>'system_settings/assay_method/assay_method_list.php?lxid='.$x['lxid']),
		array('icon'=>'','html'=>'检验方法详细配置','href'=>'system_settings/assay_method/assay_method_edit_xx.php?id='.$_GET['id'].'&value_C='.$_GET[value_C]));
}else{
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'检验方法列表','href'=>'system_settings/assay_method/assay_method_list.php?lxid='.$x['lxid']),
		array('icon'=>'','html'=>'检验方法配置列表','href'=>'system_settings/assay_method/assay_method_edit.php?xmid='.$x['xmid'].'&lxid='.$x['lxid']),
		array('icon'=>'','html'=>'检验方法详细配置','href'=>'system_settings/assay_method/assay_method_edit_xx.php?id='.$_GET['id'].'&value_C='.$_GET[value_C])
);}
$trade_global['daohang'] = $daohang;
//$e = $DB->fetch_one_assoc("SELECT * FROM leixing WHERE id=$x[id] AND xmfa.fzx_id=$fzx_id ");
/*获得所有的方法*/
$valueOption=get_fangfa($x['fangfa']);
/*化验单表格名称*/
$btsql  = "SELECT id,table_name,table_cname FROM `bt_muban` WHERE act='1' ORDER BY CONVERT( `table_cname` USING gbk ) ASC ";//得到bt表中所有模板名称
$table_name = $DB->query($btsql);
if($x['hyd_bg_id']==''||$x['hyd_bg_id']=='0'||$x['hyd_bg_id']==NULL){
	$bgline .= "<option  value='' selected=\"selected\">&nbsp;&nbsp;&nbsp;&nbsp; 未 设 置</option>";
}
while($r=$DB->fetch_assoc($table_name)){
	if($r['id'] == $x['hyd_bg_id']){  
			$bgline .= "<option selected=selected value=\"$r[id]\">$r[table_cname]</option>";
		}else{
			$bgline .= "<option   value=\"$r[id]\">$r[table_cname]</option>";
		}
}
/*获得所有的仪器*/
$valueyiqi=get_yiqi($x['yiqi']);
/*化验员*/
$userline=get_hyqx_user($x['userid']);
/*化验员2*/	
$userline2=get_hyqx_user($x['userid2']);
/*数据单位*/
if($x['unit']==''||$x['unit']=='0'||$x['unit']==NULL){
	$dwline .= "<option  value='' selected=\"selected\">未设置</option>";
}else{$dwline .= "<option  value='' >未设置</option>";}
/*资质认证*/
if($x['zzrz']=='1'){
	$zzrz= "<option  value='1' selected=\"selected\">已认证</option><option  value='0' >未认证</option>";
}else{$zzrz= "<option  value='0' selected=\"selected\">未认证</option><option  value='1' >已认证</option>";}
foreach($global[unit] as $key => $value)
	{ 
		if($value == $x['unit'] )
		{
			$dwline .= "<option selected=selected value='$value'>$value</option>";
		}else{
			$dwline .= "<option value='$value'>$value</option>";
		}
	}
//默认保留位数
	
for($i=0;$i<=5;$i++){
		$mrw1line .=($i==$x['w1'])?"<option selected=\"selected\" value='$i'>$i</option>":"<option value='$i'>$i</option>";
		$mrw2line .=($i==$x['w2'])?"<option selected=selected value='$i'>$i</option>":"<option value='$i'>$i</option>";
		$mrw3line .=($i==$x['w3'])?"<option selected=selected value='$i'>$i</option>":"<option value='$i'>$i</option>"; 
		$mrw4line .=($i==$x['w4'])?"<option selected=selected value='$i'>$i</option>":"<option value='$i'>$i</option>";
		$mrw5line .=($i==$x['w5'])?"<option selected=selected value='$i'>$i</option>":"<option value='$i'>$i</option>";
	}
	$mrw1line .=($x['w1']==''||$x['w1']==NULL)?"<option  value='' selected=\"selected\">未设置</option>":"<option  value='' >未设置</option>";
	$mrw2line .=($x['w2']==''||$x['w2']==NULL)?"<option  value='' selected=\"selected\">未设置</option>":"<option  value='' >未设置</option>";
	$mrw3line .=($x['w3']==''||$x['w3']==NULL)?"<option  value='' selected=\"selected\">未设置</option>":"<option  value='' >未设置</option>";
	$mrw4line .=($x['w4']==''||$x['w4']==NULL)?"<option  value='' selected=\"selected\">未设置</option>":"<option  value='' >未设置</option>";
	$mrw5line .=($x['w5']==''||$x['w5']==NULL)?"<option  value='' selected=\"selected\">未设置</option>":"<option  value='' >未设置</option>";
disp('assay_method_edit_xx');
?>
