<?php
/*
**作者：高培华
**时间：2015/6/8
**作用：管理者功能界面下的任务进度管理功能模块
*/
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];//分中心id
//#########导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'任务进度管理','href'=>"admin/jindu.php?site_type={$_GET['site_type']}&year={$_GET['year']}&month={$_GET['month']}&fzx={$_GET['fzx']}");
//将导航信息记录到session中
$_SESSION['daohang']['jindu']	= $trade_global['daohang'];
//年份选择
$nowyear  = date("Y");
if(!empty($_GET['year'])){
	$riqiy	= $_GET['year'];
}else{
	$riqiy	= $nowyear;
}
for($i=0;$i>-6;$i--){
	$riqiyear	= date("Y", strtotime("$i year"));
	if($_GET['year']!=$riqiyear){
		$ytime	.= "<option value='$riqiyear'>$riqiyear</option>";
	}else{
		$ytime	.= "<option value='$riqiyear' selected='selected'>$riqiyear</option>";
	}
}

//月份的选择  
$nowdate	= date("m");
if(!empty($_GET['month'])){
	$riqi	= $_GET['month'];
}else{
	$riqi	= $nowdate;
}
//$nowdate = intval(date("m"));
$nowdate	= ( empty($_GET['year'])||$_GET['year'] == date('Y') ) ? intval(date("m")) : 12;
for ($i=$nowdate;$i>0;$i--){
	//if($i==$riqi) continue;
	$i  = $i>9 ? $i:'0'.$i;
	if($_GET['month']!=$i){
		$mtime	.= "<option value='$i'>$i</option>";
	}else{
		$mtime	.= "<option value='$i' selected='selected'>$i</option>";
	}
}

//获取任务类型
//print_rr($global['site_type']);
if(isset($_GET['site_type'])&&$_GET['site_type']!=''){//判断是否有传值
	$site_type	= $_GET['site_type'];
	$where_type	= " and site_type='$site_type' ";
}else{
	$site_type	= '123';
}
$type	= "<option value=''>全部类型</option>";
foreach($global['site_type'] as $key =>$value){
	if($site_type==$key&&isset($site_type)&&$_GET['site_type']!=''){
		$type	.= "<option selected='selected' value='$key'>$value</option>";
	}else{
		$type	.= "<option value='$key'>$value</option>";
	}
}
//分中心列表
$fzx_list       = '';
$fzx_arr        = array();
if($u['is_zz']=='1'){
		$fzx_list       .= "分中心列表<select id='fzx' name='fzx'  onchange=\"jiansuo()\"><option value='全部'>全部</option>";
		$hub_list_sql   = $DB->query("SELECT * FROM `hub_info` WHERE 1");
		$hub_num	= $DB->num_rows($hub_list_sql);
		while($hub_list_rs = $DB->fetch_assoc($hub_list_sql)){
				$fzx_arr[$hub_list_rs['id']]    = $hub_list_rs['hub_name'];
				if($_GET['fzx'] == $hub_list_rs['id'] || (empty($_GET['fzx']) && $fzx_id == $hub_list_rs['id'])){
						$fzx_list	.= "<option value='{$hub_list_rs['id']}' selected>{$hub_list_rs['hub_name']}</option>";
				}else{
						$fzx_list	.= "<option value='{$hub_list_rs['id']}'>{$hub_list_rs['hub_name']}</option>";
				}
		}
	$fzx_list	.= "</select>";
	if($hub_num<=1){
		$fzx_list	= '';
	}
}
//sql条件
$sql_where      = '';
if(!empty($_GET['fzx'])){
	if($_GET['fzx'] != '全部'){
		$sql_where	.= " AND fzx_id=".$_GET['fzx']." ";
	}
}else{
	$sql_where	.= " AND fzx_id={$fzx_id} ";
}
if(!empty($where_type)){
	$sql_where	.= $where_type;
}
//查询采样单的id，任务开始时间，化验单的总数，化验单编号，要求检测完成时间
/*$sql	= "select a.cyd_id,a.create_date,count(a.assay_over) zong,c.cyd_bh,c.jcwc_date 
		from assay_order a join cy c on a.cyd_id=c.id join assay_pay ap on ap.id=a.tid 
		where ap.`is_xcjc`='0' $sql_where and c.cyd_bh like '%$riqiy$riqi%' $where_type group by c.cyd_bh";
	*/
$begin_date	= "$riqiy-$riqi-01";
$next_month	= $riqi+1;
if(strlen($next_month)<2){
	$next_month	= '0'.$next_month;
	$last_date	= "$riqiy-".$next_month."-01";
}else if($next_month==13){
	$next_month	= "01";
	$last_date	= ($riqiy+1)."-".$next_month."-01";
}else{
	$last_date	= "$riqiy-".$next_month."-01";
}
$cyd	= array();
$sql	= "SELECT `id`,`fzx_id`,`cyd_bh`,`cy_date`,`status`,`group_name`,`jcwc_date`,`xdcs_qz_date` FROM `cy` WHERE `cy_date`>='$begin_date' AND `cy_date`<'$last_date' $sql_where ORDER BY `fzx_id`,`status` desc";
$query	= $DB->query($sql);
$no_finish_cyd	= $fzx_id_arr	= array();
$cyd_xuhao	= 0;
while($sel=$DB->fetch_assoc($query)){
	$cyd[$sel['id']]['jcwc_date']	= $sel['jcwc_date'];
	$cyd[$sel['id']]['cy_date']		= $sel['cy_date'];
	$cyd[$sel['id']]['cyd_bh']		= $sel['cyd_bh'];
	$cyd[$sel['id']]['group_name']	= $sel['group_name'];
	//$cyd[$sel['id']]['xdcs_qz_date']= $sel['xdcs_qz_date'];
	//没有完成时间的按照采样日期走
	$cyd_id_arr[]=$sel['id'];
	if(@!in_array($sel['fzx_id'], $fzx_id_arr)){
		$cyd_xuhao	= 0;
		$fzx_id_arr[]	= $sel['fzx_id'];
	}
	$cyd_xuhao++;
	if($sel['status'] < '6'){
		$no_finish_cyd[$sel['fzx_id']]	.= "<tr>
					<td>$cyd_xuhao</td>
					<td align=left>{$sel['group_name']}</td>
					<td>{$sel['cyd_bh']}</td>
					<td>{$sel['cy_date']}</td>
					<td>{$sel['jcwc_date']}</td>
					<td>
						<div class='progress'>
							<div class='progress-bar' data-percent='0%' style='width:0%;background-color:rgb(168,30,34)'></div>
						</div>
						<!--<progress max=100 value=85 style='width:80%'><span id='objprogress'>85</span>%</progress>-->
					</td>
					<td>未生成化验任务</td>
					<td><a href='$rooturl/cy/cyrw_list.php?cy_date=".$riqiy."-".$riqi."&site_type={$_GET['site_type']}&year=$riqiy&month=$riqi&cyd_id={$sel['id']}&fzx={$sel['fzx_id']}&fx_user=全部' target='_blank'>查看</a></td>
				</tr>";
	}
}
//print_rr($arr);
//判断是否有数据查询出来
if(!empty($cyd_id_arr)){
	$cyd_id=implode(',',$cyd_id_arr);
	//查询每个批次下的采样单完成的数量
	$xuhao	= $i	= 0;
	$lines	= $order_by	= $old_fzx	= '';
	//"select ap.cyd_id,SUM(ap.`over` NOT IN ('未开始','已开始')) as finish_value,count(ap.`id`) as all_value,SUM(ap.`is_xcjc`='1') as xc_value,ap.fzx_id,cy.`cyd_bh`,cy.`status`,cy.`group_name`,`jcwc_date`,cy.`xdcs_qz_date` from  `assay_pay` ap INNER JOIN `cy` ON ap.cyd_id=cy.id WHERE cy.`cy_date`>='2015-05-01' AND cy.`cy_date`<'2015-06-01' group by ap.cyd_id ORDER BY `ap`.`cyd_id` ASC"
	$hyd_sql="select a.cyd_id, a.create_date,SUM(a.`assay_over`='1' or a.`assay_over`='over') as finish_value,count(a.`id`) as all_value,SUM(ap.`is_xcjc`='1') as xc_value,ap.fzx_id from assay_order a join assay_pay ap on ap.id=a.tid where a.cyd_id in ($cyd_id) group by a.cyd_id  ORDER BY ap.fzx_id";
	$hyd_query	= $DB->query($hyd_sql);
	$all_num	= $DB->num_rows($hyd_query);
	while($hyd_sel=$DB->fetch_assoc($hyd_query)){
		$i++;
		//$xuhao++;//序号
		$zhi=@round($hyd_sel['finish_value']*100/$hyd_sel['all_value']);
		if($zhi<=25){
			$bg_color="rgb(168,30,34)";
		}elseif($zhi>25&&$zhi<=50){
			$bg_color='rgb(209,145,0)';
		}elseif($zhi>50&&$zhi<=75){
			$bg_color="rgb(0,201,206)";
		}else{
			$bg_color="rgb(132,208,193)";
		}
		//将没有生成化验单的任务显示出来
		if(($old_fzx != '' && $old_fzx!= $hyd_sel['fzx_id']) || $i==$all_num){
			//$lines	.= $no_finish_cyd[$hyd_sel['fzx_id']];
		}
		//如果全部都是现场检测项目的化验单，就不显示这条记录
		if($hyd_sel['all_value'] != $hyd_sel['xc_value']){
			//不同分中心之间分开显示
			if($old_fzx!= $hyd_sel['fzx_id']){

				if(empty($fzx_arr[$hyd_sel['fzx_id']])){
					if(empty($fzx_arr)){
						$fzx_name	= $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$hyd_sel['fzx_id']}'");
						$fzx_arr[$hyd_sel['fzx_id']]	= $fzx_name['hub_name'];
					}else{
						$fzx_arr[$hyd_sel['fzx_id']]    = "未找到改分中心名称（{$hyd_sel['fzx_id']}）";
					}
				}
				$lines	.= "<tr><th colspan='8'>{$fzx_arr[$hyd_sel['fzx_id']]}</th></tr>";
				$old_fzx= $hyd_sel['fzx_id'];
				$xuhao	= 0;
			}
			$xuhao++;
			$lines.="<tr>
					<td>$xuhao</td>
					<td align=left>{$cyd[$hyd_sel['cyd_id']]['group_name']}</td>
					<td>{$cyd[$hyd_sel['cyd_id']]['cyd_bh']}</td>
					<td>{$cyd[$hyd_sel['cyd_id']]['cy_date']}</td>
					<td>{$cyd[$hyd_sel['cyd_id']]['jcwc_date']}</td>
					<td>
						<div class='progress'>
							<div class='progress-bar' data-percent='$zhi%' style='width:$zhi%;background-color:$bg_color'></div>
						</div>
						<!--<progress max=100 value=85 style='width:80%'><span id='objprogress'>85</span>%</progress>-->
					</td>
					<td>$hyd_sel[finish_value]/$hyd_sel[all_value]</td>
					<td nowrap><a href='$rooturl/huayan/ahlims.php?app=pay_list&cyd_id={$hyd_sel['cyd_id']}&year=$riqiy&month=$riqi&fzx={$hyd_sel['fzx_id']}&fx_user=全部'>查看检测任务</a>&nbsp;|&nbsp;<a href='$rooturl/cy/cy_record.php?cyd_id={$hyd_sel['cyd_id']}'>查看采样任务</a></td>
				</tr>";
		}
	}
}//是否有查询出数据的查询
disp("jindu");
?>
