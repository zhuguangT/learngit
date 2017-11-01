<?php



     //仪器维护的修改
     include "../temp/config.php";
      $wxid=$_GET['id'];//维修记录的id
     
      $id=$_GET['yid'];//仪器id
     
      $action=$_GET['action'];
     
     
       $biaotou='修改仪器设备维修记录表';
      if($action=='修改'){
		  
		$R=$DB->query("select * from yiqi_weixiu where id=$wxid "); 
		$r=$DB->fetch_assoc($R);
		$wx_riqi=$r['wx_riqi']; //维护日期	
		$wx_danwei=$r['wx_danwei']; //维护单位	
		$wx_ren=$r['wx_ren'];//维修人 	
		$wx_danhao=$r['wx_danhao']; //维修记录表表单号 	
		$wx_jiluweizhi=$r['wx_jiluweizhi'];//维修记录表位置 	
		$qj_hechashijian=$r['qj_hechashijian']; //期间核查时间 	
		$qj_hechadanhao=$r['qj_hechadanhao']; //期间核查表单号	
		$qj_hechaweizhi=$r['qj_hechaweizhi']; //期间核查位置  
		  
		  
	  }
	  
	  disp('yq_weixiujilu_insert.html');
     
     
     
     
     
     
     
     
     
     
     ?>
