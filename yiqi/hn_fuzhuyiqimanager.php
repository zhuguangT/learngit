<?php





     //辅助仪器显示页面
     include "../temp/config.php";
     
     
      $a=$_GET['a'];
      $b=$_GET['b'];
      $c=$_GET['c'];
      $d=$_GET['d'];

     $R=$DB->query("select * from n_set where name='fuzhuyiqi'  ");
        
        
        
      $sql="select * from n_set where 1=1  and name='fuzhuyiqi' ";
			if ($a!=''){
			  $sql.=" and  str1 like '%$a%' ";
			}
			if ($b!=''){
			  $sql.=" and   str3 like '%$b%' ";
			}         
			if($c!=''){
		      if($c=='全部'){
                 }
               else{
			  $sql.=" and   `int1` like '%$c%' ";
		       }
			}
			if($d!=''){
			 $sql.=" and   str5 like '%$d%' ";
			}     

    $R=$DB->query($sql);
    $i=1;
    while($r=$DB->fetch_assoc($R)){
			$operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='fuzhuyiqi_save.php?action=删除&id=$r[id]'\">删除</a> 
		|<a href=fuzhuyiqi_save.php?action=修改&id=$r[id]>修改</a>";
		   
		  if($r[int1]==1){$zhuangtai='合格'; $color='green'; } 
		  elseif($r[int1]==2){$zhuangtai='准用'; $color='blue';}
		  else {$zhuangtai='停用';$color='red'; }
		$lines.=temp('hn_fuzhuyiqimanager_line.html');
		
	$i++;
    }

     disp('hn_fuzhuyiqimanager.html');



?>
