<?php
/*
 *功能：修改成果月报表信息
 *作者：zhengsen
 *时间：2014-10-22
 */
include("../temp/config.php");
include INC_DIR . "cy_func.php";
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];

//ajax改变报告的模板
if($_POST['action']=='change_bg_mb'){
	if($_POST['rec_id']&&$_POST['te_id']){
		$DB->query("UPDATE report SET te_id='".$_POST['te_id']."' WHERE cy_rec_id='".$_POST['rec_id']."'");
		if(mysql_affected_rows()){
			echo 1;
		}else{
			echo 0;
		}
	}
	exit();
}

//切换报告类型时获取报告编号
if($_POST['bg_lx']&&$_POST['cy_date']&&$_POST['cyd_id']&&!isset($_POST['bh'])){
	$y_m=date('Y-m',strtotime($_POST['cy_date']));
	$y_m_str="AND c.cy_date like'".$y_m."%'";
	$report_rs=$DB->fetch_one_assoc("select * from report where bg_lx ='".$_POST['bg_lx']."' AND cyd_id='".$_POST['cyd_id']."'  order by id desc");
	if(!empty($report_rs['bg_bh'])){
		$bg_bh=(int)$report_rs['bg_bh'];
	}else{
		$report_rs2=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx = '".$_POST['bg_lx']."' AND r.cyd_id!='".$_POST['cyd_id']."' $y_m_str AND c.fzx_id='".$fzx_id."' order by r.bg_bh desc");
		if(!empty($report_rs2['bg_bh'])){
			$bg_bh=(int)$report_rs2['bg_bh']+1;
		}else{
			$bg_bh=1;
		}
	}
	echo $bg_bh;
	exit();
}
//手动输入编号时进行验证是否重复
if($_POST['bg_lx']&&$_POST['cy_date']&&$_POST['cyd_id']&&$_POST['bh']){
	$_POST['bh']=(int)$_POST['bh'];
	$y_m=date('Y-m',strtotime($_POST['cy_date']));
	$y_m_str="AND c.cy_date like'".$y_m."%'";
	if($_POST['action']!='bef_bh'){
		$report_rs=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx='".$_POST['bg_lx']."' AND bg_bh ='".$_POST['bh']."' AND r.cyd_id!='".$_POST['cyd_id']."' $y_m_str  AND c.fzx_id='".$fzx_id."' order by r.bg_bh desc");
		if(!empty($report_rs)){
			echo 1;
		}else{
			echo 0;
		}
	}else{
		$report_rs=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx='".$_POST['bg_lx']."' AND bg_bh !='".$_POST['bh']."'  AND bg_bh<'".$_POST['bh']."' AND bg_bh!='' $y_m_str AND c.fzx_id='".$fzx_id."' order  by r.bg_bh desc");
		if(!empty($report_rs)){
			$cy_rs=$DB->fetch_one_assoc("select * from cy where id='".$report_rs['cyd_id']."'");
			echo "上次报告编号：".$report_rs['bg_lx'].$report_rs['bg_bh']."<br/>上次批次名称：".$cy_rs['group_name'];
		}else{
			echo "当前报告编号为本月初始编号";
		}
	}
	exit();
}

$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');

//向report初始化报告的信息
if(!empty($_GET['cyd_id'])){
	$query=$DB->query("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
	$nums=mysql_num_rows($query);
	if(!$nums){
		$R=$DB->query("SELECT cr.*,c.cy_date,c.ys_date FROM cy c JOIN cy_rec cr ON c.id=cr.cyd_id where cyd_id='".$_GET['cyd_id']."' and zk_flag>=0 and sid>=0 ORDER BY cr.bar_code");
		while($row = $DB->fetch_assoc($R)){
			//print_rr($row);
			$max_water_type=get_water_type_max($row['water_type'],$fzx_id);
			 $temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$max_water_type."'");
			 $te_id= $temp_rs['id'];
			 if(empty($te_id)){//默认一个模板
				$temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 ");
				$te_id = $temp_rs['id']; 
			}
			 $DB->query(" INSERT INTO report(cyd_id,water_type,cy_rec_id,state,bg_date,te_id,tab_user)values('".$_GET['cyd_id']."','".$row['water_type']."','".$row['id']."','9',curdate(),'".$te_id."','".$u['userid']."')");  
		 }	
	}
	//查询当前cy表的进度
	$cy_status_arr=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_GET['cyd_id']."'");
	if($cy_status_arr['status']==7){
		 $DB->query("UPDATE cy SET status='8' WHERE id='".$_GET['cyd_id']."'"); 
	}
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'修改报告信息','href'=>'./baogao/modi_bg_message_list.php?cyd_id='.$_GET['cyd_id'].'&cy_date='.$_GET['cy_date']);
$_SESSION['daohang']['modi_bg_message_list']	= $trade_global['daohang'];
if($_GET['cy_date']){
	$y_m=date('Y-m',strtotime($_GET['cy_date']));
	$y_m_str="AND c.cy_date like'".$y_m."%'";
}

//查询出报告编号
$report_rs=$DB->fetch_one_assoc("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
if($report_rs['sj_date']==''||$report_rs['sj_date']=='0000-00-00'){
	$report_rs['sj_date']='';
}
if($report_rs['bg_dy_date']==''||$report_rs['bg_dy_date']=='0000-00-00'){
	$report_rs['bg_dy_date']='';
}
//查询当前报告编号是否为空，如果为空在查询最大的编号然后加1
if(!empty($report_rs['bg_bh'])&&!empty($report_rs['bg_lx'])){
		$bg_lx=$report_rs['bg_lx'];
		$bg_bh=(int)$report_rs['bg_bh'];

}else{
	$bg_lx='T';
	$report_bh=$DB->fetch_one_assoc("select r.* from report r JOIN cy c ON r.cyd_id=c.id where bg_lx = '".$bg_lx."' $y_m_str AND c.fzx_id='".$fzx_id."' order by bg_bh desc");
	if(!empty($report_bh)){
		$bg_bh=(int)$report_bh['bg_bh']+1;
	}else{
		$bg_bh=1;
	}
}

/*//检验类别
$jy_lb_arr=array("监督","Supervision","抽样","Sample","委托","Entrust","常规","Routine");
$jy_lb_option='';
foreach($jy_lb_arr as $key=>$value){
	if($report_rs['jy_lb']==$value){
		$jy_lb_option.="<option value=".$value." selected=\"selected\">".$value."</option>";
	}else{
		$jy_lb_option.="<option value=".$value.">".$value."</option>";
	}
}*/
//样品来源
$jy_lb_arr=array("自采","送样");
$jy_lb_option='';
foreach($jy_lb_arr as $key=>$value){
	if($report_rs['jy_lb']==$value){
		$jy_lb_option.="<option value=".$value." selected=\"selected\">".$value."</option>";
	}else{
		$jy_lb_option.="<option value=".$value.">".$value."</option>";
	}
}
//日期类型
$date_lx_arr=array("采样日期","Take sample date","收样日期","Sample collection date");
$date_lx_options='';
foreach($date_lx_arr as $key=>$value){
	if($value==$report_rs['date_lx']){
		$date_lx_options.="<option value='".$value."' selected=\"selected\">".$value."</option>";
	}else{
		$date_lx_options.="<option value='".$value."'>".$value."</option>";
	}
}
//水样类型
$lx_sql="SELECT * FROM leixing WHERE (fzx_id=0 or fzx_id='".$fzx_id."')";
$lx_query=$DB->query($lx_sql);

while($lx_rs=$DB->fetch_assoc($lx_query)){
	$lx_rs_arr[$lx_rs['id']]=$lx_rs['lname'];
}
//查询出所有检测标准
$jcbz_list	= array();
$jcbz_sql	= "SELECT * FROM `n_set` WHERE `module_name`='jcbz_bh' ORDER BY module_value2 *1,module_value4,module_value1";
$jcbz_query	= $DB->query($jcbz_sql);
while ($jcbz_row = $DB->fetch_assoc($jcbz_query)) {
	$jcbz_list[$jcbz_row['id']] = $jcbz_row['module_value1'];
}
//查询当前批次的所有站点
$sql_report2="SELECT r.*,cr.site_name,cr.ys_zt FROM report r LEFT JOIN cy_rec cr ON r.cy_rec_id=cr.id AND r.cyd_id=cr.cyd_id WHERE r.cyd_id='".$_GET['cyd_id']."'";
$query_report2=$DB->query($sql_report2);
while($report_rs2=$DB->fetch_assoc($query_report2)){
	$cr_id=$report_rs2['cy_rec_id'];
	$wt_options="<option value=''>不显示水样类型</option>";
	foreach($lx_rs_arr as $key=>$value){
		if($report_rs2['water_type']==$key){
			$wt_options.="<option value=".$key." selected=\"selected\">".$value."</option>";
		}else{
			$wt_options.="<option value=".$key.">".$value."</option>";
		}
	}
	//站点所选择的排序
	$xm_px_arr='';
	$xm_px_arr.="<option value=''>--请选择--</option>";
	$result_px = $DB->query("select id,module_value2 from n_set where module_name='xm_px' and module_value3!='history'");
	while ($xm_px = $DB->fetch_assoc($result_px)) {
		if($report_rs2['xm_px'] == $xm_px['id']){
			$xm_px_arr.="<option value=".$xm_px['id']." selected='selected'>".$xm_px['module_value2']."</option>";//模板排序名称
		}else{
			$xm_px_arr.="<option value=".$xm_px['id'].">".$xm_px['module_value2']."</option>";//模板排序名称
		}
	}
	//默认选择每个水样类型的任务类型
	$bg_lx_arr=array('T','R');//R 常规任务 T 委托任务
	$options='';
	foreach($bg_lx_arr as $key=>$value){//****
		if($report_rs2['bg_lx']==$value){
			$options.="<option value=".$value." selected=\"selected\">".$value."</option>";
		}else{
			$options.="<option value=".$value.">".$value."</option>";
		}
	}//****

	//样品状态
	if($report_rs2['yp_zt'] == '' && $report_rs2['ys_zt'] != ''){
		$report_rs2['yp_zt'] = $report_rs2['ys_zt'];
	}
	//所选择年份
	$year_list='';//防止影响下一步循环
	for($i=2014;$i<2018;$i++){
		if($i == $report_rs2['year']){
			$year_list.="<option value=".$i." selected='selected'>".$i."</option>";
		}else{
			$year_list.="<option value=".$i.">".$i."</option>";
		}
	}
	//检测标准下拉菜单
	$jcbz_options	= '';
	foreach ($jcbz_list as $key => $value) {
		if($report_rs2['jcbz_id']== $key){
			$jcbz_options	.= "<option value='{$key}' selected>{$value}</option>";
		}else{
			$jcbz_options	.= "<option value='{$key}'>{$value}</option>";
		}
	}
	$modi_bg_message_line.=temp("bg/modi_bg_message_line");
}

disp("bg/modi_bg_message_list");

?>