<?php

     //正式运行前仪器管理表单web

     include "../temp/config.php";
     
     $id=$_GET['id'];
     $R=$DB->query("select * from n_set where name='yq_guanlibiaodan' and `int1`=$id ");  
    $i=1;
    while($r=$DB->fetch_assoc($R)){
			$operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='yq_guanlibiaodan_save.php?action=删除&nid=$r[id]&id=$r[int1]'\">删除</a> 
		| <a href=yq_guanlibiaodan_save.php?action=修改&nid=$r[id]&id=$r[int1]>修改</a>";
		$lines.=temp('yq_guanlibiaodan_line.html');
		
	$i++;
    }
    disp('yq_guanlibiaodan.html');
     
     
   // disp('yq_guanlibiaodan_line.html');
     
     
     ?>
