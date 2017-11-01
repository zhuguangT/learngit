<?php
#################################################################
#              据合理性分析页面    (接收页面)                   #
#                    罗磊                                       #
#                  2014 .12.25                                  # 
#################################################################
include "../../temp/config.php";
//导航
/*$now_url	= "$rooturl/huayan/helixing/shuju_hl_list.php";
foreach ($_SESSION['url_stack'] as $value) {
	if(stristr($value,"shuju_hl_list.php?")){
		$now_url	= $value;
		break;
	}
}*/
switch ($_GET['lx']) {
	case '1':
		$this_title	= "三氮与总氮分析";
		break;
	case '2':
		$this_title	= "(耗)氧分析";
		break;
	case '3':
		$this_title	= "氮化物与(耗)氧分析";
		break;
	case '4':
		$this_title	= "阴阳离子平衡分析";
		break;
	case '5':
		$this_title	= "溶解性总固体平衡分析";
		break;
	case '6':
		$this_title	= "总硬度校核";
		break;
	default:
		# code...
		break;
}
$trade_global['daohang'][]	= array('icon'=>'','html'=>$this_title,'href'=>"$rooturl/huayan/helixing/shuju_hl_dan.php?cyid={$_GET['cyid']}&lx={$_GET['lx']}");
$_SESSION['daohang']['shuju_hl_dan']	= $trade_global['daohang'];
$fzx_id		= $u['fzx_id'];
if($gx_assay_form==''){
	$gx_assay_form	= 'assay_form2.php';
}
$cyid		= $_GET['cyid'];
$jsleixin	= $_GET['lx'];
//阴阳离子平衡
if($jsleixin=='4'){
	$select_cy_rec	= $DB->query("SELECT `id`,`site_name`,`bar_code` FROM `cy_rec` WHERE `cyd_id`='{$cyid}' AND `sid`>0 AND `zk_flag`>='0' ORDER BY `id`,`site_name`");
	$lines	= '';
	$i		= 0;
	while($rs_select_cy_rec = $DB->fetch_assoc($select_cy_rec)){
		$i++;
		$ion_result_arr	= array();
		$ion_result_arr = ion_balance($rs_select_cy_rec['id']);
		//被转换过的项目(硝酸盐氮、总碱度)
		empty($xsyd_str)?$xsyd_str='':'';
		empty($zjd_str)?$zjd_str='':'';
		if(in_array('186',$ion_result_arr['change_value'])){
			$xsyd_str	= '(硝酸盐氮)';
		}
		if(in_array('125',$ion_result_arr['change_value'])){
			$zjd_str	= '(总碱度)';
		}
		//行模板，每个站点的详细信息
		$lines	.= "<tr><td>{$i}</td><td>{$rs_select_cy_rec['site_name']}<br>{$rs_select_cy_rec['bar_code']}</td><td title='{$ion_result_arr['172']['count_ion']}'>{$ion_result_arr['172']['old_vd0']}</td><td title='{$ion_result_arr['162']['count_ion']}'>{$ion_result_arr['162']['old_vd0']}</td><td title='{$ion_result_arr['173']['count_ion']}'>{$ion_result_arr['173']['old_vd0']}</td><td title='{$ion_result_arr['174']['count_ion']}'>{$ion_result_arr['174']['old_vd0']}</td><td title='{$ion_result_arr['182']['count_ion']}'>{$ion_result_arr['182']['old_vd0']}</td><td title='{$ion_result_arr['190']['count_ion']}'>{$ion_result_arr['190']['old_vd0']}</td><td title='{$ion_result_arr['655']['count_ion']}'>{$ion_result_arr['655']['old_vd0']}</td><td>{$ion_result_arr['125']['old_vd0']}</td><td title='{$ion_result_arr['189']['count_ion']}'>{$ion_result_arr['189']['old_vd0']}</td><td title='{$ion_result_arr['188']['count_ion']}'>{$ion_result_arr['188']['old_vd0']}</td><td title='{$ion_result_arr['sum_yang']}'>{$ion_result_arr['sum_yang_xiuyue']}</td><td title='{$ion_result_arr['sum_yin']}'>{$ion_result_arr['sum_yin_xiuyue']}</td><td>{$ion_result_arr['count_result']}</td><td>{$ion_result_arr['result']}</td></tr>";
	}
	disp('helixing/shuju_hl_ion.html');
	exit;
}
//溶固体校核
if($jsleixin=='5'){
	$select_cy_rec	= $DB->query("SELECT `id`,`site_name` FROM `cy_rec` WHERE `cyd_id`='{$cyid}' AND `sid`>0 AND `zk_flag`>='0' ORDER BY `id`,`site_name`");
	$lines	= '';
	$i		= 0;
	while($rs_select_cy_rec = $DB->fetch_assoc($select_cy_rec)){
		$i++;
		$ion_result_arr	= array();
		$ion_result_arr = solid_balance($rs_select_cy_rec['id']);
		//行模板，每个站点的详细信息
		$lines	.= "<tr>
			<td>{$i}</td><td>{$rs_select_cy_rec['site_name']}<br>{$ion_result_arr['bar_code']}</td>
			<td>{$ion_result_arr['103']['old_vd0']}</td>
			<td>{$ion_result_arr['173']['old_vd0']}</td>
			<td title='{$ion_result_arr['125']['count_ion']}'>{$ion_result_arr['125']['old_vd0']}</td>
			<td>{$ion_result_arr['182']['old_vd0']}</td>
			<td>{$ion_result_arr['190']['old_vd0']}</td>
			<td title='{$ion_result_arr['186']['count_ion']}'>{$ion_result_arr['186']['old_vd0']}</td>
			<td>{$ion_result_arr['193']['old_vd0']}</td>
			<td>{$ion_result_arr['174']['old_vd0']}</td>
			<td>{$ion_result_arr['162']['old_vd0']}</td>
			<td>{$ion_result_arr['100']['old_vd0']}</td>
			<td>{$ion_result_arr['result_num']}</td>
			<td>{$ion_result_arr['min_result']}</td>
			<td>{$ion_result_arr['max_result']}</td>
			<td>{$ion_result_arr['count_result']}</td>
			<td>{$ion_result_arr['result']}</td></tr>";
	}
	disp('helixing/shuju_hl_solid.html');
	exit;
}
//总硬度校核
if($jsleixin=='6'){
	$select_cy_rec	= $DB->query("SELECT `id`,`site_name` FROM `cy_rec` WHERE `cyd_id`='{$cyid}' AND `sid`>0 AND `zk_flag`>='0' ORDER BY `id`,`site_name`");
	$lines	= '';
	$i		= 0;
	while($rs_select_cy_rec = $DB->fetch_assoc($select_cy_rec)){
		$i++;
		$ion_result_arr	= array();
		$ion_result_arr = zongyingdu_balance($rs_select_cy_rec['id']);
		//行模板，每个站点的详细信息
		$lines	.= "<tr>
			<td>{$i}</td><td>{$rs_select_cy_rec['site_name']}<br>{$ion_result_arr['bar_code']}</td>
			<td title='{$ion_result_arr['173']['count_value']}'>{$ion_result_arr['173']['old_vd0']}</td>
			<td title='{$ion_result_arr['174']['count_value']}'>{$ion_result_arr['174']['old_vd0']}</td>
			<td title='{$ion_result_arr['lilun_yingdu_xiangxigongshi']}'>{$ion_result_arr['lilun_yingdu']}</td>
			<td>{$ion_result_arr['103']['old_vd0']}</td>
			<td>{$ion_result_arr['count_result']}</td>
			<td>{$ion_result_arr['result']}</td></tr>";
	}
	disp('helixing/shuju_hl_zongyingdu.html');
	exit;
}
//echo $jsleixin;
$xmarr=$_SESSION[assayvalueC];
//print_rr($xmarr);
//----------------要在数组中填加要计算的元素
$danarr=array(121,187,186,198,114,104,118,119);
$arr= count($danarr);
for($i= 0;$i<$arr;$i++){
	$sql ="select ao.* from `assay_order` as ao left join `assay_pay` as ap on ao.tid=ap.id   where ap.`fzx_id`='$fzx_id' AND ao.`cyd_id` = '$cyid' and ao.vid in ($danarr[$i]) and ao.hy_flag>=0 ";
	$rows = $DB->query($sql);
	while ($row = $DB->fetch_assoc($rows)){
		if(!empty($row['ping_jun']) && !stristr($row['ping_jun'],"<")){
			$zdan	= $row['ping_jun'];
		}else{
			$zdan	= $row['_vd0'];
		}
		$danzhi[$row['sid']][$row['vid']]= $zdan;
		$tidzhi[$row['sid']][$row['vid']]= $row['tid'];
		$zarr[$row['sid']]= $row['site_name']."<br>".$row['bar_code'];
	}
}
//print_rr($danzhi);
//--------------显示三氮详细数值- 4 21 24 41 ----
if($jsleixin == '1'){
$zdbh =1;
foreach($danzhi as $key => $value){
	$zdmc= $zarr["$key"];
	if($value[186] == ''){
		$td1	= "<p  style='color:#F60'>无值</p>";
	}else{
		$td1	= $value[186];
	}
	if($value[198] == ''){
		$td2	= "<p  style='color:#F60'>无值</p>";
	}else{
		$td2	= $value[198];
	}
	if($value[187] == ''){
		$td3 = "<p  style='color:#F60'>无值</p>";
	}else{
		$td3=$value[187];
	}
	if($value[121] == ''){
		$zongdan= "<p  style='color:#F60'>无值</p>";
	}else{
		$zongdan=$value[121];
	}
	$tid1	= $tidzhi[$key][186];
	$tid2	= $tidzhi[$key][198];
	$tid3	= $tidzhi[$key][187];
	$tid4	= $tidzhi[$key][121];
	$tid1_str	= $tid2_str	= $tid3_str	= $tid4_str	= "不检测";
	if(!empty($tid1)){
		$tid1_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid1'>$td1</a>";
	}
	if(!empty($tid2)){
		$tid2_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid2'>$td2</a>";
	}
	if(!empty($tid3)){
		$tid3_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid3'>$td3</a>";
	}
	if(!empty($tid4)){
		$tid4_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid4'>$zongdan</a>";
	}
	$danhe	= $value[186] + $value[198] + $value[187];

	if($danhe <= $value[121]){
		$hl = "合理";
	}else{
		$hl = "<p style='color:#F00'>不合理</p>";
	}
	if($value[121] == ''||($value[186]==''&&$value[198]==''&&$value[187]=='')){
		$hl="<p  style='color:#F60'>缺少化验值</p>";
	}
	$tr .= temp('helixing/shuju_hl_dan_tr');
	$zdbh++;
	}

disp('helixing/shuju_hl_dan');
exit;
}
//-------------------溶解氧 高锰酸钾  化学徐氧量 五日生化氧  25 16 26 --------------------
if($jsleixin  == 2){
$zdbh =1;
foreach($danzhi as $key => $value){
	$zdmc= $zarr["$key"];
	if($value[114] == ''){
		$td1 = "<p  style='color:#F60'>无值</p>";
	}else{
		$td1 = $value[114];
	}
	if($value[118] == ''){
		$td2 = "<p  style='color:#F60'>无值</p>";
	}else{
		$td2 =$value[118];
	}
	if($value[119] == ''){
		$td3  = "<p  style='color:#F60'>无值</p>";
	}else{
		$td3 =$value[119];
	}
	if($value[104] == ''){
		$td4 = "<p  style='color:#F60'>无值</p>"; 
	}else{
		$td4 = $value[104];
	}
	$tid1 = $tidzhi[$key][114];
	$tid2 = $tidzhi[$key][118];
	$tid3 = $tidzhi[$key][119];
	$tid4 = $tidzhi[$key][104];
	$tid1_str	= $tid2_str	= $tid3_str	= $tid4_str	= "不检测";
	if(!empty($tid1)){
		$tid1_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid1'>$td1</a>";
	}
	if(!empty($tid2)){
		$tid2_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid2'>$td2</a>";
	}
	if(!empty($tid3)){
		$tid3_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid3'>$td3</a>";
	}
	if(!empty($tid4)){
		$tid4_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid4'>$td4</a>";
	}
	if($value[118] == '' || $value[119] == '' ){
		$hl  = "<p  style='color:#F60'>缺少化验值</p>";
		$value_chushu	= "缺少化验值";
	}else{
		//---合理值范围 0.2 ~ 0.8 -
		$value_chushu	= round(($value[119]/$value[118]),3);
		if($value_chushu> 0.2 && $value_chushu< 0.8 ){
			if(($value[118]>$value[104]) || ($value[118]>$value[119]) ){
				$hl = "合理";
			}	 
		}else{
			$value_chushu	= "<font color=red>$value_chushu</font>";
			$hl = "<p style='color:#F00'>不合理</p>";
		}
	}
	$tr .= temp('helixing/shuju_hl_rjy_tr');
	$zdbh++;
}
disp('helixing/shuju_hl_rjy');
exit;
}

//--------------------三氮与溶解氧 25 4 21 --
if($jsleixin == 3){
	$zdbh =1;
	foreach($danzhi as $key => $value){
		$zdmc= $zarr["$key"];
		if($value[114] == ''){
			$td1 = "<p  style='color:#F60'>无值</p>";
		}else{
			$td1 = $value[114];
		}
		if($value[198] == ''){
			$td2 = "<p  style='color:#F60'>无值</p>";
		}else{
			$td2 =$value[198];
		}
		if($value[186] == ''){
			$td3  = "<p  style='color:#F60'>无值</p>";
		}else{
			$td3 =$value[186];
		}
		$tid1 = $tidzhi[$key][114];
		$tid2 = $tidzhi[$key][198];
		$tid3 = $tidzhi[$key][186];
		$tid1_str	= $tid2_str	= $tid3_str	= "不检测";
		if(!empty($tid1)){
			$tid1_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid1'>$td1</a>";
		}
		if(!empty($tid2)){
			$tid2_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid2'>$td2</a>";
		}
		if(!empty($tid3)){
			$tid3_str	= "<a target='_blank' href='$rooturl/huayan/assay_form.php?tid=$tid3'>$td3</a>";
		}
		//总氮121 亚硝酸盐氮187 硝酸盐氮186 氨氮198
		//溶解氧114  高锰酸盐104 化学需氧量118 五日生化需氧量119
		if($value[114] != '' && ($value[114] > 5.0) ){
			if($value[186]==''||$value[114]==''){
				 $hl ="<p  style='color:#F60'>缺少化验值</p>";
				 $js="DO>5.0";
			}else{
			  if($value[186] > $value[198] ){
				$hl = "合理";
				$js = "DO>5.0 ,$value[186] > $value[198]";
			  }else{
			   $hl ="不合理";
			   $js = "DO>5.0 ,$value[186] < $value[198]";
			  }
			}
		}elseif($value[114] != ''){
			if($value[186]==''||$value[114]==''){
				 $hl ="<p  style='color:#F60'>缺少化验值</p>";
				 $js="DO<5.0";
			}else{
				if($value[186] < $value[198]){
					$hl = "合理";
					$js = "DO<5.0 ,$value[186] < $value[198]";
				}else{
					$hl = "<p style='color:#F00'>不合理</p>";
					$js = "DO<5.0 ,$value[4] > $value[198]";
				}
			}
		}elseif($value[114] == ''){
			$hl ="<p  style='color:#F60'>缺少化验值</p>";
			$js ="DO值为空";
		}
		$tr .= temp('helixing/shuju_hl_sdrj_tr');
		$zdbh++;
	}
	disp('helixing/shuju_hl_sdrj');
	exit;
}
?>
