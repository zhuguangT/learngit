<?php
/*
*功能：数据合理性分析页面
*作者：罗磊
*时间：2013-12-25
*/
include "../../temp/config.php";
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'批次任务数据合理性分析','href'=>"$rooturl/huayan/helixing/shuju_hl.php?cyd_id={$_GET['cyd_id']}");
$_SESSION['daohang']['shuju_hl']	= $trade_global['daohang'];
//----------------要在数组中填加要计算的元素和shuju_hl_dan.php中添加----
//总氮121 亚硝酸盐氮187 硝酸盐氮186 氨氮198
//溶解氧114  高锰酸盐104 化学需氧量118 五日生化需氧量119
$fzx_id	= $u['fzx_id'];
$cyid	= $_GET['cyd_id'];
/*$sandan_arr	= array(121,187,186,198);
$sandan_str	= implode(',',$sandan_arr);
$sql	= "select `vid`,`vd0`,`ping_jun` from `assay_order` where `cyd_id` = '$cyid' and vid in ($sandan_str) and hy_flag>=0 ";
$rows	= $DB->query($sql);
while ($row = $DB->fetch_assoc($rows)){
	if($row['vd0']==""){
		$zdan	= "<span title=''>缺少化验值</span>";
	}else{
		if(!empty($row['ping_jun'])){
			$zarr[$row['sid']][$row['vid']]	= $row['ping_jun'];
		}else{
			$zarr[$row['sid']][$row['vid']]	= $row['vd0'];
		}
	}
	$zarr[$row['sid']]['site_name']	= $row['site_name'];
}*/
//$danarr=array(25,16,26,4,21,24,41,32);
$danarr=array(121,187,186,198,114,104,118,119,182,103,173,125,190,193,197,174,162);
$arr= count($danarr);
$zarr_bar	= array();
for($i= 0;$i<$arr;$i++){
	$sql	= "select ao.* from `assay_order` as ao left join `assay_pay` as ap on ao.tid=ap.id  where ap.`fzx_id`='$fzx_id' AND ao.`cyd_id` = '$cyid' and ao.`vid` in ($danarr[$i]) and ao.`hy_flag`>=0 ";
	$rows	= $DB->query($sql);
	while ($row = $DB->fetch_assoc($rows)){
		//阴阳离子平衡判断
		if(@empty($danzhi[$row['sid']]['ion'])){
			$ion_result_arr = ion_balance($row['cid']);
			if($ion_result_arr['result']=='不合理'){
				$ion_style	= " color:#F00;";//红色
			}else if($ion_result_arr['result']=='数据不充分'){
				$ion_result_arr['result']	= '缺少化验值';
				$ion_style	= " color:#F60;";//橘红色色
			}
			$ion_result_arr['result']	= "<a href='shuju_hl_dan.php?cyid=$cyid&lx=4' style='{$ion_style}'>{$ion_result_arr['result']}</a>";
			//阴阳离子平衡
			$danzhi[$row['sid']]['ion']	= $ion_result_arr;
		}
		//溶固体校核
		if(@empty($danzhi[$row['sid']]['solid'])){
			$solid_result_arr = solid_balance($row['cid']);
			if($solid_result_arr['result']=='不合理'){
				$solid_style	= " color:#F00;";//红色
			}else if($solid_result_arr['result']=='数据不充分'){
				$solid_result_arr['result']	= '缺少化验值';
				$solid_style	= " color:#F60;";//橘红色色
			}
			$solid_result_arr['result']	= "<a href='shuju_hl_dan.php?cyid=$cyid&lx=5' style='{$solid_style}'>{$solid_result_arr['result']}</a>";
			//溶固体校核结果
			$danzhi[$row['sid']]['solid']	= $solid_result_arr;
		}
		//总硬度校核
		if(@empty($danzhi[$row['sid']]['zongyingdu'])){
			$zongyingdu_result_arr = zongyingdu_balance($row['cid']);
			if($zongyingdu_result_arr['result']=='不合理'){
				$solid_style	= " color:#F00;";//红色
			}else if($zongyingdu_result_arr['result']=='数据不充分'){
				$zongyingdu_result_arr['result']	= '缺少化验值';
				$solid_style	= " color:#F60;";//橘红色色
			}
			$zongyingdu_result_arr['result']	= "<a href='shuju_hl_dan.php?cyid=$cyid&lx=6' style='{$solid_style}'>{$zongyingdu_result_arr['result']}</a>";
			//总硬度校核结果
			$danzhi[$row['sid']]['zongyingdu']	= $zongyingdu_result_arr;
		}
		//结果值记录
		if(!empty($row['ping_jun']) && !stristr($row['ping_jun'],"<")){
			$zdan	= $row['ping_jun'];
		}else{
			$zdan	= $row['_vd0'];
		}
		//echo $row['sid'];
		$danzhi[$row['sid']][$row['vid']]= $zdan;
		$tidzhi[$row['sid']][$row['vid']]= $row['tid'];
		$zarr[$row['sid']]= $row['site_name'];
		$zarr_bar[$row['sid']]= $row['bar_code'];
	}
}
//print_rr($danzhi);
$zdbh =1;
foreach((array)$danzhi as $key => $value){
	//$zdmc	= $zarr["$key"];
	$zdmc	= $zarr_bar["$key"];
	$td1	= $value[186];
	$td2	= $value[198];
	$td3	= $value[187];
	$zongdan= $value[121];//总氮121 亚硝酸盐氮187 硝酸盐氮186 氨氮198
	//----------------------三氮与总氮-----------
	if(($td1+$td2+$td3)<= $zongdan){
		$danhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=1'> 合理</a>";
	}else{
		$danhl	= "<p style='color:#F00'><a href='shuju_hl_dan.php?cyid=$cyid&lx=1' style='color:#F00'>不合理</a></p>";
	}
	//没有总氮或三氮之和。无法比较，显示缺少化验值
	if($zongdan == ''||($td1==''&&$td2==''&&$td3=='')){
		if(!empty($tidzhi[$key][186]) || !empty($tidzhi[$key][187]) || !empty($tidzhi[$key][198]) || !empty($tidzhi[$key][121])){
			$danhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=1' style='color:#F60'> 缺少化验值</a>";
		}else{
			$danhl	= "<span>所有参与项目均不检测</span>";
		}
	}
	//-----------------溶解氧 高锰酸钾  化学徐氧量 五日生化氧--------
	if((($value[118] > $value[104])||($value[118] > $value[119] ))&& (0.2<($value[119]/$value[118])&& 0.8>($value[119]/$value[118]))){
		$rjyhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=2'> 合理</a>";
	}else{
		$rjyhl	= "<p style='color:#F00'><a href='shuju_hl_dan.php?cyid=$cyid&lx=2' style='color:#F00'>不合理</a></p>";
	}
	if($value[118] == '' || $value[119] ==''){
		if(!empty($tidzhi[$key][118]) || !empty($tidzhi[$key][119]) || !empty($tidzhi[$key][104])){
			$rjyhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=2' style='color:#F60'> 缺少化验值</a>";
		}else{
			$rjyhl	= "<span>所有参与项目均不检测</span>";
		}
	}
	//---------------------------三氮与溶解氧------------ 
	if($value[114] != ''&&$value[186]&&$value[198]){
		if((($value[114] > 5.0) && ($value[186] > $value[198])) || (($value[114] < 5.0) && ($value[186] < $value[198])) ){
			$sdrjhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=3'> 合理</a>"; 
		}else{
			$sdrjhl	="<p style='color:#F00'><a href='shuju_hl_dan.php?cyid=$cyid&lx=3' style='color:#F00'>不合理</a></p>";
		}
	}else{
		if(!empty($tidzhi[$key][114]) || !empty($tidzhi[$key][186]) || !empty($tidzhi[$key][198])){
			$sdrjhl	= "<a   href='shuju_hl_dan.php?cyid=$cyid&lx=3' style='color:#F60'> 缺少化验值</a>";
		}else{
			$sdrjhl	= "<span>所有参与项目均不检测</span>";
		}
	}
	$shujuhl_tr	.= temp('helixing/shuju_hl_tr');
	$zdbh++;
}
disp("helixing/shuju_hl");
?>