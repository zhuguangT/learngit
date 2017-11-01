<?php
/**
 * 功能：显示修改标准样品的页面
 * 作者：zhengsen
 * 时间：2014-06-19
**/
include('../temp/config.php');
if($u[userid] == ''){
	nologin();
}
if($_POST['action']=='del_add_bzyp'){
	$del_sql="DELETE FROM `cy_rec` WHERE id='".$_POST['rec_id']."'";
	$query=$DB->query($del_sql);
	if(mysql_affected_rows()){
		echo '1';
	}else{
		echo '0';
	}
	exit();
}
if(!empty($_GET['cyd_id'])){
	$rec_sql="SELECT cr.id,b.wz_bh,b.wz_name,cr.assay_values FROM `cy_rec` cr left join `bzwz` b on cr.by_id=b.id WHERE cyd_id='".$_GET['cyd_id']."' and sid='-3' order by b.wz_bh";
	$rec_query=$DB->query($rec_sql);
	while($rec_rs=$DB->fetch_assoc($rec_query)){
		$value_C=array();
		$xm_sql="SELECT value_C FROM `assay_value` WHERE id IN (".$rec_rs['assay_values'].")";
		$xm_query=$DB->query($xm_sql);
		while($xm_rs=$DB->fetch_assoc($xm_query)){
			$value_C[]=$xm_rs['value_C'];
		}
		$value_str=implode(',',$value_C);
		$modi_add_bzyp_lines.="<tr><td>".$rec_rs['wz_bh']."</td><td>".$rec_rs['wz_name']."</td><td>".$value_str."</td><td><a  onclick=del_add_bzyp(this,{$rec_rs[id]})>删除</a></td></tr>";
	}
echo temp("modi_add_bzyp.html");
}
?>