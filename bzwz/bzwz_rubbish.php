<?php
include "../temp/config.php";
$wz_select=<<<ETF
	<option>$_GET[wz_type]</option>
	<option value='0'>全部物质</option>
	<option value='1'>标准溶液</option>
	<option value='2'>标准样品</option>
ETF;
if($_GET['wz_type']=='全部物质'||$_GET['wz_type']=='全部'){
	$wz_type='';
	$type_label = "<td>分类</td>";
}else{
	$wz_type="AND `wz_type`='$_GET[wz_type]'";
}
$sql="SELECT * FROM `bzwz` WHERE `wz_status`=1 $wz_type ORDER BY `wz_type`";
$re=$DB->query($sql);
$lines='';
$num=$DB->num_rows($re);
if($num!=0){
	while($data=$DB->fetch_assoc($re)){
		if($_GET['wz_type']=='全部物质'||$_GET['wz_type']=='全部'){
			$type = "<td>$data[wz_type]</td>";
		}
		$lines.=<<<ETT
			<tr>
				<td>$data[wz_bh]</td>
				<td><a href="bzwz.php?action=查看&wz_id=$data[id]&wz_type=$data[wz_type]">$data[wz_name]</a></td>
				$type
				<td>$data[amount]</td>
				<td>$data[unit]</td>
				<td>$data[time_limit]</td>
				<td>$data[modify_man]</td>
				<td><a href="javascript:if(confirm('确定将编号为$data[wz_bh]的$data[wz_type]永久删除么?'))location='bzwz.php?action=删除&wz_id=$data[id]&wz_type=$data[wz_type]'">彻底删除</a>|<a href="javascript:if(confirm('确定将编号为$data[wz_bh]的$data[wz_type]还原么？'))location='bzwz.php?action=还原&wz_id=$data[id]&wz_type=$data[wz_type]'">还原</a></td>
			</tr>
ETT;
}
	disp('bzwz_rubbish');
}else{
	echo "<script>alert('回收站没有数据');window.close();</script>";
}