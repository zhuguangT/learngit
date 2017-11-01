<?php

     //正式运行前仪器管理表单增加

     include "../temp/config.php";
     
     
     
      
        $id=$_GET['id'];
     
        
        $zhi=$_GET['action'];
       
        $nid=$_GET['nid'];
        
        $nid2=$_POST['nid'];
         $aid=$_POST['id'];//修改时候传的int1
  
        $biaodanmingcheng=$_POST['biaodanmingcheng'];
        $queren=$_POST['queren'];
        $cundang=$_POST['cundang'];
        $youxiaoqi=$_POST['youxiaoqi'];
        $weizhi=$_POST['weizhi'];
        		
		$biaotou='增加仪器表单';
        if($zhi=='删除'){
			 $R=$DB->query("delete  from  n_set where id=$nid ");
			  gotourl("$rooturl/yiqi/yq_guanlibiaodan.php?id=$id");
		}
        
        
        if($biaodanmingcheng!='' && $nid2=='' ){
			
		    $DB->query("insert into n_set set name='yq_guanlibiaodan', str1='$biaodanmingcheng',
		    str2='$queren',str3='$cundang',str4='$youxiaoqi', str5='$weizhi',`int1`=$aid ");
		    
		    gotourl("$rooturl/yiqi/yq_guanlibiaodan.php?id=$aid");
		}
		
		if($nid!=''){
			$biaotou='修改仪器表单';
			$R=$DB->query("select * from  n_set where id=$nid ");
			
			$r=$DB->fetch_assoc($R);
			
			$biaodanmingcheng=$r['str1'];
			$queren=$r['str2'];
			$cundang=$r['str3'];
			$youxiaoqi=$r['str4'];
			$weizhi=$r['str5'];
		}
     
     
        if($nid2!='' ){
			//更新
			//echo $nid2;exit;
			$DB->query("update n_set set str1='$biaodanmingcheng',str2='$queren',str3='$cundang',str4='$youxiaoqi',str5='$weizhi'
				where id=$nid2 ");
				gotourl("$rooturl/yiqi/yq_guanlibiaodan.php?id=$aid");
						
		}
     disp('yq_guanlibiaodan_save.html');
     
     
     ?>
