<?php
include "../temp/config.php";
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'客户管理','href'=>"$rooturl/kehu/kh_list.php");
$_SESSION['daohang']['kh_list']	= $trade_global['daohang'];
$year = date("Y");
//判断下载
	if($_GET['xiazai']=='xiazai'){
		header("Content-Type:application/msexcel");        
		header("Content-Disposition:attachment;filename=委托客户列表.xls");        
		header("Pragma:no-cache");        
		header("Expires:0"); 	
	}
//删除处理
if($_GET['action']=='删除'){
	if($_GET['kid']&&$_GET['name']){
		$DB->query("update kehu set act ='0' where id='".$_GET['kid']."'");
	}
}
if($_GET['action']=='wtsc'){
	if($_GET['wtid']){
		$DB->query("update kehu_wt set act ='0' where id='".$_GET['wtid']."'");
	}else{
		echo "<script>alert('参数丢失，请重试！');</script>";
	}
}
if(empty($_GET['group'])){
	$_GET['group']	= '全部';
}
if(empty($_GET['kehu'])){
	$_GET['kehu']	= '全部';
}
//时间和筛选条件
if(empty($_GET['khsx'])){
	$_GET['khsx']	= '全部';
}

$listy	= date('Y');
if(empty($_GET['year'])){
	$_GET['year']	= $listy;
}

if(empty($begin_year)){
	$begin_year	= '2015';//如果没有配置时间，这里默认一个时间
}
for($k=$begin_year;$k<=$listy;$k++){
	if($k == $_GET['year']){
		$ytime	.=" <option value=$k selected>$k</option>";
	}else{
		$ytime	.=" <option value=$k>$k</option>";
	}
}
//筛选条件
$sql_where = '';
if($_GET['kehu']	!= '全部'){
	$sql_where .= " and name = '".$_GET['kehu']."' ";
}
if($_GET['khsx']	!= '全部'){
	$sql_where .= " and id = '".$_GET['khsx']."' ";
}
//查询所有委托站点
$sitesql = $DB->query("select id,site_name,water_type,jcbz from sites where site_type='3'");
while($s = $DB->fetch_assoc($sitesql)){
	$sarr[$s['id']]['site_name'] = $s['site_name'];
	$sarr[$s['id']]['water_type'] = $s['water_type'];
	$sarr[$s['id']]['jcbz'] = $s['jcbz'];
}

//查询
$chaxun = $DB->query("select * from kehu where act = '1' $sql_where ");
$i = '0';
$kh_list = "";
while($r = $DB->fetch_assoc($chaxun)){
	++$i;
	$operation	 = "<a href='$rooturl/kehu/newwt.php?kid=".$r['id']."'>新增委托</a> | ";
	$operation	.= "<a href='$rooturl/kehu/newkh.php?kid=".$r['id']."&&action=xiugai'>修改客户信息</a> | ";
	$operation	.= "<a href=\"javascript:if(confirm('你真的要删除$r[sname]么?\\n一经删除,无法恢复!')) location='$rooturl/kehu/newkh.php?action=删除&kid=$r[id]'\">删除</a>";
	
	$sql_where1 = '';
	if($_GET['year']	!= ''){
		$sql_where1 .= " and wt_date like '".$_GET['year']."%' ";
	}
	$rsql = $DB->query("select * from kehu_wt where kid = '".$r['id']."' and act='1' ".$sql_where1);
	$j = '0';
	$rsstr  = $rsstr1 ='';
	
	while($rs = $DB->fetch_assoc($rsql)){
		++$j;
		//站点处理
		$sitestr = '';
		if($rs['sites']){
			$sites = explode(',',$rs['sites']);
			$sshu = count($sites);
			foreach($sites as $vv){
				if($sitestr == ''){
					$sitestr = $sarr[$vv]['site_name'];
				}else{
					$sitestr .= "，".$sarr[$vv]['site_name'];
				}
			}
			$sitestr .= "&nbsp;共计<font color='red'>".$sshu."</font>个样品";
		}
		if($j == '1'){
			if($_GET['xiazai']=='xiazai'){
				$rsstr .= "<td height='88px'>$j</td><td>$rs[wt_date]</td><td height='88px'>$sitestr</td><td>$rs[jcsx]工作日</td><td>$rs[fenlei]</td><td>$rs[money]</td>";
			}else{
				$rsstr .= "<td height='88px'>$j</td><td>$rs[wt_date]</td><td height='88px'><a href='$rooturl/kehu/wtyptjb.php?year=$_GET[year]&wtdw=$r[name]&sites=$rs[sites]'>$sitestr</a></td><td>$rs[jcsx]工作日</td><td>$rs[fenlei]</td><td>$rs[money]</td><td><a href='$rooturl/kehu/newwt.php?kid=".$r['id']."&&wtid=".$rs['id']."' >修改</a>|<a href='javascript:void(0)'  onclick='wtsc(".$rs['id'].")'>删除</a>|<a href='$rooturl/kehu/newwt.php?kid=".$r['id']."&&wtid=".$rs['id']."&&fuzhi=fuzhi'>复制本条</a></td>";
			}
			
		}else{
			if($_GET['xiazai']=='xiazai'){
				$rsstr1 .= "<tr height='88px'><td>$j</td><td>$rs[wt_date]</td><td>$sitestr</td><td>$rs[jcsx]工作日</td><td>$rs[fenlei]</td><td>$rs[money]</td></tr>";
			}else{
				$rsstr1 .= "<tr height='88px'><td>$j</td><td>$rs[wt_date]</td><td><a href='$rooturl/kehu/wtyptjb.php?year=$_GET[year]&wtdw=$r[name]&sites=$rs[sites]'>$sitestr</a></td><td>$rs[jcsx]工作日</td><td>$rs[fenlei]</td><td>$rs[money]</td><td><a href='$rooturl/kehu/newwt.php' >修改</a>|<a href='javascript:void(0)'  onclick='wtsc(".$rs['id'].")'>删除</a>|<a href='$rooturl/kehu/newwt.php?kid=".$r['id']."&&wtid=".$rs['id']."&&fuzhi=fuzhi'>复制本条</a></td></tr>";
			}
		}
		
	}
	if(($rsstr1=='')&&($rsstr=='')){
		$rsstr .= "<td height='88px'></td><td height='88px'></td><td height='88px'></td><td height='88px'></td><td height='88px'></td><td></td><td></td>";
		$j = '1';
	}
	if($_GET['xiazai']=='xiazai'){
		$pd_xiazai = "";
	}else{
		$pd_xiazai = "<td rowspan='$j'>$operation</td>";
	}
	$lines		.= temp('kehu/kh_list_line');
}
//筛选
$chaxun1 = $DB->query("select * from kehu where act = '1'");
$kh_list = "";
while($r = $DB->fetch_assoc($chaxun1)){
	if($r['id'] == $_GET['khsx']){
		$kh_list .= "<option value=$r[id] selected>$r[name]</option>";
	}else{
		$kh_list .= "<option value=$r[id]>$r[name]</option>";
	}
}
if(empty($_GET['xiazai'])){
	disp("kehu/kh_list");
}else{
	echo temp("kehu/kh_list_print");
}


?>

