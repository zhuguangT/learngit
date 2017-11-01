<?php
//功能 试剂器皿管理  详细信息 新增 修改 页面
include "../temp/config.php";
$daohang_name	= '新增试剂';
switch($_GET['action']){
case 'new_sjqm':$daohang_name	= "新增试剂";break;
case 'see':$daohang_name	= "查看详细信息";break;
case '修改':$daohang_name	= "修改详细信息";break;
case '入库':$daohang_name       = "入库";break;
case '出库':$daohang_name       = "出库";break;
}
//导航
$daohang= array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'库房管理','href'=>"$rooturl/sjqm/sjqm_list.php"),
	array('icon'=>'','html'=>$daohang_name,'href'=>"$rooturl/sjqm/sjqm.php?action={$_GET['action']}&type={$_GET['type']}")
);
//查找物品类型
$sql_select = "SELECT * FROM `sjqm` GROUP BY type ORDER BY id";
$re_select = $DB->query($sql_select);
$type_arr=array();
$i = 1;
while ($data_select = $DB->fetch_assoc($re_select)) {
	$type_str = $data_select['type'];
	$type_select .="<option value=$type_str>$type_str</option>";
	array_push($type_arr,$data_select['type']);
	$i++;
}
$count_type = "<input type='hidden' name='count_type' value='$i'>";
//$trade_global['js'] = array('bootbox.min.js');
$trade_global['daohang']= $daohang;
$fzx_id = $u['fzx_id'];
$user_select= '';
if($_POST['name']){
	$_POST['name']		= trim($_POST['name']);
	$_POST['nice_name']	= trim($_POST['nice_name']);
	$_POST['fenzi_shi']	= trim($_POST['fenzi_shi']);
	$_POST['jibie']		= trim($_POST['jibie']);
	$_POST['guige']		= trim($_POST['guige']);
	$_POST['kucun']		= intval($_POST['kucun']);
	$_POST['KCtixing']	= intval($_POST['KCtixing']);
	$_POST['danjia']	= trim($_POST['danjia']);
	$_POST['danwei']	= trim($_POST['danwei']);
	$_POST['user']		= trim($_POST['user']);
	$_POST['pihao']		= trim($_POST['pihao']);
	$_POST['changjia']	= trim($_POST['changjia']);
	$_POST['youxiaoqi']	= trim($_POST['youxiaoqi']);
	$_POST['GQtixing']	= trim($_POST['GQtixing']);
	$_POST['beizhu']	= trim($_POST['beizhu']);
}
//新建信息
if($_POST['add']){
	if(!empty($_POST['name'])){
		$sql = "SELECT `id` FROM `sjqm` WHERE `fzx_id`='$fzx_id' AND `name` = '$_POST[name]'";
		//if($DB->fetch_one_assoc($sql)){
			//echo "<script>alert('该名称已存在名称不能为空');window.history.go(-1)</script></script>";die;
		//}
		$sql="INSERT INTO `sjqm`(`fzx_id`, `name`, `nice_name`,`fenzi_shi`, `type`, `jibie`, `guige`, `kucun`,`KCtixing`,`danjia`, `danwei`, `gl_user`, `pihao`, `changjia`, `youxiaoqi`,`GQtixing`, `beizhu`, `add_user`, `add_date`) VALUES(
			'$fzx_id',
			'$_POST[name]',
			'$_POST[nice_name]',
			'$_POST[fenzi_shi]',
			'$_POST[type]',
			'$_POST[jibie]',
			'$_POST[guige]',
			'$_POST[kucun]',
			'$_POST[KCtixing]',
			'$_POST[danjia]',
			'$_POST[danwei]',
			'$_POST[user]',
			'$_POST[pihao]',
			'$_POST[changjia]',
			'$_POST[youxiaoqi]',
			'$_POST[GQtixing]',
			'$_POST[beizhu]',
			'$u[userid]',
			'".date('Y-m-d')."')";
		if($DB->query($sql)){
			$sql = "INSERT INTO `sjqm_ls`(`sj_id`, `type`, `time`, `shuliang`, `zhaiyao`, `jiecun`, `user`) VALUES('".($DB->insert_id())."','r','".time()."','$_POST[kucun]','起始库存','$_POST[kucun]','$u[userid]') ";
			$DB->query($sql);
			echo "<script>alert('新建成功');location.href='sjqm_list.php?type={$_POST['type']}'</script>";die;
		}
	}else{
		echo "<script>alert('名称不能为空');window.history.go(-1)</script></script>";die;
	}
}
//修改信息
if($_POST['fix']){
	if(!empty($_POST['name'])){
		$sql="UPDATE `sjqm` SET  `name`= '$_POST[name]',`nice_name`= '$_POST[nice_name]',`type` = '$_POST[type]',`fenzi_shi`= '$_POST[fenzi_shi]',`jibie`= '$_POST[jibie]',`guige`= '$_POST[guige]',`kucun`='$_POST[kucun]',`KCtixing`='$_POST[KCtixing]',`danjia`='$_POST[danjia]', `danwei`='$_POST[danwei]', `gl_user`='$_POST[user]', `pihao`='$_POST[pihao]', `changjia`= '$_POST[changjia]',`youxiaoqi`='$_POST[youxiaoqi]',`GQtixing`= '$_POST[GQtixing]', `beizhu`='$_POST[beizhu]'where id='$_POST[id]'";
		if($DB->query($sql)){
			echo "<script>alert('修改成功');location.href='sjqm_list.php?type={$_POST['type']}';</script>";
		}
	}else{
		echo "<script>alert('名称不能为空');window.history.go(-1)</script></script>";die;
	}
}

//新建或修改完信息后，更新单位的下拉菜单
if($_GET['action'] == 'new_sjqm' || $_GET['action'] == '修改'){
	$sql	= "SELECT `danwei` FROM `sjqm` WHERE `fzx_id`='$fzx_id' GROUP BY `danwei`";
	$query	= $DB->query($sql);
	$danwei	= '<select name="danwei1" onchange="change_danwei(this)"><option value=""></option>';
	while ($row = $DB->fetch_assoc($query)) {
		if(trim($row['danwei']) == ''){
			continue;
		}
		$danwei .= '<option value="'.$row['danwei'].'">'.$row['danwei'].'</option>';
	}
	$danwei .= '</select>';
}else{
	$danwei = '';
}
$type = ($_GET['type'] == '全部') ? '' : $_GET['type'];
switch($_GET['action']){
case 'new_sjqm':
	// if(!$_GET['type']){
	// 	echo "<script>alert('请选择类别');location.href='sjqm_list.php'</script>";
	// }
	if($r['danwei'] == ''){
		$r['danwei']	= '瓶';
	}
	if($_GET['type'] != '试剂'){
		$fenzishi = '';
	}else{ 
		$fenzishi = '<tr align=center>
						<td width="80">分子式</td>
						<td colspan=3>
							<input type="text" name="fenzi_shi" id="fenzi_shi" value="'.$r['fenzi_shi'].'" size="63" >
							<br><input type="button" id=\'sub\' value="添加下标" onclick="fenzi_shi.value+=\'<sub></sub>\';cursorInput(\'fenzi_shi\');" />
							<input type="button" id=\'sup\' value="添加上标" onclick="fenzi_shi.value+=\'<sup></sup>\';cursorInput(\'fenzi_shi\');" />
							<input type="button" onclick="setCursorPosition(document.getElementById(\'fenzi_shi\'),\'1000\');" value="光标移到末尾" />
							<input type="button" onclick="show_label()" value="显示分子式" />
						</td></tr>';
	}
	$user_select = "<option value='$u[userid]' selected>$u[userid]</option>";
	$title = "<h3 class='header smaller center title' align=center>新增 $type </h3>";
	$sub = "<input type=submit name=add value='保存'>";
	break;
case 'see':
	$title = "<h3 class='header smaller center title' align=center>查 看 详 细 信 息 </h3>";
	$sqlsee = "SELECT * FROM `sjqm` WHERE id=$_GET[id]";
	$rsee = $DB->query($sqlsee);
	$r = $DB->fetch_assoc($rsee);
	if($_GET['type'] == '试剂' || $r['type']==0)
		$fenzishi = '<tr align=center><td width="80">分子式</td><td>'.$r['fenzi_shi'].'</td><td style="border:none"></td><td></td></tr>';
	else 
		$fenzishi = '';
	disp("sjqm_info");
	die;
	break;
case '删除':
	$sql = "DELETE FROM `sjqm` WHERE id=$_GET[id]";
	$sql2 = "DELETE FROM `sjqm_ls` WHERE `sj_id` = '$_GET[id]'";
	if($DB->query($sql) && $DB->query($sql2))
	echo "<script>alert('删除成功');location.href='sjqm_list.php'</script>";
	break;
case '修改':
	$title = "<h3 class='header smaller center title' align=center>修改详细信息 </h3>";
	$sqlsee = "SELECT * FROM `sjqm` WHERE id=$_GET[id]";
	$rsee = $DB->query($sqlsee);
	$r = $DB->fetch_assoc($rsee);
	if($r['danwei'] == ''){
		$r['danwei']	= '瓶';
	}
	if($_GET['type'] == '试剂' || $r['type']==0){
		$fenzishi = '<tr align=center>
				<td width="80">分子式</td>
				<td colspan=3>
					<input type="text" name="fenzi_shi" id="fenzi_shi" value="'.$r['fenzi_shi'].'" size="63" >
					<br><input type="button" id=\'sub\' value="添加下标" onclick="fenzi_shi.value+=\'<sub></sub>\';cursorInput(\'fenzi_shi\');" />
					<input type="button" id=\'sup\' value="添加上标" onclick="fenzi_shi.value+=\'<sup></sup>\';cursorInput(\'fenzi_shi\');" />
					<input type="button" onclick="setCursorPosition(document.getElementById(\'fenzi_shi\'),\'1000\');" value="光标移到末尾" />
					<input type="button" onclick="show_label()" value="显示分子式" />
				</td></tr>';
	}else{ 
		$fenzishi = '';
	}
	if(!empty($r['gl_user'])){
		$user_select = "<option value='$r[gl_user]' selected>$r[gl_user]</option>";
	}else{
		$user_select = "<option value='$u[userid]' selected>$u[userid]</option>";
	}
	$hidid = "<input type=hidden name=id value=$_GET[id]>";
	$sub = "<input type=submit name=fix value='修改'>";
	break;
case '入库':
	$title = "<h3 class='header smaller center title' align=center>入 库</h3>";
	gotourl("sjqm_rck.php?id=$_GET[id]&action=ru");
	break;
case '出库':
	$title = "<h3 class='header smaller center title' align=center>入 库</h3>";
	gotourl("sjqm_rck.php?id=$_GET[id]&action=chu");
	break;
}
disp("sjqm");
?>
