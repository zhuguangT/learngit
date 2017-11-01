<?php


      //仪器维护的显示页面
     include "../temp/config.php";
 
 
        $id=$_GET['id'];
       
        
        $R=$DB->query("select * from `yiqi` where id=$id ");  
        $a=$DB->fetch_assoc($R);
        $yq_mingcheng=$a['yq_mingcheng'];//yq_mingcheng 设备名称
   	    $yq_sbbianhao=$a['yq_sbbianhao'];//yq_sbbianhao 编号
   	    $yq_xinghao=$a['yq_xinghao'];//yq_xinghao 型号	
   	    $yq_baoguanren=$a['yq_baoguanren'];//yq_baoguanren 保管人
   	    $yq_sbdidian=$a['yq_sbdidian'];//yq_sbdidian 设备存放地点

       
 
        
        
         $Q=$DB->query("select * from yiqi_weihu where yid=$id ");  
            while($r=$DB->fetch_assoc($Q)){
			$operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete_weihu.php?action=删除&wid=$r[id]&yid=$r[yid]'\">删除</a> 
		|<a href='yq_weihujilu_update.php?action=修改&wid=$r[id]&yid=$r[yid]'>修改</a>
		|<a href='yq_weihujilu_update.php?action=详细&wid=$r[id]&yid=$r[yid]'>详细</a>";

		$lines.=temp('yq_weihujilu_line.html');
    }

          
            $g=$DB->query("select * from yiqi_weihu where yid=$id and no!='' ");  
            while($d=$DB->fetch_assoc($g))
            $no=$d['no']; 
    	    $display=temp('weihuno.html');
    
 
     disp('yq_weihujilu.html');
     
     
     //disp('yq_weihujilu_line.html');
 
 
 
  ?>
 
