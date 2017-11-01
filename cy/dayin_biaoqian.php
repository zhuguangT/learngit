<?php
/**
* 
*  
*/
include "../temp/config.php";
include(INC_DIR."cy_func.php");
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'样品标签打印','href'=>'./cy/dayin_biaoqian.php?cyd_id='.$_GET['cyd_id']);
//将导航信息记录到session中
$_SESSION['daohang']['dayin_biaoqian']  = $trade_global['daohang'];
if($_GET['print'] != 'print'){
	$trade_global['js']             = array('lims/dayin_biaoqian.js');
}
if(empty($u['userid'])){
	nologin();
}
$cy_arr = $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='{$_GET['cyd_id']}'");
$rec_query=$DB->query("SELECT * FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND `sid`>='0'  ORDER BY id");//ORDER BY bar_code");// AND status='1' AND sid>-1 ORDER BY bar_code");
$i=0;
$data=$value_arr=array();
$yp_bar=0;
while($rec_rs=$DB->fetch_assoc($rec_query)){
	if($_GET['bianhao'] == 'xinbianhao'){
		if($rec_rs['bar_code']==''){
			if(!empty($rec_rs['water_type'])){
				$fater_water	= $rec_rs['water_type'];//没有水样类型的样品，自动按照上一个样品的水样类型生成编号
			}
			$rec_rs['bar_code'] = new_bar_code($cy_arr['site_type'],$fater_water,$cy_arr['cy_date']);
			$DB->query("update cy_rec set bar_code='".$rec_rs['bar_code']."' where id='".$rec_rs['id']."'");
		}
	}
	$data[]=$rec_rs;
	$assay_values=explode(',',$rec_rs['assay_values']);
	$value_arr=array_merge($assay_values,$value_arr);
	if($rec_rs['bar_code']){
		$yp_bar = '1';
	}
}
//采样瓶信息
//采样接收人签字后通知单的容器信息获取cy表的rq_info
if($cyd['cy_rwjs_qz_date']!=''&!empty($cyd['rq_info']))
{
	$cy_rq_info=$DB->fetch_one_assoc("SELECT rq_info FROM `cy` WHERE id='".$_GET['cyd_id']."'");
	$rq_data=json_decode($cy_rq_info['rq_info'],true);
}else{//查询出容器关联的项目、保存剂、规格
	$rq_sql="SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
	$rq_query=$DB->query($rq_sql);
	$rq_data=array();
	while($rq_rs=$DB->fetch_assoc($rq_query))
	{
		$rq_value=explode(',',$rq_rs['vid']);
		if(array_intersect($value_arr,$rq_value))
		{
			$rq_data[$rq_rs['id']]['rq_name']=$rq_rs['rq_name'];
			$rq_data[$rq_rs['id']]['bcj']=$rq_rs['bcj'];
			$rq_data[$rq_rs['id']]['rq_size']=$rq_rs['rq_size'];
			$rq_data[$rq_rs['id']]['vid']=$rq_value;
		}
	}
}
foreach($data as $key=>$value){
	//采样任务没有审核的时候根据采样瓶默认显示
	if($cy_arr['status']<4){
		$rowarr = explode(',',$value['assay_values']);
		//获取json转换为数组
		$json_arr = json_decode($value['json'],true);
		//获取数组交集,不同材质的瓶子是否包含站点要化验的项目
		$rq_num = $z_nums = 0;
		foreach($rq_data as $k=>$v)
		{
			if(!empty($json_arr['rq'][$k]))
			{
				$rq_num=$json_arr['rq'][$k];
				$z_nums+=$rq_num;
			}
			else
			{
				if(array_intersect($rowarr,$v['vid']))
				{
					if(($cyd['cy_rwjs_qz_date'] <'2015-3-10')&&($cyd['cy_rwjs_qz_date']!='')){
						$rq_num=1;
					}else{
						$rq_num=$v['mr_shu'];
					}
				}
				else
				{
					$rq_num=0;
				}
				$z_nums+=$rq_num;
			}
		}
		$value['rq_num']  = $z_nums;
	}
	$i++;
	if($value['sid']>0&&$value['zk_flag']<0){
		$value['site_name']=$value['site_name']."(平行)";
	}
	//生成化验单前可以修改样品编号
	$modify_bar_code	= ' onclick="alert(\'该批次已生成化验单，无法修改样品编号！\');" ';
	if($cy_arr['status']<6 && $_GET['print']!='print'){
		$modify_bar_code	= 'title="点击修改样品编号" onclick="gt(this)"  shunxu="'.$i.'" cid="'.$value['id'].'" style="color:blue;cursor:pointer;"';
	}
	if($value['status']=='-1'){
		$site_code_lines.='<tr tongji="tong"><td>'.$i.'</td><td>'.$value['site_name'].'</td><td '.$modify_bar_code.'>'.$value['bar_code'].'</td><td>未采到水样</td><td><font color="red"></font></td></tr>';
	}else{
		$site_code_lines.='<tr tongji="tong"><td>'.$i.'</td><td>'.$value['site_name'].'</td><td '.$modify_bar_code.'>'.$value['bar_code'].'</td><td>
	<select class="bh_sum" old_val="'.$value['rq_num'].'" name="bh['.$value['bar_code'].']">  
	<option value="'.$value['rq_num'].'">'.$value['rq_num'].'</option>
	<option value="0">0</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
	<option value="9">9</option>
	<option value="10">10</option>
 <option value="11">11</option>
	<option value="12">12</option>
	<option value="13">13</option>
	<option value="14">14</option>
	<option value="15">15</option>
</select></td><td><font color="red"></font></td></tr>';
	}
}
$anniu = "";
if($yp_bar){
	$anniu = "<input type=\"button\" value=\"验收样品\" class=\"btn btn-primary btn-sm no-print\" clicked='' onclick='ys(this)' cyd_id={$_GET['cyd_id']}>
	<input type=\"submit\" value=\"打印标签\" class=\"btn btn-primary btn-sm no-print\" clicked='' />
			<input type=\"button\" value=\"打印站点编码对照表\" class=\"btn btn-primary btn-sm no-print\" onclick=\"var url = window.location.href;url+='&print=print';window.open(url);\" />";
}else{
	$anniu = "<input type=\"button\" value=\"点击编号\" class=\"btn btn-primary btn-sm no-print\" clicked='' onclick='bianhao(this)' cyd_id={$_GET['cyd_id']}>";
}
disp("dayin_biaoqian.html");
?>
