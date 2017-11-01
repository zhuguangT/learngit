<?php
/*
*功能：检测报告项目设置
*作者：zhengsen
*时间：2016-02-19
*/
include '../temp/config.php';
//得到模板的下拉菜单
$fzx_id	= FZX_ID;//中心

$S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id'" );

$mbxm = '<option value="">----请选择----</option>';
while( $row = $DB->fetch_assoc( $S ) ) {
	$mbxm.="<option value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
}
//查询是否配置了项目
$bg_xm_rs=$DB->fetch_one_assoc("SELECT id,bg_xm FROM report WHERE cyd_id='".$_POST['cyd_id']."' AND cy_rec_id='".$_POST['rec_id']."'");
$bg_id=$bg_xm_rs['id'];
$bg_xm_arr=array();
if(!empty($bg_xm_rs['bg_xm'])){
	$bg_xm_arr=explode(',',$bg_xm_rs['bg_xm']);
}
//显示报告的项目
$sql="SELECT ap.vid,assay_element FROM assay_pay ap JOIN assay_order ao ON ap.id=ao.tid WHERE ao.cyd_id='".$_POST['cyd_id']."' AND ao.cid='".$_POST['rec_id']."' GROUP BY ap.vid ";
$av=$DB->query($sql);
$s=1;

while( $row = $DB->fetch_assoc( $av ) )
{	
	$y=$s%5;
	$mid=$row['vid'];
	if(empty($bg_xm_arr)){
		$pd='checked="checked"';
	}else{
		if(in_array($row['vid'],$bg_xm_arr)){
			$pd='checked="checked"';
		}else{
			$pd='';
		}
	}
	$mx[$y]='<label class="show" flag="mb" style="cursor: pointer;"><input '.$pd.' name="vid[]" flag="mb" value="'.$mid.'" type="checkbox">'.$row['assay_element'].'</label>';
	
	//echo "id:$row[vid]  项目:  $row[value_C]";
	if($s%5==0)
	{
		$lines.=temp('bg/bg_xm_line');
		$n=$s;
		unset($mx);
		unset($mid);
		unset($pd);
	}	
	$s++;
}
if($s>$n){
	$lines.=temp('bg/bg_xm_line');
}
disp('bg/bg_xm_list');
?>