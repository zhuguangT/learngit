<?php
/**
 * 功能：显示添加质控(室内平行,加标回收,质控样品)界面
 * 作者：zhengsen
 * 时间：2014-06-16
**/
include "../temp/config.php";
if(!$u[userid]){
	nologin();
}
$fzx_id=$u['fzx_id'];
$cy_rs=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_GET['cyd_id']."'");
if($cy_rs['status']>='6'){
	$save_button='';
	$disabled='disabled="disabled"';
}else{
	$save_button="<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" value=\"保存\"></center>";
}
$sql_rec=$DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`=$_GET[cyd_id] AND `status`='1' ORDER BY `bar_code`");
while($rs=$DB->fetch_assoc($sql_rec)){
	if(!empty($rs['snpx_item'])){
		$snpx_c='checked=checked';
	}else{
		$snpx_c='';
	}
	if(!empty($rs['jbhs_item'])){
		$jbhs_c='checked=checked';
	}else{
		$jbhs_c='';
	}
  	$lines.="<tr align='center'>
				<td>".$rs['bar_code']."</td>
				<td align='left' title=".$title.">".$rs['site_name']."</td>
				<td><input type='checkbox' name='snpx[]' {$snpx_c} value={$rs[id]} onclick=\"check(this,'snpx')\" {$disabled}><a onclick=\"show_snzk_item({$rs[id]},'snpx')\">室内平行项目设定</a></td>
				<td><input type='checkbox' name='jbhs[]' {$jbhs_c} value={$rs[id]} onclick=\"check(this,'jbhs')\" {$disabled}><a  onclick=\"show_snzk_item({$rs[id]},'jbhs')\">加标回收项目设定</a></td>
			</tr>";
}
echo temp('modi_zk.html');
?>
