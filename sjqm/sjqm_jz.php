<?php
//2012-8-21 add by lixiaojun
//功能 试剂器皿管理  打印台帐
include "../temp/config.php";
$a = 'asfasdfsdfsadsad';
echo strlen($a);
if(!isset($a{5})){
	echo '长度为5';
}else{
	echo '长度超过5';
}
$fzx_id	= $u['fzx_id'];
$print_head = temp("head");
$print_foot	= temp("bottom");
//if($u['userid'] != 'admin') {echo '维护中。。。';die;}
$biao=intval($_GET['biao']);
$i = $j = 0;	//表格数 行数
$per_line = 24;	//默认一个表格的行数，数据不足以空行补齐
$type	= array('试剂'=>0,'药品'=>3,'器皿'=>1,'杂物'=>2);
$where	= '';
if(!empty($_GET['name'])){
	$where .= " AND `name` LIKE '%".$_GET['name']."%'";
}
if(!empty($_GET['type']) &&  $_GET['type'] != '全部'){
	$where .= " AND sjqm.`type` = '".$_GET['type']."'";
}
$_GET['year'] = empty($_GET['year']) ? date('Y') : $_GET['year'];
if($biao != 1){
	$where .= " AND FROM_UNIXTIME(sjqm_ls.time,'%Y') = ".$_GET['year'];
}
//暂时不对试剂进行排序
$order	=' ORDER BY `name` ';
if($biao==1){
	$sqlsee	= "SELECT * FROM `sjqm` WHERE `fzx_id`='$fzx_id' ".$where . " $order";
	$rsee	= $DB->query($sqlsee);
	while($r = $DB->fetch_assoc($rsee)){
		if($j == $per_line){
			$i++ ;
			$j = 0 ;
		}
		$data[$i][$j++] = $r;
	}
	if(empty($data)) {echo "<script>alert('无数据');location.href='sjqm_list.php'</script>";die;}
	for($j;$j<$per_line;$j++){
		$data[$i][$j] = array();
	}
	include('yilan_biao.php');
}else if($biao==2){
	$per_line = 11;
	//暂时不对试剂进行排序
	$order =  ' ORDER BY `name` ,sjqm_ls.time ASC';
	$sqlsee = "SELECT * FROM `sjqm` JOIN `sjqm_ls` ON sjqm.id =  sjqm_ls.sj_id WHERE sjqm.`fzx_id`='$fzx_id' ".$where.$order.' ';
	$rsee = $DB->query($sqlsee);
	while($r = $DB->fetch_assoc($rsee)){
		$name = $name == '' ? $r['name'] : $name;
		if($j == $per_line){
			$i++ ;
			$j = 0 ;
		}elseif($name != $r['name']){
			for($j;$j<$per_line;$j++)
				$data[$i][$j] = array();
			$i++ ;
			$j = 0 ;
			$name = $r['name'];
		}
		$r['Y'] = date('Y',$r['time']);
		$r['m'] = date('m',$r['time']);
		$r['d'] = date('d',$r['time']);
		$data[$i][$j++] = $r;
	}
	if(empty($data)){echo "<script>alert('无数据');location.href='sjqm_list.php'</script>";die;}
	for($j;$j<$per_line;$j++){
		$data[$i][$j] = array();
	}
	include('taizhang_biao.php');
}else if($biao==3){
	$per_line = 11;
	$sqlsee = "SELECT * FROM `sjqm` LEFT JOIN `sjqm_ls` ON sjqm.id = sjqm_ls.sj_id WHERE sjqm.`fzx_id`='$fzx_id' $where AND sjqm_ls.type = 'c' ORDER BY sjqm_ls.time , `name`";
	$rsee = $DB->query($sqlsee);
	while($r = $DB->fetch_assoc($rsee)){
		if($j == $per_line){
			$i++ ;
			$j = 0 ;
		}
		$r['Y'] = date('Y',$r['time']);
		$r['m'] = date('m',$r['time']);
		$r['d'] = date('d',$r['time']);
		$data[$i][$j++] = $r;
	}
	//if(empty($data)){echo "<script>alert('无数据');location.href='sjqm_list.php'</script>";die;}
	for($j;$j<$per_line;$j++){
		$data[$i][$j] = array();
	}
	include('jilu_biao.php');
}else echo "<script>location.href='sjqm_list.php'</script>";
?>
