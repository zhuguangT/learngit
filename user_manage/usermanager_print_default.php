<?php
/*
*人员档案 默认打印格式 页面	
*/
include "../temp/config.php";
$fzx_id		= $_SESSION['u']['fzx_id'];
$page_max	= 16;//每页显示行数
$page_kong	= 'yes';//是否空白行补齐 yes为补齐
//分中心列表
$fzx_arr	= array();
if($u['is_zz']=='1'){
	$hub_list_sql   = $DB->query("SELECT * FROM `hub_info` WHERE 1");
	while($hub_list_rs = $DB->fetch_assoc($hub_list_sql)){
		$fzx_arr[$hub_list_rs['id']]	= $hub_list_rs['hub_name'];
	}
}
$zz='';
if(!empty($_GET['zz'])){//判断是否有传递过来的在职状态的查看（本html页面）
	if($_GET['zz']=='离职'){
		$zz=" and b.group ='离职' ";
	}elseif($_GET['zz']=='在职'){
		$zz=" and b.group !='离职' ";
	}
}
$sql_where	= '';
if(!empty($_GET['fzx'])){
	if($_GET['fzx'] != '全部'){
		$sql_where	.= " AND b.fzx_id=".$_GET['fzx']." ";
	}
}else{
	$sql_where	.= " AND b.fzx_id={$fzx_id} ";
}
//获取人员档案数据
$title	= "<p style='margin:0 auto;width:25cm;text-align:center;font-weight:bold;font-size:15px;'>水环境监测人员基本情况一览表</p>";
$table_header	= " <table class='table  table-bordered  center print' style='width:25cm'>
        <tr align=center>
	<td>序号</td>
	<td>姓名</td>
	<td>性别</td>
	<td>年龄</td>
	<td>文化程度</td>
	<td>职称</td>
	<td>所学专业</td>
	<td>从事技术<br>领域年限</td>
	<td>所在部门<br>岗位</td>
    <td>本岗位<br>年限</td>
	<td>备注</td>
        </tr>";
$page_break	= "<p style='page-break-before:always;'></p>";
$table_str	= '';
$old_fzx_id	= $r_fzx_id	= '';
$sql	= "select a.*,b.* from hn_users as a right join users as b on a.uid=b.id where b.group!='0' AND b.group!='测试组' $sql_where $zz and b.userid != 'admin' ORDER BY  b.fzx_id,a.px_id";
$R		= $DB->query($sql);
$i		= 0;
while($r=$DB->fetch_assoc($R)){
	$i++;
	$r_fzx_id	= $r['fzx_id'];
	if($old_fzx_id=='' || $old_fzx_id!=$r_fzx_id || $i==$page_max){
		if($page_kong == 'yes'){
			if($old_fzx_id != ''){
				$yushu	= $page_max-$i;
			}
			if($yushu >0){
				for($ii=0;$ii<$yushu;$ii++){
					$lines	.= "<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
				}
			}
		}
		//这里将分中心的名称显示出来
		$fzx_name	= "<p style='margin:0 auto;width:25cm'>&nbsp;&nbsp;&nbsp;".$fzx_arr[$r_fzx_id]."</p>";
		$old_fzx_id	= $r_fzx_id;
		if($table_str	== ''){
			$table_str	.= $title.$fzx_name.$table_header;
		}else{
			$table_str	.= $lines."</table>".$page_break.$title.$fzx_name.$table_header;
			$lines	= '';
		}
		$i		= 0;
	}
	//计算年龄
	if(!empty($r['csrq'])){
		$cs_year	= substr($r['csrq'],0,4);
		$now_year	= date('Y');
		$age	= $now_year-$cs_year;
		$r['csrq']	= $age;

	}
    $lines	.= "<tr align=center>
			<td>$r[px_id]</td>
			<td>$r[userid]</td>
			<td>$r[sex]</td>
			<td>$r[csrq]</td>
			<td>$r[whcd]</td>
			<td>$r[zc]</td>
			<td>$r[zy]</td>
			<td>$r[jsnx]</td>
			<td>$r[gw]</td>
			<td>$r[gwsj]</td>
			<td>$r[bz]</td>
		</tr>";
}
if(empty($table_str)){
	$table_str	.= $title.$table_header."<tr><td colspan='12'>没有查询到{$_GET['zz']}人员</td></tr></table>";
}else{
	if($page_kong == 'yes'){
		$yushu	= $page_max-$i;
		if($yushu >0){
			for($ii=0;$ii<$yushu;$ii++){
				$lines	.= "<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
			}
		}
	}
	$table_str	.= $lines."</table>";
}
echo temp(head);
echo "<script>window.print();window.close();</script>";
echo $table_str;
echo temp(bottom);
?>
