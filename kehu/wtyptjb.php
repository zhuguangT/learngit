<?php
include "../temp/config.php";
//查看是否未下载
if($_GET['handle']=='download'){
	header("Content-Type:application/msexcel");        
    header("Content-Disposition:attachment;filename=委托样品统计表.xls");        
    header("Pragma:no-cache");        
    header("Expires:0");  
}

//向 n_set 表中增加或修改默认选项
if(!empty($_POST)){
	$name = $_POST['name'];
	if($_POST['action']=='add_project'){
		$sql = "SELECT * FROM `n_set` WHERE `module_value3` = '$u[userid]' AND `module_name` = 'wtyptjb'";
		$data = $DB->fetch_one_assoc($sql);
		$key_arr = explode(",",$data['module_value2']);
		$key_va = array_flip($key_arr);
		$project = str_replace(str_pad($key_va[$name],2,0,STR_PAD_LEFT),$name,$data['module_value1']);
		$sql_update = "UPDATE `n_set` SET `module_value1` = '$project' WHERE  `module_value3` = '$u[userid]' AND `module_name` = 'wtyptjb'";
		$DB->query($sql_update);
		echo 'ok';
	}else if($_POST['action']=='up_project'){
		$sql = "SELECT * FROM `n_set` WHERE `module_value3` = '$u[userid]' AND `module_name` = 'wtyptjb'";
		$data = $DB->fetch_one_assoc($sql);
		$arr = explode(",",$data['module_value1']);
		//通过函数利用键值获得键名
		$arr_va = array_flip($arr);
		$project = str_replace(str_pad($name,2,0,STR_PAD_LEFT),str_pad($arr_va[$name],2,0,STR_PAD_LEFT),$data['module_value1']);
		//将原本键名的键值放入数据库
		$sql_update = "UPDATE `n_set` SET `module_value1` = '$project' WHERE  `module_value3` = '$u[userid]' AND `module_name` = 'wtyptjb'";
		$DB->query($sql_update);
		echo 'ok';
	}
	die;
}
//查询 n_set 表  看看此id是否登陆过这个页面 如果没有就写入默认的显示选项
$sql = "SELECT * FROM `n_set` WHERE `module_name` = 'wtyptjb' AND `module_value3` = '{$u['userid']}'";
if(!$re=$DB->fetch_one_assoc($sql)){
	$module_value = "序号,月,报告编号,委托协议书编号,委托单位,样品类别,样品名称,样品编码,备注,采样时间,送样时间";
	$sql = "INSERT INTO `n_set` (`module_name` , `module_value1` , `module_value2` , `module_value3`) VALUES('wtyptjb' , '$module_value' , '$module_value' , '$u[userid]')";
	$DB->query($sql);
}
if(!empty($_GET['year'])){
	$selected_year="<option>$_GET[year]</option>";
}

$select_year="<select class='select_year' onchange='change_year();'>
			$selected_year
			<option>选择查看年份</option>";
$now_year=date('Y',time());
$z=$begin_year;
while($z<=$now_year){
	$select_year.="<option>$z</option>";
	$z++;
}			
$select_year.="</select>";
if(!empty($_GET['month'])){
	$selected_month = "<option>$_GET[month]</option>";
}
$select_month="<select class='select_month' onchange='change_month();'>$selected_month<option>选择查看月份</option><option>全部</option>";
for($m=1;$m<13;$m++){
	$m=str_pad($m,2,0,STR_PAD_LEFT);
	$select_month.="<option>$m</option>";
}
$select_month.="</select>";
//通过对n_set表的查询得到表格的选项
$sql = "SELECT * FROM `n_set` WHERE `module_name` = 'wtyptjb' AND `module_value3` = '$u[userid]'";
$data = $DB->fetch_one_assoc($sql);
$checked_arr=explode(',',$data['module_value1']);
$check_arr = explode(',',$data['module_value2']);
$num = count($check_arr);
for($i=0;$i<$num;$i++){
	if(in_array($checked_arr[$i],$check_arr)){
		$check_data .= <<<ETT
			<li><label><input type="checkbox" checked="checked" value="$checked_arr[$i]" data-field="$checked_arr[$i]" onclick='check(this);'> $checked_arr[$i]</label></li>
ETT;
	//前台显示列表自定义选项
		if(!is_int($checked_arr[$i])){
			$table_pro.="<td name='$checked_arr[$i]' >$checked_arr[$i]</td>";// style='cursor:pointer;'onclick='order_by(M);'
		}
	}else{
		$check_data .= <<<ETT
			<li><label><input type="checkbox" value="$check_arr[$i]" data-field="$check_arr[$i]" onclick='check(this);'> $check_arr[$i]</label></li>
ETT;
	//前台显示列表自定义选项
		if(!is_int($check_arr[$i])){
			$table_pro.="<td name='$check_arr[$i]' style='display:none;' >$check_arr[$i]</td>";// style='cursor:pointer;'onclick='order_by(M);'
		}		
	}
}


//自定义筛选
if(empty($_GET['year']) || $_GET['year']=='选择查看年份'){
	$where =" AND LEFT(cy.cy_date,4) =". date('Y',time());
}else{
	$y="<option selected>".$_GET['year']."</option>";
	$where =" AND LEFT(cy.cy_date,4) = $_GET[year]";
}
if(!empty($_GET['month']) && $_GET['month']!='选择查看月份'&& $_GET['month']!='全部'){
	$m="<option selected>".$_GET['month']."</option>";
	$where.=" AND SUBSTRING(cy.cy_date,6,2) = '$_GET[month]'";
}
if(!empty($_GET['name'])){	
	$where.=" AND cy.cy_dept LIKE '%$_GET[name]%'";
}
//外接 site（站点） id   关联 cy_rec 表中 sid
if(!empty($_GET['sites'])){
	$where .=" AND cy_rec.sid IN ($_GET[sites]) ";
}
$e       =	$_GET['e'];
$order   =	$_GET['order'];



//ajax自定义排序(目前自定义显示功能开启后，取消自定义排序功能)
if($_POST['action']=="order_by"){
	$by=$_POST['name'];
	echo 'ok'.$by;
	die;
}
if($e=='bar_code'||$e=='site_name'){
	$table='cy_rec.';
}else{
	$table="cy.";
}
if($e!=''){
	$sql_order=" ORDER BY $table$e $order";//GROUP BY hy.yq_xinghao
}

$sql="SELECT *,cy.id as cyd_id , cy.cy_date as cydate FROM `cy` LEFT JOIN `cy_rec` ON cy.id=cy_rec.cyd_id WHERE cy.`site_type`=3 ".$where.' GROUP BY cy.id' .$sql_order;
$re=$DB->query($sql);
$i=1;
$w=0;
while($data=$DB->fetch_assoc($re)){
	$i=str_pad($i,2,0,STR_PAD_LEFT);
	$w=str_pad($w,3,0,STR_PAD_LEFT);
	//$data['Y']+$data['M']+"report_detail表中的bg_bh字段（9999代表没有编号）"
	$sql="SELECT * FROM `report` WHERE cy_rec_id = '$data[id]'";
	$res=$DB->fetch_one_assoc($sql);
	if($res['bg_bh']=='9999'){
		$wb='----';
	}
	//采样月份
	$month = substr($data['cydate'],5,2);
	//补齐三位数	
	$wb=str_pad($res['bg_bh'],3,0,STR_PAD_LEFT);
	//拼接委托编号
	$wtbh=str_replace('-','',substr($data['cydate'],0,7)).$wb;
	$year = substr($data['cydate'] , 0 ,4);
	//拼接采样日期
	$cy_date_month = substr($data['cydate'] , 5 ,2);
	$cy_date_day = substr($data['cydate'] , 8 ,2);
	$cy_date = $cy_date_month."月".$cy_date_day."日";
	//拼接送样日期
	$sy_date_month = substr($res['sj_date'],5,2);
	$sy_date_day = substr($res['sj_date'],8,2);
	$sy_date = $sy_date_month."月".$sy_date_day."日";
	$lines.= temp("wtyptjb_line");
	$i++;
	$w++;
}
$total = $w;
if($_GET['handle']=='download'){
	echo temp("wtyptjb_download");
}else{
	disp("wtyptjb");
}

