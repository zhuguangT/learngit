<?php
/**
  * 功能：根据提供的采样任务通知单号,显示采样任务明细,如果该张采样单尚未生成化验单，允许在此界面删除标样质控
  * 作者：zhengsen
  * 时间：2014-04-22
*/

include "../temp/config.php";
if($u[userid] == '') nologin();
$R=$DB->query("select * from `cy_rec` where `cyd_id`='$cyd[id]' and `status`=1 order by `id`");
if($gx_btgs=='hn')
$R=$DB->query("select * from `cy_rec` where `cyd_id`='$cyd[id]' and `status`=1 order by `bar_code`");
$n=$DB->rows;
$yp_flag=array('0'=>"普通样品",'1'=>"全程序空白",'2'=>"标准样品",'3'=>"普通样品",'-3'=>'现场平行','4'=>"室内平行",'5'=>"加标回收",'6'=>'室内平行','7'=>"室内平行",'8'=>'加标回收','9'=>'室内平行且加标回收','12'=>'室内平行且加标回收');
while($r=$DB->fetch_assoc($R)){
  $zk_info=$yp_flag[$r['zk_flag']];
  $title=$delete='';
  if($u['sys_manage']){
    if($zk_info!='普通样品') $zk_info="<font color='blue'>$zk_info</font>";
    $sys_manage=" class='canclick' title='点击修改样品编号' onclick=modify_bar_code($r[id],'$r[bar_code]')";
    if(in_array($r[zk_flag],array(2,6))){
        $by=$DB->fetch_one_assoc("select id,wz_bh from bzwz where id='$r[by_id]'");
        $zk_info="<font color='blue' onclick=modify_wz_bh($r[id],'$by[wz_bh]') title='点击修改标样编号'>$by[wz_bh]</font>";
    }
    if(in_array($r['zk_flag'],array(2,6)) && $cyd['status']=='5'){
      $title=" title='点击红色的 X 删除该标准样品!'";
      $delete="<a href=\"javascript:if(confirm('你真的要删掉这个标准样品吗?')) location='delete_by.php?cid=$r[id]&wz_id=$r[by_id]';\" title='点击红色的 X 删除该标准样品!'><font color='red'>X</font></a>";
    }
  }else $zk_info='普通样品';
 $lines=temp('cy_mx_line.html');
}  
$dayinbq="<center><a href=\"$rooturl/caiyang/dayin_biaoqian.php?cyd_id=$_GET[cyd_id]\">打印标签</a></center>";
disp('cy_mx.html');
?>
