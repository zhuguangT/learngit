<?php
/*
 *@扫描枪扫描ajax对编号样品完成率的判断页面
 *@author:hanfeng
 *@date：2012-6-15
 */
session_start();
include "../temp/config.php";
//ajax输入样品编号时，出现提示
if($_POST['handle'] == 'find_bh'){
	$sql = "SELECT `bar_code` FROM `assay_order` WHERE `bar_code` LIKE '{$_POST['value']}%' GROUP BY `bar_code` LIMIT 10";
	$re = $DB->query($sql);
	while($data = $DB->fetch_assoc($re)){
		$str .= $data['bar_code'].",";
	}
	echo substr($str, 0 , -1);
	die;
}
//清空session（用户点击清空表时 清空）
if($_GET['zt']=="qingkong"){
	$_SESSION['bianhao'] = '';
	exit;
}
if(!$_SESSION['bianhao']){//第一次触发时没有session值 先定义
	$_SESSION['bianhao'] = array();
}
if(!empty($_GET['bh'])){
	$jg  = 0;
	$win = $finish = $river = $site_name = $float = $int = $sql = '';
	if(!in_array($_GET['bh'],$_SESSION['bianhao'])){//防止同一个编号刷两次的情况
		//preg_match("/^[a-z,A-Z]*/",$_GET['bh'],$float);//取出编号最前面的 “英文字符”
		//preg_match("/[0-9]*$/",$_GET['bh'],$int);//取出 编号 最后面的 “数字”
		//$float = substr($_GET['bh'],0,1);//因为要适应每个地方的编号规则而改的，编号只能是5位，数据库编号的第一位和后4位
		//$int   = substr($_GET['bh'],-4);
		//if(isset($float) && !empty($int)&&strlen($_GET['bh'])>=5){//判断是不是编号格式
		if(strlen($_GET['bh'])>=9){
			/*if(!empty($_GET['cyd_bh'])){
				$sql = "select ao.site_name,ao.river_name,ao.vd0,ao.assay_over from `assay_order` as ao inner join `cy` on ao.cyd_id = cy.id where cy.cyd_bh='".$_GET['cyd_bh']."' and ao.bar_code like '".$float."%".$int."'";
			}else{
				$sql = "select site_name,river_name,vd0,assay_over from `assay_order` where bar_code like '".$float."%".$int."'";
			}*/
			$sql = "SELECT cy_rec.id AS cid , s.id AS sid , cy.id AS cyd_id , cy_rec.cy_date , cy.cy_dept , ao.tid, s.site_address,cy.`group_name`,ao.site_name,ao.river_name,ao.vd0,ao.assay_over from `sites` as s  RIGHT JOIN `assay_order` as ao ON s.id=ao.sid LEFT JOIN cy ON ao.cyd_id=cy.id LEFT JOIN `cy_rec` ON cy.id = cy_rec.cyd_id where ao.hy_flag >= '0' AND year(ao.`create_date`)='".date('Y')."' and ao.bar_code ='{$_GET['bh']}'";//like '".$float."%".$int."'";
			$query = $DB->query($sql);
			$num   = $DB->num_rows($query);
			if($num==0){
				//未找到该编号
				$src = "roung.ogg";
			}else{
				$fenzi   = 0;//完成率的分子部分(已完成的)
				while($rs = $DB->fetch_assoc($query)){
					if($rs['vd0']!='' && $rs['assay_over']==1){
						$fenzi++;//完成的个数
					}
					$tid_arr[] = $rs['tid'];
					$rs1 = $rs;
				}
				$rs1['tid'] = $tid_arr;
				$finish  = $fenzi."/".$num;//完成率
				if($fenzi==$num){
					$src = "yes_new.ogg";
					$win = "已完成";
				}else{
					$src = "no_new.ogg";
					$win = "未完成";
				}
				$cid = $rs1['cid'];
				$cyd_id = $rs1['cyd_id'];
				$sid = $rs1['sid'];
				$cy_date = !empty($rs1['cy_date']) ? $rs1['cy_date'] : "无";
				$cy_dept = !empty($rs1['cy_dept']) ? $rs1['cy_dept'] : "无";
				$tid_arr = !empty($rs1['tid']) ? $rs1['tid'] : "无" ;
				!empty($rs1['river_name'])?$river=$rs1['river_name']:$river="无";
				!empty($rs1['site_name'])?$site_name=$rs1['site_name']:$site_name="无";
				!empty($rs1['site_address'])?$site_address=$rs1['site_address']:$site_address="无";
				!empty($rs1['group_name'])?$group_name=$rs1['group_name']:$group_name="无";
				$jg = 1;
				$_SESSION['bianhao'][] = $_GET['bh'];//已经读过的编号 存入session全局变量
			}
			//$_SESSION['bianhao'][] = $_GET['bh'];//已经读过的编号 存入session全局变量
		}else{//非编号
			$src = "roung.ogg";
		}
	}
	else{//已经刷过一次
		$src = "roung.ogg";
	}
	// print_rr($cy_date);
	$cy_Ym = substr($cy_date , 0 , 7);
	$year = substr($cy_date, 0 , 4);
	$month = substr($cy_date, 5 , 2);
	$arr=array(
				"jg"       =>$jg,
				"src"      =>$src,
				"win"      =>$win,
				"finish"   =>$finish,
				"tid"	   =>$tid_arr,
				"river"    =>$river,
				"site_name"=>$site_name,
				"ypbh"  =>"<a onclick='to_hyd_list(this,$year,$month);' style='cursor:pointer;'>".$_GET['bh']."</a>",
				"site_address"	=>$site_address,
				"group_name"	=>"<a href='$rooturl/baogao/bg_chakan.php?cid=$cid&cyd_id=$cyd_id&sid=$sid' target='__blank'>".$group_name."</a>",
				"cy_dept"  =>$cy_dept,
				"cy_date"  =>$cy_date,

				);
	echo json_encode($arr);
	exit;
}
?>
