<?php
/**
 * 功能：显示测试任务通知单的信息
 * 作者：zhengsen
 * 时间：2014-06-26
**/

include '../temp/config.php';
include_once INC_DIR.'/cy_func.php';

if($u['userid'] == ''){
	nologin();
}
$fzx_id=$u['fzx_id'];


if(($_GET['cyd_id'])){
	$cyd_id = $_GET['cyd_id'];
}
//获取cy表里的数据
$cyd	= get_cyd( $cyd_id );
$cyd	= get_userid_img('cy',array("csrw_xdcs_user","csrw_xdcy_user"),$cyd_id,$cyd);
if(empty($cyd['jcwc_date'])||$cyd['jcwc_date']=='0000-00-00'){
	$cyd['jcwc_date']=date("Y-m-d",strtotime("{$cyd['cy_date']}   +2   day"));
}
if($cyd['status']>='6'){
	$display='style="display:none"';
}
//所有采样员
$fx_user_data = array();
$user_sql= $DB->query( "SELECT * FROM `users` WHERE fzx_id='".$fzx_id."' and `group`!='0' and `group`!='测试组' and `cy`='1' order by userid" );
while( $user_rs = $DB->fetch_assoc( $user_sql) ){
        $cy_user_data[] = $user_rs['userid'];
}
if($cyd['stauts']<2){
		$cy_users.="<select name='cy_user'><option value='委托方'>委托方</option>";
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
 //委托任务不显示批名
    if($cyd['site_type']=='3'){
        $cyd['group_name'] = '委托任务（真实名称已隐藏）';
    }
//查询出是不是总中心
$hub_rs=$DB->fetch_one_assoc("SELECT * FROM hub_info WHERE id='".$fzx_id."'");
if($hub_rs['parent_id']=='0' || $cyd['status']>='6'){
	$display2='style="display:none"';
}
//导航
$csrw_title='分配检测任务';
if($cyd['status']>'5'){
	$csrw_title='修改检测任务';
}
$trade_global['daohang'][]	= array('icon'=>'','html'=>$csrw_title,'href'=>'./xd_csrw/fp_csrw.php?cyd_id='.$_GET['cyd_id']);
$_SESSION['daohang']['fp_csrw']	= $trade_global['daohang'];
$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('date_input.css');
//查询同一任务类型上一次的备注并更新到这次的任务中
$old_cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE site_type='".$cyd['site_type']."' AND id!='".$_GET['cyd_id']."' ORDER BY id DESC");
if(is_null($cyd['jc_dept'])){
	if(empty($old_cy_rs)){
		$cyd['jc_dept']='';
	}else{
		$cyd['jc_dept']=$old_cy_rs['jc_dept'];
	}
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
//获取本批次的水样类型
$wt_sql="SELECT * FROM `leixing` where id IN (".$cyd['water_type'].")";
$wt_query=$DB->query($wt_sql);
while($wt_rs=$DB->fetch_assoc($wt_query)){
$water_type_arr[]=$wt_rs['lname'];
}
$water_type_str=implode(',',$water_type_arr);
//查询所有的水样类型
$lx_sql="SELECT * FROM `leixing`";
$lx_query=$DB->query($lx_sql);
while($lx_rs=$DB->fetch_assoc($lx_query)){
	$lx_arr[$lx_rs['id']]=$lx_rs['lname'];
}
//查询rec表里的数据按照水样类型把数据整合成数组
$rec_sql = "SELECT id,sid,assay_values,bar_code,water_type,status,snpx_item,jbhs_item,zk_flag FROM `cy_rec` WHERE cyd_id ='".$cyd_id."'  AND status='1' order by sid desc";
$query_rec = $DB->query($rec_sql);
$yp_nums=0;//样品数量
while($rs_rec=$DB->fetch_assoc($query_rec)){
	if($rs_rec['sid']=='0'){
		$rec_zk_data['全程序空白'][]=$rs_rec;
		$all_bar_code_arr['全程序空白'][$rs_rec['status']][]=$rs_rec['bar_code'];
	}elseif($rs_rec['sid']=='-3'){
		$rec_zk_data['标准样品'][]=$rs_rec;
		$all_bar_code_arr['标准样品'][$rs_rec['status']][]=$rs_rec['bar_code'];
	}elseif($rs_rec['zk_flag']=='-6'){
		$all_bar_code_arr[$rs_rec['water_type']][$rs_rec['status']][]=$rs_rec['bar_code'];
		$rec_zk_data['现场平行'][]=$rs_rec;
	}
	else{
		$rec_data[$rs_rec['water_type']][]=$rs_rec;
		$all_bar_code_arr[$rs_rec['water_type']][$rs_rec['status']][]=$rs_rec['bar_code'];
	}
	if(!empty($rs_rec['jbhs_item'])){
		$rec_zk_data['加标回收'][]=$rs_rec;
	}
	if(!empty($rs_rec['snpx_item'])){
		$rec_zk_data['室内平行'][]=$rs_rec;
	}

	$yp_nums++;
}
//print_rr($all_bar_code_arr);
//循环数组获取不同水样类型下的样品编号
if(!empty($all_bar_code_arr)){
	foreach($all_bar_code_arr as $key=>$value){
		if($lx_arr[$key]){
			$water_type=$lx_arr[$key];
		}else{
			$water_type=$key;
		}
		$all_bar_code_str.=$water_type.':<font>'.get_short_barcode($value[1]).'</font>计'.count($value[1]).'个。';
		if(!empty($vlaue[0])){
			$all_bar_code_str.='其中'.get_short_barcode($value[0]).'未取回水样。<br/>';
		}else{
			$all_bar_code_str.='<br/>';
		}
	}
	//$all_bar_code_str.='<div style="text-align:right;padding-right:20%">共'.$yp_nums.'个水样</div>';
}
//print_rr($rec_zk_data);
//循环数组获取不同水样类型的检测参数
if(!empty($rec_data)){
	foreach($rec_data as $key=>$value){
		$all_jcxm_arr=array();//均需检测项目
		$bar_code_arr=array();
		$vid_arr=array();
			$water_type=$lx_arr[$key];
		foreach($value as $k=>$v){
			//获取编号和均测项目
			$bar_code_arr[]=$v['bar_code'];
			if(!empty($v['assay_values'])){
				$vid_arr=explode(',',$v['assay_values']);
			}
			if(!empty($all_jcxm_arr)){
				$all_jcxm_arr=array_intersect($vid_arr,$all_jcxm_arr);
			}else{
				$all_jcxm_arr=$vid_arr;
			}
		}
		$all_jcxm_vids=implode(',',$all_jcxm_arr);
		$jccs_str.=$water_type.':<br/>'.get_short_barcode($bar_code_arr).'均需检测项目：'.get_jccs($key,$all_jcxm_vids,$fzx_id).'共'.count($all_jcxm_arr).'项指标。<br/>';
		foreach($value as $k=>$v){
			//获取每个样品加测项目
			if(!empty($v['assay_values'])){
				$vid_arr=explode(',',$v['assay_values']);
			}
			$diff_jcxm=array_diff($vid_arr,$all_jcxm_arr);
			if(!empty($diff_jcxm)){
				$add_jcxm=implode(',',$diff_jcxm);
				$jccs_str.='<font color="green"><b>'.$v['bar_code'].'</b></font>加测项目:'.get_jccs($key,$add_jcxm,$fzx_id).'计'.count($diff_jcxm).'个<br/>';
			}

			
		}
	}
}
//循环数组获取质控的参数
if(!empty($rec_zk_data)){
	foreach($rec_zk_data as $key=>$value){
		$water_type='';
		$zk_all_jcxm_arr=array();//均需检测项目
		$bar_code_arr=array();
			$zk_way=$key;
		foreach($value as $k=>$v){
			//获取编号和均测项目
			$bar_code_arr[]=$v['bar_code'];
			if(empty($water_type)){
				if(!empty($v['water_type'])){
					$water_type=$v['water_type'];
				}else{
					$lx_zf=substr($v['bar_code'],1,1);//代表水样类型的字符
					$wtbh = get_all_wtbh();
					$water_type=array_search($water_type_bh,$wtbh);
				}
			}
			if($zk_way=='室内平行'){
				$zk_vid_arr=explode(',',$v['snpx_item']);
			}else if($zk_way=='加标回收'){
				$zk_vid_arr=explode(',',$v['jbhs_item']);
			}else{
				$zk_vid_arr=explode(',',$v['assay_values']);
			}
			if(!empty($zk_all_jcxm_arr)){
				$zk_all_jcxm_arr= array_intersect($zk_vid_arr,$zk_all_jcxm_arr);
			}else{
				$zk_all_jcxm_arr= $zk_vid_arr;
			}
		}
		$zk_all_jcxm_vids	= implode(',',$zk_all_jcxm_arr);
		if($zk_all_jcxm_vids!=''){
			$zkyq_str.=$zk_way.':<br/>'.get_short_barcode($bar_code_arr).'均需检测项目：'.get_jccs($water_type,$zk_all_jcxm_vids,$fzx_id).'共'.count($zk_all_jcxm_arr).'项指标。<br/>';
			$jiance_str	= '加测';
		}else{
			$zkyq_str.=$zk_way.':<br/>';
			$jiance_str	= '检测';
		}
		foreach($value as $k=>$v){
			//获取每个样品加测项目
			if($zk_way=='室内平行'){
				$zk_vid_arr=explode(',',$v['snpx_item']);
			}else if($zk_way=='加标回收'){
				$zk_vid_arr=explode(',',$v['jbhs_item']);
			}else{
				$zk_vid_arr=explode(',',$v['assay_values']);
			}
			$zk_diff_jcxm=array_diff($zk_vid_arr,$zk_all_jcxm_arr);
			if(!empty($zk_diff_jcxm)){
				$zk_add_jcxm=implode(',',$zk_diff_jcxm);
				$zkyq_str.='<font color="green"><b>'.$v['bar_code'].'</b></font>'.$jiance_str.'项目:'.get_jccs($water_type,$zk_add_jcxm,$fzx_id).'计'.count($zk_diff_jcxm).'个<br/>';
			}

			
		}
	}
}
//保存按钮

if(empty($cyd['csrw_xdcs_user']) || $u['system_admin']){
	$cyd['save_input']='<input class="btn btn-xs btn-primary" type="submit" name="sub" value="保存">';
}
//签字部分
//测试任务下达人签字
if(empty($cyd['csrw_xdcs_user'])&&($cyd['status']==5)){
	$cyd['csrw_xdcs_user']='<input onclick="dis_hyd(this)"   id="create_hyd" class="btn btn-xs btn-primary" type="submit" name="csrw_xdcs_user" value="签字并生成化验"/>';
}
//采样任务下达人签字
if(empty($cyd['csrw_xdcy_user'])&&($u['userid']==$cyd['create_user']||$u['userid']=='admin')){
	$cyd['csrw_xdcy_user']='<input class="btn btn-xs btn-primary" type="submit" name="csrw_xdcy_user" value="签字"/>';
	
}
disp('fp_csrw');


 ?>
