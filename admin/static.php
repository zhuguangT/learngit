<?php
include '../temp/config.php';
if($_POST['handle'] == 'up_juli'){
	$jingdu = $_POST['jingdu'];
	$weidu = $_POST['weidu'];
	$y_jingdu = $_POST['y_jingdu'];
	$y_weidu = $_POST['y_weidu'];
	echo round(getdistance2($y_jingdu,$y_weidu,$jingdu,$weidu)*10000,0);
die;
}
if($u[userid] == '') nologin();
$trade_global['daohang'][]  = array('icon'=>'','html'=>'采样到位查看','href'=>"$rooturl/admin/static.php?site_type={$_GET['site_type']}&year={$_GET['year']}&month={$_GET['month']}&cyd_bh={$_GET['cyd_bh']}&dw_qk={$_GET['dw_qk']}");
$_SESSION['daohang']['barstatus'] = $trade_global['daohang'];
if(!$_GET['site_type'] )$_GET['site_type']    = "全部" ;
if($_GET["site_type"] != "全部")$site_type_num = $site_flag[$_GET['site_type']];
if(!$_GET['year'])$_GET['year']   = date('Y');
if(!$_GET['month'])$_GET['month'] = date('m');
if(!$_GET['cyd_bh'] )$_GET['cyd_bh']    = "全部" ;
if(!$_GET['dw_qk'] )$_GET['dw_qk']    = "全部" ;


//先按照年和月份查询，不加状态和其他的判断 放弃坐标不能为空  and   jingdu!=''  and  weidu!=''
if($u['fzx_id']=='1'){
	$fzx_sql = "";
}else{
	$fzx_sql = " AND cy.`fzx_id` = '{$u['fzx_id']}' ";
}
$sql    = "SELECT cy.id,cy_rec.bar_code,cy_rec.juli ,cy_rec.sid,cy_rec.jingdu,cy_rec.weidu , cy.cyd_bh,cy.cy_user,cy.group_name , cy_rec.cy_date , cy_rec.create_date FROM cy,cy_rec WHERE year(cy.`cy_date`) = '$_GET[year]' AND month(cy.`cy_date`) = '$_GET[month]'  AND cy.id = cy_rec.cyd_id   and  zk_flag>=0  and  zk_flag!=1 and jingdu!='' and weidu!=''  $fzx_sql ";// 
if(isset($site_type_num))$sql    .= " AND cy.site_type = '$site_type_num' ";
if($_GET['cyd_bh']!="全部")$sql   .=  " and cy.cyd_bh='$_GET[cyd_bh]' ";
if($_GET['dw_qk'] != "全部"  &&  $_GET['dw_qk'] == "未到位")  $sql .="and  cy_rec.juli > 0";
if($_GET['dw_qk'] != "全部"  &&  $_GET['dw_qk'] == "到位")  $sql .="and  cy_rec.juli <= 0";
$sql   .= "  order by cy_rec.cy_date DESC , cy.group_name , cy_rec.bar_code"; 
$res    = $DB->query( $sql );
$cyd_bh_data  = array($_GET['cyd_bh'],"全部");
$i=1;
$group_name_data=array();
if($u['fzx_id'] == '1'){
	$fzx_zd = 'fzx_id';
}else{
	$fzx_zd = 'fp_id';
}
while( $row = $DB->fetch_assoc( $res ) ) {
	// if($u['userid'] == '管理员'){
	// 	print_rr($row);
	// }
  if( $row['cyd_bh'] != $_GET['cyd_bh'] ){
	$cyd_bh_data[] = $row['cyd_bh'];
	$juli=$row['juli'];
  }
  $sql2="SELECT site_name ,banjing,jingdu,weidu  FROM `sites`   WHERE `id` = $row[sid] ";//AND $fzx_zd = '{$u['fzx_id']}' 
  $res2 = $DB->query($sql2);
  while($row2 = $DB->fetch_assoc( $res2 )){
	$site_name= $row2['site_name'];
	if($row2['banjing'] != ''){
	$banjing= $row2['banjing'];//采样 范围规定的距离
	}else{
	$banjing= '200';
	}
	$jd= $row2['jingdu'];//原来计划的精度
	$wd= $row2['weidu'];//原来计划的纬度
	if(empty($row['jingdu']) && empty($row['weidu'])){
	  	$juli = "定位失败";
	  	$map = "<span style='color:red;'>定位失败,无法显示具体位置</span>";
	  }else{
	  	$juli = '';
	  	if(empty($juli)){
			if(empty($jd) || empty($wd)){
				$juli	= '<font color="#939090">站点坐标不完整</font>';
			}else if(empty($row2['jingdu']) || empty($row2['weidu'])){
				$juli	= '<font color="#939090">采样坐标不完整</font>';
			}else{
				$juli	=  getdistance2("$jd", "$wd", "$row[jingdu]","$row[weidu]") ;
				$juli=round($juli*10000,0);
			}
		}
	  	$map = "<a  class='boxy'  href='$rooturl/admin/site_gmap2.php?yjd=$jd&ywd=$wd&jd=$row[jingdu]&wd=$row[weidu]'>查看地图</a>";
	  }
  }
  if($juli >= $banjing){
  	$juli_color = "style='color:red;'";
  }else{
  	$juli_color = "";
  }
  $i += 0;
  if($i<=9)
  $i='0'.$i;
  if (!in_array($row[group_name],$group_name_data)){
	//采样到位监督查看采样单的时候，只跳转一张上面去先去掉，想出办法在修改
	// <td  > <a href='../caiyang/cy_rec_zls.php?cyd_id=$row[id]'>查看采样单</a></td>
	$lines.=" 
	  <TR align='center'   ><td >批次</td><td >$row[group_name]</td> <td colspan=3></td>
	  <td></td>
	  </TR>
	  <TR align='center'>
	  <TD> 序号</TD>
	  <TD>样品编号</TD>
	  <TD> 站点名称</TD>
	  <TD>下达采样时间</TD>
	  <TD>采样时间</TD>
	  <TD>采样范围(米)</TD>
	  <TD> 相差距离(米) </TD>
	  <TD   >地图对比查看</TD>
	  </TR>
	  <TR>";
  }
	$lines.=temp('static_line');
	$group_name_data[]=$row['group_name'];
  $i++;
}
 


$temparr=array();
foreach($cyd_bh_data as $k=>$v){
	 $temparr[$v] = true;//去掉重复采样单号 s
}
$cyd_bh_data=array_keys($temparr);
$cyd_bh_list=disp_options($cyd_bh_data);

if($sttype2=='')//**config.php 文件中配置
$site_type_data = array_unique( array( $_GET['site_type'], "全部", "站网", "临时", "委托" ) );
else{
array_unshift($sttype2,$_GET['site_type']); //这里的意思是把新加的放到前面为了显示
$site_type_data = array_unique($sttype2);
}
$site_type_list = disp_options( $site_type_data );//任务性质


$month_max      = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
$month_data     = array($_GET['month']);
for( $i = $month_max; $i >= 1; $i-- ) {
	$month_text = ( $i < 10 ) ? "0{$i}" : $i;
	if( $month_text != $_GET['month'] ) $month_data[] = $month_text;
}
$month_list = disp_options($month_data);//月份

$year_data  = array($_GET['year']);
for( $i = date('Y'); $i >= 2005; $i-- )
	if( $i != $_GET['year'] )$year_data[] = $i;
$year_list  = disp_options($year_data);//年份


$dw_qk_data = array_unique( array( $_GET['dw_qk'], "全部", "到位", "未到位" ) );
$dw_qk_list = disp_options( $dw_qk_data );//到位情况

//$juli=  getdistance2("119.42047", "32.79808", "119.36806005106521", "33.24176606722058") ;
//echo round($juli*10000,0);
function trans_jwd($jwd){
	if(preg_match("/[\x{4e00}-\x{9fa5}]+/u",$jwd)){
		$jd_xiugaihou = preg_replace("/[\x{4e00}-\x{9fa5}]+/u",'-',$jwd);
		$jd_arr = explode('-',$jd_xiugaihou);
		$jwd = $jd_arr[0] + $jd_arr[1]/60 + $jd_arr[2]/60/60;
		return $jwd;
	}elseif(preg_match("/[º|′]/", $jwd)){
		$jd_xiugaihou = preg_replace("/[º|′|″]/",'-',$jwd);
		$jd_arr = explode('-',$jd_xiugaihou);
		$jwd = $jd_arr[0] + $jd_arr[2]/60 + $jd_arr[5]/60/60;
		return $jwd;
	}else{
		return $jwd;
	}
}
//公里
 function getdistance2($lng1,$lat1,$lng2,$lat2){//根据经纬度计算距离
// //将角度转为狐度 
	 $lat1 = trans_jwd($lat1);$lat2 = trans_jwd($lat2);$lng1 = trans_jwd($lng1);$lng2 = trans_jwd($lng2);
	 $radLat1=@deg2rad($lat1);
	 $radLat2=@deg2rad($lat2);
	 $radLng1=@deg2rad($lng1);
	 $radLng2=@deg2rad($lng2);
	 $a=$radLat1-$radLat2;//两纬度之差,纬度<90
	 $b=$radLng1-$radLng2;//两经度之差纬度<180
	 $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*637.8137;
	 return $s;
 }
function getdistant($lat1,$lng1,$lat2,$lng2){
	   $lat1 = trans_jwd($lat1);$lat2 = trans_jwd($lat2);$lng1 = trans_jwd($lng1);$lng2 = trans_jwd($lng2);
	  $radLat1 = deg2rad($lat1);
	  $radLat2 = deg2rad($lat2);
	  $a = $radLat1 - $radLat2;
	  $b = deg2rad($lng1) - deg2rad($lng2);
	  $s = 2 * Asin(Sqrt(Pow(Sin($a/2),2) + Cos($radLat1)*Cos($radLat2)*Pow(Sin($b/2),2)));
	  $s = $s * 6378.137;
	  $s =Round($s * 10000) / 10000;
	  return $s;
}
disp("static.html");
?>
