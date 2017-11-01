<?php
include "../temp/config.php";
$trade_global['daohang'][]	=	array('icon'=>'','html'=>'合格供应商名录','href'=>"$rooturl/gys/gys_list.php");
if($_POST){
    $pingjia_ren=$u['nickname'];
    $time=date('Y-m-d',time());
    if($_POST['status']=='add'){
        $sql="insert into `ghs_pingjia`(`gys_id`,`wupin_name`,`zhiliang_shuiping`,`jiaohuo_xinyu`,`fuwu_zhiliang`,`shebei_sheshi`,`zhiliang_baozheng`,`pingjia_ren`,`time`)values('$_POST[gys_id]','$_POST[wupin_name]','$_POST[zhiliang_shuiping]','$_POST[jiaohuo_xinyu]','$_POST[fuwu_zhiliang]','$_POST[shebei_sheshi]','$_POST[zhiliang_baozheng]','$pingjia_ren','$time')";
    }else{
        $sql="update `ghs_pingjia` set `wupin_name`='$_POST[wupin_name]',`zhiliang_shuiping`='$_POST[zhiliang_shuiping]',`jiaohuo_xinyu`='$_POST[jiaohuo_xinyu]',`fuwu_zhiliang`='$_POST[fuwu_zhiliang]',`shebei_sheshi`='$_POST[shebei_sheshi]',`zhiliang_baozheng`='$zhiliang_baozheng',`pingjia_ren`='$pingjia_ren',`time`='$time' where `id`=$id";
    }
    $info=$DB->query($sql);
    gotourl("$rooturl/gys/ghs_pingjia.php?id=$_POST[gys_id]");
}else{
    $id=$_GET['gys_id'];
    $pingjia_id=$_GET['pingjia_id'];
    //判断是编辑还是添加
    if($pingjia_id){
        $status='edit';
        $sql="select * from `ghs_pingjia` where `id`='$pingjia_id'";
        $rs=$DB->fetch_one_assoc($sql);
        $pingjia_ren=$rs['pingjia_ren'];
        $time=$rs['time'];
        $button='<input type=submit class="btn btn-xs btn-primary" value="编辑完成">';
    }else{
        $status='add';
        $pingjia_ren=$u['nickname'];
        $time=date('Y年m月d日',time());
        $button='<input type=submit class="btn btn-xs btn-primary" value="添加评价">';
    }
    $sql="select `sname`,`dz` from `gys_gl` where `id`=$id";
    $gys_arr=$DB->fetch_one_assoc($sql);
    $title='供货商评价表';
    disp('ghs_pingjia_line');
}
?>