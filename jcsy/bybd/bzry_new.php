<?php
/*
*标准溶液新建,修改,计算,保存模块[与显示模块(bzry_disp.php)共用一个模板bzry.html]
*
*/	
	
include "temp/config.php";
if($u[userid] == '') nologin();

//$_GET['zd'] 终点 $_GET['sd']始点 $sj:实际用量 $bz_nongdu:标定结果,标准溶液浓度
$today=date('Y-m-d');
$gong_shi=($_GET['标准溶液名称']=='氰化钾') ? "C=(基准溶液浓度*平均用量*52.04)/基准溶液体积" : "C=(基准溶液浓度*基准溶液体积)/平均用量";
switch($_GET['act']){    //新建标准溶液
	case '':
		$c='class="inputc"';
		$l='class="inputl"';
		$dw="<select name=基准溶液浓度单位> 
				<option>
				<option value='mol/L'>mol/L
				<option value='mg/L'>mg/L
			 </select>";
		$bzry_name="<select name=标准溶液名称>
		<option>
		<option value='盐酸'>盐酸
		<option value='硫代硫酸钠'>硫代硫酸钠
		<option value='EDTA-2Na'>EDTA-2Na
		<option value='硝酸银'>硝酸银
		<option value='氰化钾'>氰化钾
		</select>";
		$save_button="<input type=submit name=act value=保存>";
		$r[bzry_bdrq]=$today;
		break;
	case '保存':
		//以下代码计算出滴定结果
		/***********************************************************************/
		$sum=0;
		for($i=0;$i<6;$i++){
			if($_GET['zd'.$i]=='') break;
			$sj[$i]=$_GET['zd'.$i]-$_GET['sd'.$i];
			$sum+=$sj[$i];
		}
		$pingjun=_round($sum/($i),2);
           if($_GET['zd6']) $kong_bai=$_GET['zd6']-$_GET['sd6'];
           else $kong_bai=0;
           
           if($_GET['标准溶液名称']!='氰化钾') $bzry_nongdu=_round(($_GET[基准溶液浓度]*$_GET[基准溶液体积])/($pingjun-$kong_bai),4);
           else $bzry_nongdu=_round(($pingjun-$kong_bai)*$_GET[基准溶液浓度]*52.04/$_GET[基准溶液体积],4);
		/************************************************************************/
		//保存原始表所有数据
		$r=$DB->fetch_one_assoc("select `id` from `assay_change` where `zhushi`='$_GET[标准溶液名称]' limit 1");
		$DB->query("
insert into `bzry` 
`jzry_name`='$_GET[基准溶液名称]',
`jzry_pzrq`='$_GET[基准溶液配制日期]',
`jzry_nongdu`='$_GET[基准溶液浓度]',
`jzry_nddw`='$_GET[基准溶液浓度单位]',
`bzry_name`='$_GET[标准溶液名称]',
`bzry_pzrq`='$_GET[标准溶液配制日期]',
`bzry_bdrq`='$_GET[标准溶液标定日期]',
`bzry_bdff`='$_GET[标定方法]', 
`zd0`='$_GET[zd0]',
`zd1`='$_GET[zd1]',
`zd2`='$_GET[zd2]',
`zd3`='$_GET[zd3]',
`zd4`='$_GET[zd4]',
`zd5`='$_GET[zd5]',
`zd6`='$_GET[zd6]',
`sd0`='$_GET[sd0]',
`sd1`='$_GET[sd1]',
`sd2`='$_GET[sd2]',
`sd3`='$_GET[sd3]',
`sd4`='$_GET[sd4]',
`sd5`='$_GET[sd5]',
`sd6`='$_GET[sd6]',
`pingjun`='$pingjun',
`gongshi`='$_GET[gongshi]',
`bzry_nongdu`='$bzry_nongdu',
`jzry_tiji`='$_GET[基准溶液体积]',
`bzry_notes`='$_GET[bzry_notes]',
`fx_user`='$u[userid]',
`did`=$r[id] 
");
		$id=$DB->insert_id();
		gotourl("bzry_disp.php?bzry_id=$id");
		break;
	case '修改':
		if(!(($u['hua_yan']=='1' && $u['userid']==$_GET['fx_user']) || $u[userid]=='admin')) noquanxian('hua_yan');
		//以下代码计算出滴定结果
		/************************************************************************/
		$sum=0;
		for($i=0;$i<6;$i++){
			if($_GET['zd'.$i]=='' || $_GET['zd'.$i]=='-') break;
			$sj[$i]=$_GET['zd'.$i]-$_GET['sd'.$i];
			$sum+=$sj[$i];
		}
		$pingjun=_round($sum/($i),2);
           if($_GET['zd6']) $kong_bai=$_GET['zd6']-$_GET['sd6'];
           else $kong_bai=0;
           if($_GET['标准溶液名称']!='氰化钾') $bzry_nongdu=_round(($_GET[基准溶液浓度]*$_GET[基准溶液体积])/($pingjun-$kong_bai),4);
           else $bzry_nongdu=_round(($pingjun-$kong_bai)*$_GET[基准溶液浓度]*52.04/$_GET[基准溶液体积],4);
		/************************************************************************/
		$r=$DB->fetch_one_assoc("select `id` from `assay_change` where `zhushi`='$_GET[标准溶液名称]' limit 1");
		$DB->query("
update `bzry` set 
`jzry_name`='$_GET[基准溶液名称]',
`jzry_pzrq`='$_GET[基准溶液配制日期]',
`jzry_nongdu`='$_GET[基准溶液浓度]',
`jzry_nddw`='$_GET[基准溶液浓度单位]',
`bzry_name`='$_GET[标准溶液名称]',
`bzry_pzrq`='$_GET[标准溶液配制日期]',
`bzry_bdrq`='$_GET[标准溶液标定日期]',
`bzry_bdff`='$_GET[标定方法]', 
`zd0`='$_GET[zd0]',
`zd1`='$_GET[zd1]',
`zd2`='$_GET[zd2]',
`zd3`='$_GET[zd3]',
`zd4`='$_GET[zd4]',
`zd5`='$_GET[zd5]',
`zd6`='$_GET[zd6]',
`sd0`='$_GET[sd0]',
`sd1`='$_GET[sd1]',
`sd2`='$_GET[sd2]',
`sd3`='$_GET[sd3]',
`sd4`='$_GET[sd4]',
`sd5`='$_GET[sd5]',
`sd6`='$_GET[sd6]',
`pingjun`='$pingjun',
`gongshi`='$_GET[gongshi]',
`bzry_nongdu`='$bzry_nongdu',
`jzry_tiji`='$_GET[基准溶液体积]',
`bzry_notes`='$_GET[bzry_notes]',
`did`=$r[id]  
where `id`='$_GET[bzry_id]' limit 1");
		gotourl("bzry_disp.php?bzry_id=$_GET[bzry_id]");
		break;
	case '删除':
		$DB->query("delete from `bzry` where `id`=$_GET[bzry_id]");
		gotourl($url[$_u_][1]);
		break;
}
$_file="bzry";
include "disp.php";
?>
