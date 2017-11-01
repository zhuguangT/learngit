<?php
include "../temp/config.php";
$id=$_GET['id'];
$trade_global['daohang'][]	=	array('icon'=>'','html'=>'供货商评价表','href'=>"$rooturl/gys/ghs_pingjia.php?id=$id");
$sql="select `sname`,`dz` from `gys_gl` where `id`=$id";
$gys_arr=$DB->fetch_one_assoc($sql);
$title='供货商评价表';
$sql="select * from `ghs_pingjia` where `gys_id`=$id";
$query=$DB->query($sql);
$i=1;
while($r=$DB->fetch_assoc($query)){
    $lines.=<<<EOF
    <tr>
        <td>$i</td>
        <td>$gys_arr[sname]</td>
        <td>$r[wupin_name]</td>
        <td>$r[pingjia_ren]</td>
        <td>$r[time]</td>
        <td><a href='gys_pingjia_add.php?gys_id=$id&pingjia_id=$r[id]'>编辑</a> | <a href='gys_pingjia_del.php?id=$id&pingjia_id=$r[id]'>删除</a></td>
    </tr>
EOF;
    $i++;
}
$num=$i-1;
disp('ghs_pingjia');
?>