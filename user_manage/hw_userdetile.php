<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
if($_GET['fzx']){
        $fzx_id = $_GET['fzx'];
}

//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'上岗项目统计','href'=>'user_manage/hw_userwoke.php'),
		array('icon'=>'','html'=>$_GET['user'].'的项目及方法','href'=>'user_manage/hw_userdetile.php?user='.$_GET['user']),
);
$trade_global['css']		= array('datepicker.css');
$trade_global['js']			= array('date-time/bootstrap-datepicker.min.js');
$trade_global['daohang'] = $daohang;

$user = $_GET['user'];
$i = 0;
$sql = "SELECT u.userid,u.id uid, method_number, v.value_C,x.id as fid,if(u.id = x.`userid`,sgz_date,sgz_date2) sgz_date
FROM users u
LEFT JOIN xmfa x ON ( u.id = x.`userid`
OR u.id = x.userid2 )
LEFT JOIN assay_method m ON x.fangfa = m.id
LEFT JOIN assay_value v ON x.xmid = v.id
where u.fzx_id='$fzx_id' and u.userid='$user' and v.value_C !=''  
GROUP BY u.userid, x.xmid";

$rs = $DB->query($sql);
while($r = $DB->fetch_assoc($rs)){
	$i++;
	$vax = $r['value_C'];
         $zheng = $DB->fetch_one_assoc("select * from users_zheng where userid='".$r['userid']."' and fid = '".$r['fid']."'");
	/* if($r['str5']!='' && $r['str4']!=''){
                $strx = '+'.$r['str4'].' days';
                $c1 =  strtotime($strx);
                $date = date("Y-m-d",$c1);
                $c2 = strtotime($date);             
                $today0 = time();
                $today1 = date("Y-m-d",$today0);
                $today2 = strtotime($today);
                //下次检定的日期
                $time2 = strtotime($r['str5']);
                if($c2 >= $time2 && $today2 <= $time2){
                        $color='red';
                }else{
                        $color='';
                }
        }else{
                        $color='';
                }*/
	$lines.=temp('user_manager/hw_userdetile_line');
}
disp('user_manager/hw_userdetile');
?>
