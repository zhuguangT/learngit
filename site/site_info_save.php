<?php
require_once "../temp/config.php";
require_once __SITE_ROOT . "inc/site_func.php";
require_once  "../inc/cy_func.php";
//if(count($_POST['json'])>0)$json = JSON($_POST['json']);
$sid	= 0;
$fzx_id	= FZX_ID;//中心
if ( (int)$_POST['site_id'] )
    $sid = (int)$_POST['site_id'];
if ( $_POST['site_name'] )
    $site_name = mysql_real_escape_string( $_POST['site_name'] );//站名
if ( $_POST['water_type'] )
    $water_type = mysql_real_escape_string( $_POST['water_type'] );//水样类型
if ( $_POST['fenz'] )
   $fenz = mysql_real_escape_string( $_POST['fenz'] );//分中心
if ( $_POST['river_name'] )
    $river_name = mysql_real_escape_string( $_POST['river_name'] );//河（库）名
if ( $_POST['site_address'] )
    $site_address = mysql_real_escape_string( $_POST['site_address'] );//站址
if ( $_POST['area'] )
    $area = mysql_real_escape_string( $_POST['area'] );//流域
if ( $_POST['xz_area'] )
    $xz_area = mysql_real_escape_string( $_POST['xz_area'] );//行政区
if ( $_POST['banjing'] )
    $banjing = mysql_real_escape_string( $_POST['banjing'] );//采样范围
if ( $_POST['site_code'] )
    $site_code = mysql_real_escape_string( $_POST['site_code'] );//站码
if ( $_POST['site_vertical'] )
    $site_vertical = mysql_real_escape_string( $_POST['site_vertical'] );//层面编号
if ( $_POST['site_line'] )
    $site_line = mysql_real_escape_string( $_POST['site_line'] );//垂线编号
if ( $_POST['jingdu'] )
    $jingdu = dfmto( $_POST['jingdu'] );//经度
if ( $_POST['weidu'] )
    $weidu = dfmto( $_POST['weidu'] );//纬度
if ( $_POST['note'] )
    $note = mysql_real_escape_string( $_POST['note'] );//备注
if ( $_POST['sgid'] )
	$sgid = mysql_real_escape_string( $_POST['sgid'] );//site_group中的id
if( !$sid )
    die( '非法操作' );
if ( $_POST['syxz'] ){
	$syxz = implode(',',$_POST['syxz']);
}
$site_info = array(
	'site_type'		=> $_POST['sites_type'],//任务类型
	'site_name'		=> $site_name,//站名
    'water_type'	=> $water_type,//水样类型
	'fp_id'			=> $fenz,//分中心
    'river_name'	=> $river_name,//河（库）名
    'site_address'	=> $site_address,//站址
	'area'			=> $area,//流域
	'xz_area'		=> $xz_area,//行政区
	'water_system'	=> $water_system,//水系
	'banjing'		=> $banjing,//采样范围
	'site_code'		=> $site_code,//站码
	'site_vertical' => $site_vertical,//层面编号
	'site_line'		=> $site_line,//垂线编号
	'sgnq'			=> $sgnq,//水功能区
	'sgnq_code'		=> $sgnq_code,//水功能区编号
    'jingdu'		=> $jingdu,//经度
    'weidu'			=> $weidu,//纬度
    'note'			=> $note,//备注
    'syxz'			=> $syxz,//水源限制
); 
######################更新站点表头信息
if ( update_site_info( $sid, $site_info ) ){
   $info = "成功更新站点信息\n" ;
}
######################更新rec和order表的站点名称，只有未生成报告的才进行修改。
$cydsql = $DB->query("SELECT cr.cyd_id FROM `cy_rec` AS cr LEFT JOIN `report` AS re ON cr.id=re.cy_rec_id where cr.sid='{$sid}' AND (re.print_status!='1' OR re.id is null)");
while($cyd = $DB->fetch_assoc($cydsql)){
	$recup = $DB->query("update cy_rec set site_name='".$site_name."',river_name='".$river_name."' where cyd_id='".$cyd['cyd_id']."' and sid='".$sid."'");
	$ordup = $DB->query("update assay_order set site_name='".$site_name."',river_name='".$river_name."' where cyd_id='".$cyd['cyd_id']."' and sid='".$sid."'");
}

if($_POST['site_type']=='0'){
######################更新站点的统计参数
	$site_tjcs = array();
	if ( $_POST['vid'] ){  
			$vids = join( ',', $_POST['vid'] );
		}
	 $old_tjcs=$_POST['old_tjcs'];

	if (isset($_POST['tjcs_name']) ) {
		$site_tjcs = $_POST['tjcs_name'];
		$new_tjcs=implode(',',$site_tjcs);
	}else{
		$site_tjcs ='kong';
		$new_tjcs  ='';
		}
	//echo $old_tjcs.'he'.$new_tjcs;die();
	 #############################更新站点该统计参数下的项目
	$DB->query("UPDATE site_group SET assay_values='$vids' WHERE site_id = $sid AND id = '$sgid' AND site_type='0' AND fzx_id='".$fzx_id."'");
	if($old_tjcs!=$new_tjcs){//统计参数是否更改了
		if( update_site_tjcs( $sid,$site_tjcs,$_POST['site_type'],$old_tjcs,$vids )){ 
			$info .= "成功更新站点的统计参数\n" ;
		}
	}
	
}else{
######################更新站点批次信息
	$site_group = array();
	if( isset($_POST['group_name'] ) ){//得到已选批次
		$site_group = $_POST['group_name'];
	}else{
		$site_group ='kong';
	}

	if( $_POST['custom_group_name'] ){;
		$site_group[] = trim( $_POST['custom_group_name'] );
	}

	if ( isset($_POST['current_group_name']) ) {//得到该批次
		$s_group=$_POST['current_group_name'];
		if ( $_POST['vid'] ){  
			$vids = join( ',', $_POST['vid'] );
		}
		if ( update_site_group( $sid, $site_group,$_POST['site_type'] ,$fzx_id,$vids)){ 
			$info .= "成功更新站点所属的批\n" ;
		}
		if($_POST['old_vid']!=$vids){//判断该批次下的项目有没有改
			if ( update_site_group_assay_value2( $sid,$_POST['sgid'],$fzx_id,$_POST['site_type'],$s_group, $vids,$xids ) ){
				$info .= "成功更新站点的化验项目.";
			}
		}
	}
	//修改sites表中的统计参数
	if (isset($_POST['tjcs_name']) ) {
		$site_tjcs = $_POST['tjcs_name'];
		$new_tjcs=implode(',',$site_tjcs);
	}else{
		$new_tjcs  ='';
		}
	$site_tj=','.$new_tjcs.',';
    $DB->query( "UPDATE sites SET tjcs = '$site_tj' WHERE id = $sid");
}
if($_POST['sites_type']=='0'&&$_POST['actions']=='xdjdrw'){$url="$rooturl/site/site_list_new.php?fzx_id=$_POST[fenz]";}else{
//if($_POST['fzxxg']=='yes'){}
$url="$rooturl/xd_cyrw/xd_cyrw_index.php?site_type=$_POST[site_type]";
//$url="$rooturl/site/site_info.php?action=xdrw&site_id=$sid&site_type=$_POST[site_type]&group_name=$s_group";
//echoEx( nl2br( $info ), 1, $_SESSION['back_url'] );  
}
echoEx( nl2br( $info ), 1, $url);
?>
