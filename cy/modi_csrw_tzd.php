<?php
/**
 * 功能：显示测试任务通知单的信息
 * 作者：zhengsen
 * 时间：2014-06-26
**/

include '../temp/config.php';
include_once INC_DIR.'/cy_func.php';
$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('date_input.css');
//导航

$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样任务列表','href'=>'./cy/cyrw_list.php'),
	array('icon'=>'','html'=>'修改及确认采样任务','href'=>'./cy/modi_csrw_tzd.php?cyd_id='.$_GET['cyd_id'])
);
if($u['userid'] == ''){
	nologin();
}
$fzx_id=$u['fzx_id'];
if($_GET['cyd_id']){
	$cyd_id = $_GET['cyd_id'];
}else{
	 exit('bad request');
}

//获取cy表里的数据
$cyd      = get_cyd( $cyd_id );
if(empty($cyd['jcwc_date'])){
	$cyd['jcwc_date']=date('Y-m-d');
}
//所有采样员
$fx_user_data = array();
$user_sql= $DB->query( "SELECT * FROM `users` WHERE fzx_id='".$fzx_id."' and `group`!='0' and `group`!='测试组' and `cy`='1' order by userid" );
while( $user_rs = $DB->fetch_assoc( $user_sql) ){
        $cy_user_data[] = $user_rs['userid'];
}
if(!$cyd['cy_user_qz']){
		$cy_users.="<select name='cy_user'><option value=''>请选择</option>";
		foreach($cy_user_data as $k=>$v){
			if($cyd['cy_user']==$v){
				$cy_users.="<option value=".$v." selected=\"selected\">".$v."</option>";
			}else{
				$cy_users.="<option value=".$v.">".$v."</option>";
			}
		}
		$cy_users.="</select>";
		$cy_users.="<span style=\"padding-left:20px\"><select name='cy_user2'><option value=''>请选择</option>";
		foreach($cy_user_data as $k=>$v){
			if($cyd['cy_user2']==$v){
				$cy_users.="<option value=".$v." selected=\"selected\">".$v."</option>";
			}else{
				$cy_users.="<option value=".$v.">".$v."</option>";
			}
		}
		$cy_users.="</select></span>";
	
}else{
	if(!empty($cyd['cy_user'])&&$cyd['cy_user2']){
		$cy_users=$cyd['cy_user'].' 、'.$cyd['cy_user2'];
	}else{
		$cy_users=$cyd['cy_user'].$cyd['cy_user2'];
	}
}
//查询同一任务类型上一次的备注并更新到这次的任务中
$old_cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE `fzx_id`='$fzx_id' AND site_type='{$cyd['site_type']}' AND status>1 AND id!='{$_GET['cyd_id']}' ORDER BY id DESC");
if(is_null($cyd['jc_dept'])){
	$hub_rs=$DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE id='".$fzx_id."'");
	$cyd['jc_dept']=$hub_rs['hub_name'];
	$DB->query("UPDATE `cy`	SET jc_dept='".$cyd['jc_dept']."' WHERE id='".$_GET['cyd_id']."'");
}
if(is_null($cyd['jc_yiju'])){
	if(empty($old_cy_rs)){
		$cyd['jc_yiju']='';
	}else{
		$cyd['jc_yiju']=$old_cy_rs['jc_yiju'];
	}
	$DB->query("UPDATE `cy`	SET jc_yiju='".$cyd['jc_yiju']."' WHERE id='".$_GET['cyd_id']."'");
}
if(is_null($cyd['csrw_tzd_note'])){
	if(empty($old_cy_rs)){
		$cyd['csrw_tzd_note']='';
	}else{
		$cyd['csrw_tzd_note']=$old_cy_rs['csrw_tzd_note'];
	}
	$DB->query("UPDATE `cy`	SET csrw_tzd_note='".$cyd['csrw_tzd_note']."' WHERE id='".$_GET['cyd_id']."'");
}
//ajax判断任务是否已经接受，接收后无法打开修改采样任务页面
if($_GET['action']=='check_status'){
	if($cyd['status']<='5' && $cyd['cy_user_qz'] == '' && $cyd['cy_user_qz2'] == ''){
		echo '0';
	}else{
		echo '1';
	}
	exit();
}
//获取本批次的水样类型
if(!empty($cyd['water_type'])){
	$wt_sql="SELECT * FROM `leixing` where id IN (".$cyd['water_type'].")";
	$wt_query=$DB->query($wt_sql);
	while($wt_rs=$DB->fetch_assoc($wt_query)){
	$water_type_arr[]=$wt_rs['lname'];
	}
	$water_types=implode(',',$water_type_arr);
}

//查询样品的数量
$yp_nums=0;//样品数量
$rec_sql = "SELECT count(*) as yp_nums  FROM `cy_rec` WHERE cyd_id ='".$cyd_id."' AND sid>-1";
$yp_rs=$DB->fetch_one_assoc("SELECT count(*) as yp_nums  FROM `cy_rec` WHERE cyd_id ='".$cyd_id."' AND sid>-1");
if(!empty($yp_rs)){
	$yp_nums=$yp_rs['yp_nums'];
}

//保存按钮
if(empty($cyd['csrw_xdcs_user'])){
	$cyd['save_input']='<input class="btn btn-sm btn-primary" type="submit" name="sub" value="保存">';
}
//签字部分
/*//测试任务下达人签字
if(!empty($cyd['csrw_xdcy_user'])&&empty($cyd['csrw_xdcs_user'])&&$cyd['status']=='5'){
	$cyd['csrw_xdcs_user']='<input class="btn btn-xs btn-primary" type="submit" name="csrw_xdcs_user" value="签字"/>';
}*/
//采样任务下达人签字
if(empty($cyd['csrw_xdcy_user'])&&($u['userid']==$cyd['create_user']||$u['userid']=='admin')){
	$cyd['csrw_xdcy_user']='<input class="btn btn-xs btn-primary" type="submit" name="csrw_xdcy_user" value="签字"/>';
	
}




/**
 * 功能：显示添加删除站点、项目、质控的页面
 * 作者：zhengsen
 * 时间：2014-08-12
**/
//查询出不同的水样类型下项目的名称
$aj_value_sql="SELECT n.module_value2,aj.vid,aj.value_C FROM `n_set` n JOIN `assay_jcbz` aj ON n.id=aj.jcbz_bh_id WHERE  n.module_value3='1'";
$aj_value_query=$DB->query($aj_value_sql);
while($aj_value_rs=$DB->fetch_assoc($aj_value_query)){
	$aj_value_arr[$aj_value_rs['module_value2']][$aj_value_rs['vid']]=$aj_value_rs['value_C'];
}

//查询出cy_rec表的数据
$rec_sql = "SELECT * FROM cy_rec WHERE cyd_id = '".$_GET['cyd_id']."' AND status = '1' AND sid > -1  ORDER BY id";
$rec_query = $DB->query($rec_sql);
$data = array();
while($row = $DB->fetch_assoc($rec_query)) {
	$vid_str='';
	$water_type_max='';
	$vid_options='';
	$vid_name_arrs=array();
	$vid_arrs=array();
	if($row['zk_flag']=='-6'){
		$row['site_name']=$row['site_name'].'<br/>(平行)';
	}
	if(!empty($row['water_type'])){
		$prev_water_type	= $row['water_type'];//记录上一个样品的水样类型，当全程序空白等样品没有水样类型是可以用这个来查询方法
	}
	if(empty($row['water_type'])){
		$water_type_max=get_water_type_max($prev_water_type,$fzx_id);
		$water_type_str='='.$water_type_max;
	}else{
		$water_type_max=get_water_type_max($row['water_type'],$fzx_id);
		if($row['water_type']==$water_type_max){
			$water_type_str='= '.$water_type_max;
		}else{
			$water_type_str='in ('.$water_type_max.','.$row['water_type'].')';
		}
	}
	$vid_arrs=explode(',',$row['assay_values']);//站点项目
	//查询某水样类型下有方法的项目
	$xmfa_value_sql="SELECT av.id,av.value_C FROM `assay_value` av JOIN `xmfa` x ON av.id=x.xmid WHERE  x.fzx_id='".$fzx_id."'  AND x.mr='1' AND x.act='1' AND x.lxid ".$water_type_str."  GROUP BY av.id ORDER BY av.id";
	$xmfa_value_query=$DB->query($xmfa_value_sql);
	while($xmfa_value_rs=$DB->fetch_assoc($xmfa_value_query)){

			$vid_options.='<option value='.$xmfa_value_rs['id'].'>'.$xmfa_value_rs['value_C'].'</option>';
	}
	foreach($vid_arrs as $key=>$value){
		if(!empty($aj_value_arr[$water_type_max][$value])){
			$vid_name_arrs[$value]=$aj_value_arr[$water_type_max][$value];
		}else{
			$vid_name_arrs[$value]=$_SESSION['assayvalueC'][$value];
		}
	}
	$vid_str=implode(',',$vid_name_arrs);
	$cla='';
	if($row['zk_flag']<0){
		$cla=$row['sid'];
	}
	$add_hy_item_lines.="<tr style='height:40px' class=".$cla.">
		<td >{$row['site_name']}
		<a class='red icon-remove bigger-130' href='#' onclick='ajax_del_site(this,".$row['id'].",".$row['cyd_id'].",".$row['sid'].")' title='删除'></a></td>
		<td align='left'  style=\"font-size:12px\">{$vid_str}</td>
		<td><select name='vid[{$row['id']}][]' class='chosen-select' multiple='' data-placeholder='请选择要添加或删除的项目...' style='display: none;width:400px;'>".$vid_options."</select></td>
		</tr>";
}


disp('modi_csrw_tzd.html');

 ?>
