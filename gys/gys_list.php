<?php
include "../temp/config.php";
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'合格供应商名录','href'=>"$rooturl/gys/gys_list.php");
$_SESSION['daohang']['gys_list']	= $trade_global['daohang'];
if ($_GET['dayin']=='excel') {
    header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=合格供应商名录.xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");  
}
//$fzx_id	= $u['fzx_id'];
/*if(empty($_GET['gys'])){
	$_GET['gys']	= '全部';
}*/
//供应商下拉框
$gys=$_GET['gys'];
if($gys!='全部'&&$gys){
	$where.="and `sname`='$gys'";
}
//供应商默认选中
$query=$DB->query("select `sname` from `gys_gl`");
$gys_arr=array();
while($gys_arr=$DB->fetch_assoc($query)){
	foreach($gys_arr as $k=>$v){
	if($gys==$v){
		$gys_list.="<option value='$v' selected>".$v."</option>";
	}else{
		$gys_list.="<option value='$v'>".$v."</option>";
	}
}
}
//供应、服务领域下拉框
$gy_fwly_v=$_GET['gy_fwly'];
if($gy_fwly_v&&$gy_fwly_v!='全部'){
	$where.="and `gy_fwly`='$gy_fwly_v'";
}
$gy_fwly_arr=array('设备采购类供应商','低值易耗品供应商','标准品供应商','危废处理提供商','服务类供应商','其它');
foreach($gy_fwly_arr as $k => $v){
	if($gy_fwly_v==$v){
		$gy_fwly.="<option value='$v' selected>".$v."</option>";
	}else{
		$gy_fwly.="<option value='$v'>".$v."</option>";
	}
}
/*if(empty($_GET['gys_wuzhi'])){
	$_GET['gys_wuzhi']	= '全部';
}
$ye		= $_GET['ye'];
$nowyear= date("Y");*/
//默认供应商显示所有  不显示当年
/*if(!empty($_GET['riqiy'])){
	$riqiy = $_GET['riqiy'];
	$riqiy_sql = " AND g.`scdate` LIKE '%$riqiy%' ";
}else{
	$riqiy_sql = '';
}
if($_GET['riqiy']=='全部'){
	$riqiy_sql = '';
}
$listy	= date('Y');
if(empty($begin_year)){
	$begin_year	= '2015';//如果没有配置时间，这里默认一个时间
}
$ytime = "<option value='全部'>全部</option>";
for($k=$begin_year;$k<=$listy;$k++){
	if($k == $riqiy){
		$ytime	.=" <option value=$k selected>$k</option>";
	}else{
		$ytime	.=" <option value=$k>$k</option>";
	}
}
$where	= "";
if(!empty($_GET['gys']) && $_GET['gys']!='全部'){
	// $where	.= " AND n.`module_value1`='{$_GET['gys']}'  ";
}
if(!empty($_GET['gys_wuzhi']) && $_GET['gys_wuzhi']!='全部'){
	$where	.= " AND g.`id`='{$_GET['gys_wuzhi']}' ";
}*/
//测试去除与 n_set 表的关联
// $sql = "SELECT n.*,g.`pjdate`,g.*,n.id as nid,g.id as gid,g.wpname as wp , g.sname as gn ,g.lxr as gl, g.lxdh as gld  FROM `gys_gl` g
// 			LEFT JOIN `n_set` n ON n.id=g.gid 
// 			WHERE g.`parent_id` = '0' AND n.`fzx_id`='$fzx_id' AND n.`module_name`='gys' $where $riqiy_sql";
$sql = "SELECT * FROM `gys_gl` as g where 1 $where";
$R	= $DB->query($sql);
$i	= 1;
$dqdate='';
$gys_id=array();
//$gys_wp=array();
//$gys_wupin_list='';
//$nextmonth = date('Y-m-d' , strtotime('next month'));
while($r=$DB->fetch_assoc($R)){
	// print_rr($r);die;
	$gys_id[]=$r['id'];
	/*$today=date('Y-m-d');
	if($r['dqdate']<$today && !empty($r['dqdate']) || $r["dqdate"] < $r['yyzz']){
		$r['dqdate']='<span style="color:red;">'.$r["dqdate"].'<br>营业执照已到期</span>';
	}else if(empty($r['dqdate'])){
		$r['dqdate']='';
	}else if($nextmonth >= $r['dqdate'] && !empty($r['dqdate']) || $r["dqdate"] < $r['yyzz']){
		$r['dqdate']='<span style="color:red;">'.$r['dqdate'].'到期</span>';
	}else{
		$r['dqdate']='<span>'.$r['dqdate'].'到期</span>';
	}*/
    $operation='';
	//打印时供应商没有a标签
	if ($_GET['dayin']=='excel') {
		$gys=$r[sname];
	}else{
		$gys="<a href='ghs_pingjia.php?id=$r[id]'>$r[sname]</a>";
	}
    if ($_GET['dayin']!='excel') {
       $operation   .= "<td><a href='$rooturl/gys/pingjia.php?parent_id={$r['id']}&gid={$r['gid']}&y={$_GET['riqiy']}&year={$r['scdate']}&scdate={$r['scdate']}'>修改</a> | ";
    $operation  .= "<a href=\"javascript:if(confirm('你真的要删除$r[sname]么?\\n一经删除,无法恢复!')) location='delete.php?action=删除&id=$r[id]&sname=$r[sname]&pjdate=$r[pjdate]'\">删除</a></td>";
    }
	$lines		.= temp('gys_list_line');
	$i++;
}



// //供应商列表 及 供应物品列表
// $gys_arr	= $gys_wupin_arr	= array();

// $list_sql	= $DB->query("SELECT n.`module_value1`,g.`wpname`,g.`id` FROM `gys_gl` g
// 			LEFT JOIN `n_set` n ON n.id=g.gid 
// 			WHERE g.`parent_id` = '0' AND n.`fzx_id`='$fzx_id' AND n.`module_name`='gys' AND g.`scdate` LIKE '%$riqiy%'");
// while($list_rs = $DB->fetch_assoc($list_sql)){
// 	// print_rr($list_rs);
// 	if(!in_array($list_rs['module_value1'],$gys_arr)){
// 		$gys_arr[]		= $list_rs['module_value1'];
// 	}
// 	if(!in_array($list_rs['wpname'],$gys_wupin_arr)){
// 		$gys_wupin_arr[]	= $list_rs['wpname'];
// 	}
// 	if($_GET['gysp_name']==''){
// 		$gys_wupin_list.="<option name='id' value='$list_rs[id]'>$list_rs[wpname]</option>";
// 	}else{
// 		if($_GET['gys_wuzhi']==$list_rs['id']){
// 			$gys_wupin_list.="<option name='id' value='$list_rs[id]' selected>$list_rs[wpname]</option>";
// 		}else{
// 			$gys_wupin_list.="<option name='id' value='$list_rs[id]' >$list_rs[wpname]</option>";
// 		}
// 	}
// }
// $gys_list		= disp_options($gys_arr,0,$_GET['gys']);
      if ($_GET['dayin']=='excel') {
        echo temp("gys_dayin.html");
      }else{
        disp("gysgl");
      }





// $sql = "SELECT * FROM `gys_gl`";
// $re = $DB->query($sql);
// while($data = $DB->fetch_assoc($re)){
// 	$sql = "INSERT INTO `gys_gl` (`parent_id` , `sname` , `wpname` , `scdate` , `dx` ,`lxr` , `lxdh` ,`pjr` ,`pjdate` , `cpzl` , `fuwu` , `xinyu` , `jiage` , `fujian` , `gid` , `zzjgdm` , `dqdate` , `swdjz` , `yyzz` , `beizhu`) VALUES('$data[id]' , '$data[sname]' , '$data[wpname]' , '$data[scdate]' , '$data[dx]','$data[lxr]','$data[lxdh]','$data[pjr]','$data[pjdate]','$data[cpzl]','$data[fuwu]','$data[xinyu]','$data[jiage]','$data[fujian]','$data[gid]','$data[zzjgdm]','$data[dqdate]','$data[swdjz]','$data[yyzz]','$data[beizhu]')";
// }
// die;

?>

