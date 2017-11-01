<?php
//仪器检定记录的的显示页面
include "../temp/config.php";
$id=$_GET['id'];
$sql="select * from n_set where name ='yiqi' and `int1`=$id";
$rs = $DB->query($sql);
$i=1;
while($r = $DB->fetch_assoc($rs)){
	$lines.=temp('jiandingjilu_line.html');
	$i++;
}
disp('jiandingjilu.html');  
?>
