<?php
/**
 * 功能：显示采样通知单
 * 作者：zhengsen
 * 时间：2014-04-15
**/
include '../temp/config.php';
require_once  "../inc/cy_func.php";
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样任务列表','href'=>'./cy/cyrw_list.php'),
	array('icon'=>'','html'=>'采样通知单','href'=>'./cy/cy_tzd.php?cyd_id='.$_GET['cyd_id'])
);
//点击打印时的显示
if($_GET['print']){
	$bar='';
	$print="<link href=\"$rooturl/css/lims/print.css\" rel=\"stylesheet\" />";

}
else{
	$dayin='<a href="?cyd_id='.$_GET['cyd_id'].'&print=1&ajax=1" target="_blank" class="btn btn-primary btn-sm"><i class="icon-print bigger-160"></i>打印</a>';
} 
$fzx_id=$u['fzx_id'];
//更新cy_rec表的json中容器的个数
if($_GET['action']=='modi_ps'){
	if($_GET['is_modi_all']){
		$sql_rec="SELECT id,json FROM `cy_rec`	WHERE cyd_id='".$_GET['cyd_id']."'";
		$query_rec=$DB->query($sql_rec);
		while($rs_rec=$DB->fetch_assoc($query_rec)){
			$rec_json_arr = json_decode($rs_rec[json],true);
			$rec_json_arr['rq'][$_GET['rq_id']]=$_GET['ps'];
			//将数组转换为json格式然后保存
			$rec_json = json_encode($rec_json_arr);
			$update_sql = "UPDATE `cy_rec` SET `json`='".$rec_json."' WHERE `id`='".$rs_rec['id']."'";
			$DB->query($update_sql);
		}
	}else{
		$sql_rec="SELECT json FROM `cy_rec` WHERE `id` ='".$_GET['id']."'";
		$rs_rec=$DB->fetch_one_assoc($sql_rec);
		$rec_json_arr = json_decode($rs_rec[json],true);
		$rec_json_arr['rq'][$_GET['rq_id']]=$_GET['ps'];
		//将数组转换为json格式然后保存
		$rec_json = json_encode($rec_json_arr);
		$update_sql = "UPDATE `cy_rec` SET `json`='".$rec_json."' WHERE `id`='".$_GET[id]."'";
		$DB->query($update_sql);
	}
}
//修改备注
if($_POST['action']=='save_note'){
	$DB->query("UPDATE `cy`	SET note='".$_POST['note']."' WHERE id='".$_POST['cyd_id']."'");
	exit();
}
//查询出本批次下的现场项目
$sql_xc_jcxm="SELECT value_C FROM `assay_pay`  ap  JOIN `assay_value` av ON ap.vid=av.id WHERE ap.is_xcjc='1' AND ap.cyd_id='".$_GET['cyd_id']."' GROUP BY ap.vid";
$query_xc_jcxm=$DB->query($sql_xc_jcxm);
$xc_jcxm='';
while($rs_xc_jcxm=$DB->fetch_assoc($query_xc_jcxm))
{
	$xc_jcxm.="<label class='xc_jcxm'><input type='checkbox' onclick='return false' checked='checked'>".$rs_xc_jcxm['value_C']."</label>";
}

if($_GET['cyd_id']>0)
{
	$cyd = get_cyd( $_GET['cyd_id'] );
	//将普通签名转换为电子签名
	$cyd	= get_userid_img('cy',array("cy_rwxd_user","cy_rwjs_user","cy_rwjs_user2"),$_GET['cyd_id'],$cyd);
	$_GET['cyid']=$_GET[cyd_id];
}
if(is_null($cyd['note'])){
	//查询同一任务类型上一次的备注并更新到这次的任务中
	$old_cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE site_type='".$cyd['site_type']."' AND status>1 AND id!='".$_GET['cyd_id']."' ORDER BY id DESC");
	if(empty($old_cy_rs)){
		$cyd['note']='';
	}else{
		$cyd['note']=$old_cy_rs['note'];
	}
	$DB->query("UPDATE `cy`	SET note='".$cyd['note']."' WHERE id='".$_GET['cyd_id']."'");
}
//print_rr($cyd);
$date = date('Y-m-d');

if($cyd['status'] <1 && ($u['userid'] == $cyd['create_user'])){
    $cyd['cy_rwxd_user']	= "<input class='btn btn-primary btn-xs' class='noprint' type='button' value=\"签字\" onclick=\"location='cy_tzd_qz.php?cyd_id={$cyd[id]}&d[cy_rwxd_user]={$u[userid]}&d[cy_rwxd_user_qz_date]={$date}'\">";
}
if(!empty($cyd['cy_rwjs_user'])&&!empty($cyd['cy_rwjs_user2'])){
	$cyd['cy_users_qz']=$cyd['cy_rwjs_user'].'、'.$cyd['cy_rwjs_user2'];
}else{
	if(!empty($cyd['cy_rwjs_user'])){
		$cyd['cy_users_qz']=$cyd['cy_rwjs_user'];
	}else{
		$cyd['cy_users_qz']=$cyd['cy_rwjs_user2'];
	}
}
if($cyd['status']>=1 && ($cyd['cy_user']== $u['userid']||$cyd['cy_user2'] == $u['userid'])&&$u['userid']!=$cyd['cy_rwjs_user']&&$u['userid']!=$cyd['cy_rwjs_user2']){
	if(!empty($cyd['cy_users_qz'])){
		$cyd['cy_users_qz'] =$cyd['cy_users_qz']."、<input class='btn btn-primary btn-xs' class='noprint' type='button' value=\"签字\" onclick=\"location='cy_tzd_qz.php?cyd_id={$cyd[id]}&d[cy_rwjs_qz_date]={$date}'\">";
	}else{
		$cyd['cy_users_qz'] ="<input class='btn btn-primary btn-xs' class='noprint' type='button' value=\"签字\" onclick=\"location='cy_tzd_qz.php?cyd_id={$cyd[id]}&d[cy_rwjs_qz_date]={$date}'\">";
	}
}
//判断是否有采样人员确认签字，如果没有这都设置为空
if($cyd['cy_rwxd_user_qz_date'] == '0000-00-00'){
	$cyd['cy_rwxd_user_qz_date'] = '';
}
if($cyd['cy_rwjs_qz_date'] == '0000-00-00') {
    $cyd['cy_rwjs_qz_date'] = '';
}
get_int($_GET['cyd_id']);
//查询采样时间、任务类型、备注
$rs_cy= $DB->fetch_one_assoc("SELECT id,`site_type`,`cy_date`,`note` FROM `cy` WHERE id ='".$_GET['cyd_id']."'");

$note = $rs_cy['note'];
$rwxz=$global['site_type'][$rs_cy['site_type']];

//查询一个批次下的所有项目
$value_arr=array();
$sql="SELECT * FROM `cy_rec` WHERE `cyd_id` ='".$_GET['cyd_id']."' ORDER BY id";
$res=$DB->query($sql);
while($rs=$DB->fetch_assoc($res))
{
	$assay_values=explode(',',$rs['assay_values']);
	$value_arr=array_merge($assay_values,$value_arr);
}
//采样接收人签字后通知单的容器信息获取cy表的rq_info

if($cyd['cy_rwjs_qz_date']!=''&!empty($cyd['rq_info'])){
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
if(!empty($rq_data))
{
	$rq_td='';
	$bcj_td='';
	$bcj_note='';
	$rq_size_td='';
	$zlie = (int)count($rq_data)+1;
	foreach($rq_data as $k=>$v)
	{	
		if($_GET['print']){
			$rq_name_td.="<td>".$v['rq_name']."</td>";
		}else{
			$rq_name_td.="<td  onclick=\"gourl()\">".$v['rq_name']."</td>";
		}
		$rq_size_td.="<td>".$v['rq_size']."</td>";
		//$bcj_td.="<td>".$v['bcj']."</td>";
		if(empty($v['bcj'])){
			$v['bcj']='无';
		}
		$bcj_note.='<b>'.$v['rq_name'].'：</b>'.$v['bcj'].'。';
	}
}
else
{
	echo "<script>if(confirm('项目没有设置保存的容器,点击确定去设置！')){location.href='$rooturl/system_settings/cy_rq_manage/rq_list.php';}else{location.href='$rooturl/cy/cyrw_list.php';}</script>";
}
//查询出一个批次下的所有站点信息  
$sql="SELECT cr.*,s.site_address FROM `cy_rec` cr LEFT JOIN `sites` s  ON cr.sid=s.id WHERE cyd_id ='".$_GET['cyd_id']."' ORDER BY cr.id ASC";
$res=$DB->query($sql);
$i=1;
$xu=1;
$cy_tzd_line='';

//如果是打印页面显示设置
if($_GET['print']){
	if(!empty($_GET['page_size'])){
		$page_size=$_GET['page_size'];
	}else{
		$page_size='12';//默认打印12行
	}
	$input_note="此处设置打印行数，默认12行";
	echo temp("cy_tzd_print_head");
}
$note_textarea=$note;
$nums    = $DB->num_rows($res);//所有样品的数量
while( $row = $DB->fetch_assoc( $res ) )
{
	if($_GET['print']){
		$xh=$xu;
	}else{
		$xh=$i;	
	}
	$z_nums=0;
	$site_name=$row['site_name'];
	if($row['zk_flag']=='-6'){
		$site_name=$row['site_name'].'<br>(平行)';
	}
	$cy_tzd_line.="<tr align='center' class='change_bg'><td>".$xh."</td><td>".$site_name."<b>/</b>".$row['site_address']."</td>";
	 //遍历查询结果，得到值后，将字符串转换为数组
	$rowarr=explode(',',$row[assay_values]);
	  //获取json转换为数组
	$json_arr = json_decode($row[json],true);
	//获取数组交集,不同材质的瓶子是否包含站点要化验的项目
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
		if($_GET['print']){
			$cy_tzd_line.="<td>".$rq_num."</td>";
		}else{
			//'$rooturl/cy/cy_tzd.php?cyd_id=$_GET[cyd_id]&id=$row[id]&rq_id=$k&ps=','{$row[site_name]}{$v[rq_name]}使用的数量为'
			$cy_tzd_line.="<td onclick=modi_ps('{$row[id]}','{$k}','{$row[site_name]}','{$v[rq_name]}')>".$rq_num."</td>";
		}	
	}
	$cy_tzd_line.="<td>".$z_nums."</td></tr>";
	$rq_td_nums=count($rq_data);//容器信息占据的td个数
	if($_GET['print']&&($xu==$page_size||$i==$nums)){
		if($i==$nums){
			if($page_size-$xu){
				$td_nums=$rq_td_nums+2;//空白行应该增加的td个数
				$add_tr_nums=$page_size-$xu;
				for($k=0;$k<$add_tr_nums;$k++){
					$k_td_xh=$xu+$k+1;//空白行的序号
					$cy_tzd_line.="<tr  align='center' class='change_bg'><td>".$k_td_xh."</td>";
					for($j=0;$j<$td_nums;$j++){
						$cy_tzd_line.="<td></td>";
					}
					$cy_tzd_line.="</tr>";
				}
			}
			echo temp("cy_tzd");
		}else{
			echo temp("cy_tzd");
		}
				$cy_tzd_line='';
				$xu=0;
	}
	$i++;
	$xu++;
}
if(empty($_GET['print'])){
	$note_textarea="<textarea name='note' id='note' style='width:100%;text-align:left' onblur=\"modi_note(this,'{$_GET[cyd_id]}')\">".$note."</textarea>";
 disp("cy_tzd");
}
?>
