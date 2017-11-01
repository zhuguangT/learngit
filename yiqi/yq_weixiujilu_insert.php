
<?php


      //仪器维修的增加页面
     include "../temp/config.php";
     
     
    $id=$_GET['id'];//仪器的id编号
    
    $wx_riqi=$_POST['wx_riqi']; //维护日期	
    $wx_danwei=$_POST['wx_danwei']; //维护单位	
    $wx_ren=$_POST['wx_ren'];//维修人 	
    $wx_danhao=$_POST['wx_danhao']; //维修记录表表单号 	
    $wx_jiluweizhi=$_POST['wx_jiluweizhi'];//维修记录表位置 	
    $qj_hechashijian=$_POST['qj_hechashijian']; //期间核查时间 	
    $qj_hechadanhao=$_POST['qj_hechadanhao']; //期间核查表单号	
    $qj_hechaweizhi=$_POST['qj_hechaweizhi']; //期间核查位置
     
     
    $yid=$_POST['id'];//仪器的id编号,点击保存时候
     

     
     $wxid=$_POST['wxid'];
     
      $action=$_POST['action'];
          $biaotou='新增仪器设备维修记录表';
  
     if($yid!='' && $action==''){
		  $R=$DB->query("insert into yiqi_weixiu set 
		        yid=$yid,
		        wx_riqi = '$wx_riqi',
				wx_danwei  = '$wx_danwei',
				wx_ren = '$wx_ren',
				wx_danhao  = '$wx_danhao',
				wx_jiluweizhi  = '$wx_jiluweizhi',
				qj_hechashijian  = '$qj_hechashijian',
				qj_hechadanhao  = '$qj_hechadanhao',
				qj_hechaweizhi = '$qj_hechaweizhi' ");
				gotourl("$rooturl/yiqi/yq_weixiujilu.php?id=$yid");
		 
	 }
	 	  if($action=='修改'){
		  
		  		    $R=$DB->query("update   yiqi_weixiu set 
		        wx_riqi = '$wx_riqi',
				wx_danwei  = '$wx_danwei',
				wx_ren = '$wx_ren',
				wx_danhao  = '$wx_danhao',
				wx_jiluweizhi  = '$wx_jiluweizhi',
				qj_hechashijian  = '$qj_hechashijian',
				qj_hechadanhao  = '$qj_hechadanhao',
				qj_hechaweizhi = '$qj_hechaweizhi' 
				where id=$wxid
		    ");
		  
		 gotourl("$rooturl/yiqi/yq_weixiujilu.php?id=$yid");
		  
	  }

	 
	 
     
     disp('yq_weixiujilu_insert.html');
  ?>
     
     
     
     
