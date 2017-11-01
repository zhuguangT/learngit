<?php
  //培训后台处理
 include "../temp/config.php";

$fzx_id         = $u['fzx_id'];
      $a=$_GET['a'];
      $b=$_GET['b'];
      $c=$_GET['c'];
      $d=$_GET['d'];
	  $yq_type = $_GET['yq_type'];
    $R=$DB->query("select * from `yiqi` WHERE `fzx_id`='{$fzx_id}' "); 
    $sql="select * from  `yiqi` where 1=1 AND `fzx_id`='{$fzx_id}' ";
			if($yq_type=='' || empty($_GET['yq_type'])){
				$yq_type = '计量仪器';
				$sql.=" and  yq_type='$yq_type' ";
			}else{
				$sql.=" and  yq_type='$yq_type' ";
			}
			if ($a!=''){
			  $sql.=" and  yq_mingcheng like '$a%' ";
			}
			if ($b!=''){
			  $sql.=" and   yq_chucangbh like '$b%' ";
			}         
			if($c!=''){
		      if($c=='全部'){
				    $qb="selected='selected'";
                 }
               elseif($c!='近期需检定'){
				   if($c=='启用')$hg="selected='selected'";
				   elseif($c=='准用')$zy="selected='selected'";
				   elseif($c=='封存')$fc="selected='selected'";
				 
				   else $bf="selected='selected'";
			  $sql.=" and   yq_state like '%$c%' ";
		       }
		       else{
				   $asql="SELECT * FROM `yiqi` WHERE `fzx_id`='{$fzx_id}' AND (yq_state='启用' or yq_state='准用') ";
				   $jinqi="selected='selected'";
				   }
			}
			if($d!=''){
			 $sql.=" and   yq_baoguanren like '$d%' ";
			} 
		
		if($asql!=''){
			$R=$DB->query($asql); 
			    while($r=$DB->fetch_assoc($R)){
					$tixing=$r['yq_tixingriqi'];// 提醒天数3
$zuijin=$r['yq_jiandingriqi'];// 最近
$zhouqi=$r['yq_jdriqi'];//yq_jdriqi 12
$year=date("Y",strtotime($zuijin));
$yue=date("m",strtotime($zuijin));
$day=date("d",strtotime($zuijin));
$a=$yue+$zhouqi;
$nianjisuan=floor($a/12);//计算出是几年
if($nianjisuan<1){
$yue=$yue+$zhouqi;
}else{
$year=($nianjisuan-1)+$year; 
}
$a= date("$year-$yue-$day ",time());
$tx= strtotime($a)-(3600*24*$tixing);
$t=date("Y-m-d",$tx);//提醒日期
$a= date('Y-m-d',time());
	  $operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete.php?action=删除&yid=$r[id]'\">删除</a> 
		|<a href=yiqi_update.php?action=修改&yid=$r[id]>修改</a>|<a href=yiqi_detail.php?action=详细&yid=$r[id]>详细</a>";
		    if($r['yq_state']=='启用') $color='green'; 
		    elseif($r['yq_state']=='准用') $color='blue';
		    else $color='red';	    
		if($a>$t)$lines.=temp('hn_yiqimanager_line.html');
	$i++;
    }
    disp('hn_yiqimanager.html');
			}
	   else $R=$DB->query($sql); 
       $mc=$a;
       $ccbh=$b;
       $bgr=$d;   
      $i=1;
    while($r=$DB->fetch_assoc($R)){
	  $operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete.php?action=删除&yid=$r[id]'\">删除</a> 
		|<a href=yiqi_update.php?action=修改&yid=$r[id]>修改</a>|<a href=yiqi_detail.php?action=详细&yid=$r[id]>详细</a>";
		//    if($r['yq_state']=='启用') $color='green'; 
		  //  elseif($r['yq_state']=='准用') $color='blue';
                //		    else $color='red';		    
		$lines.=temp('hn_yiqimanager_line.html');
	$i++;
    }
    disp('hn_yiqimanager.html');
?> 





