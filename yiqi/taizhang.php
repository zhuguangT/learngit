<?php
//仪器台帐文件
      include "../temp/config.php";
$page = $_POST['page'];
if(!$page){
    $page = '1';
}
unset($_POST['page']);
$thang =  "";
$ttou ="<td>序号</td>";
$tstr = "";
$xu = 1;
$fenye = 13;
$xiazai = "";
if($_POST){
    $_GET['ziduan']= '';
    //$ziduan = $_POST;
    foreach($_POST as $kk=>$vv){
        $tarr[] = $kk;
        $ttou .= "<td>$vv</td>";
        $zistr .= '|'.$kk.'*'.$vv;
    }
    $yqsql = $DB->query("select * from yiqi where fzx_id='$fzx_id'");
    while($yq = $DB->fetch_assoc($yqsql)){
        if($xu%$fenye==0){
            $thang .= "</table><DIV STYLE=\"page-break-after:always\"></DIV> <br/>";
            $thang .= "<center><table style='width:28cm;border-collapse:collapse;text-align:center;'><tr><td width='60%' align='left'><font face='宋体' size='3px'>国家城市供水水质监测网</font>•<font face='隶书' size='4px'>兰州监测站</font></small></td></tr></table><hr style='width:28cm;'/><SPAN style='TEXT-DECORATION: underline'><font size='6px' face='宋体'><B>设备台账</B></font></span></center><table style='width:28cm;border-collapse:collapse;text-align:center;' border='1px' cellpadding='0' cellspacing='0' align='center'><tr height='40px'>{$ttou}</tr>";
        }
        $thang .=  "<tr height='40px'><td>$xu</td>";
        foreach($tarr as $zhi){
            $thang .= "<td width='12%'>".$yq[$zhi]."</td>";
        }
        $thang .=  "</tr>";
        $xu++;
        
    }
    $xiazai = "<input type=\"button\"  value=\"打印\" id='dayin' onclick=\"dayin(this)\" value='打印' style='width:88px;height:44px;'>";
    $xzbtn="<input type=\"button\"  value=\"下载\" id='xiazai' onclick=\"xiazai(this)\" ziduan = {$zistr} style='width:88px;height:44px;'>";
    echo temp('yqtaizhang');
}elseif($_GET['ziduan']){
    $ziarr = explode('|',$_GET['ziduan']);
    foreach($ziarr as $zi){
        if($zi){
            $z = explode('*',$zi);
            $po[$z[0]] = $z[1];
        }
    }
    foreach($po as $kk=>$vv){
        $tarr[] = $kk;
        $ttou .= "<td>$vv</td>";
    }
    $yqsql = $DB->query("select * from yiqi where fzx_id='$fzx_id'");
    while($yq = $DB->fetch_assoc($yqsql)){
        if($xu%$fenye==0){
            $thang .= "</table><DIV STYLE=\"page-break-after:always\"></DIV> <br/>";
            $thang .= "<center><table style='width:23cm;border-collapse:collapse;text-align:center;'><tr><td width='60%' align='left' colspan='4'><font face='宋体' size='3px'>国家城市供水水质监测网</font>•<font face='隶书' size='4px'>兰州监测站</font></small></td></tr></table><hr style='width:28cm;'/><SPAN style='TEXT-DECORATION: underline'><font size='6px' face='宋体'><B>设备台账</B></font></span></center><table style='width:28cm;border-collapse:collapse;text-align:center;' border='1px' cellpadding='0' cellspacing='0' align='center'><tr height='40px'>{$ttou}</tr>";
        }
        /*
        <td width='25%' colspan='3'><font face='宋体' size='3px'>记录编号：LZSZJC-RM-Z-35</font></td><td width='15%'><font face='宋体' size='3px'>版本号：AO</font></td>这段代码原本是加在兰州监测站td之后的，现不用故删之
         */
        $thang .=  "<tr height='88px'><td>$xu</td>";
        foreach($tarr as $zhi){
            $thang .= "<td width='12%'>".$yq[$zhi]."</td>";
        }
        $thang .=  "</tr>";
        $xu++;
    }
     $xiazai = "";
    $xzbtn='';
    header("Content-Type:application/msexcel");        
    header("Content-Disposition:attachment;filename=仪器台帐.xls");        
    header("Pragma:no-cache");        
    header("Expires:0");  
    echo temp('yqtaizhang');
}else{
    echo "<script>alert('您没有选择任何参数！');</script>";
    gotourl("$rooturl/yiqi/hn_yiqimanager.php?page=$page");
}