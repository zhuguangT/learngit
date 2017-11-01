<?php
	$db_host='121.42.140.28';
	$db_database='qd_changbu';
	$db_username='qd_root';
	$db_password='63508790';
	$connection=mysql_connect($db_host,$db_username,$db_password);//连接到数据库
	mysql_query("set names 'utf8'");//编码转化
	if(!$connection){
		die("could not connect to the database:</br>".mysql_error());//诊断连接错误
	}
	$db_selecct=mysql_select_db($db_database);//选择数据库
	if(!$db_selecct){
		die("could not to the database</br>".mysql_error());
	}
	$date_arr=explode('-',$_GET['date']);
	$year=$date_arr[0];
	$month=$date_arr[1];
	$sql_cb="SELECT * FROM changbu_data WHERE `cy_date`='".$_GET['date']."'";
	$query_cb=mysql_query($sql_cb);
	$nums=mysql_num_rows($query_cb);
	if($_GET['action']=='back'){
		if($nums){
			$query=mysql_query("UPDATE changbu_data SET `tb_status`='0' WHERE cy_date='".$_GET['date']."' AND tb_status='1'");
			if($query>0){
				$cb_query=1;
			}else{
				$cb_query=0;
			}
		}else{
			$cb_query=-1;
		}
	}else{
		if($nums){
			$query=mysql_query("UPDATE `changbu_data` SET `tb_status`='1' WHERE cy_date='".$_GET['date']."' AND tb_status='0'");
		}
		while($rs_cb=mysql_fetch_array($query_cb)){
			$cb_data[]=$rs_cb;
			
		}
	}
	include("../temp/config.php");
	$cb_query2=$DB->query("DELETE FROM changbu_data WHERE cy_date='".$_GET['date']."'");
	if($_GET['action']=='into'){
		if(!empty($cb_data)){
			foreach($cb_data as $key=>$value){
				$query=$DB->query("INSERT INTO changbu_data values(NULL,'".$value['year']."','".$value['month']."','".$value['day']."','".$value['fzx_id']."','".$value['site_name']."','".$value['xz_area']."','".$value['water_type']."','".$value['cy_date']."','".$value['json_data']."')");
			}	
		}
		if(isset($query)){
			if($query>0){
				$message='同步数据成功！';
			}else{
				$message='同步数据失败！请联系管理员！';	
			}
		}
		if(!$nums){
			$message='数据暂未上报！';
		}
	}
	if($_GET['action']=='back'){
		if($cb_query>0&&$cb_query2>0){
			$message='数据退回成功！';
		}else{
			if($cb_query=='-1'){
				$message='数据暂未上报！';
			}else{
				$message='数据退回失败！请联系管理员！';
			}
		}
	}
	gotourl('tjbg_gb.php?year='.$year.'&month='.$month,$message);

?>