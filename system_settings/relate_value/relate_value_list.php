<?php
/*
*   关联项目设置
*/

include '../../temp/config.php';
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'关联项目列表','href'=>'./system_settings/relate_value/relate_value_list.php'),
);
$fzx_id	= $u['fzx_id'];

//从数据库 获取 已添加的 关联项目数据/并转换成 行模板
$lines	= '';
$xuhao	= 0;
$relate_value_sql	= $DB->query("SELECT * FROM `n_set` WHERE `fzx_id`='$fzx_id' AND `module_name`='relate_value'");
while ($relate_value_rs = $DB->fetch_assoc($relate_value_sql)) {
	$xuhao++;
	//项目名称
	$value_name_str	= '';
	if(!empty($relate_value_rs['module_value1'])){
		$tmp_vid_ar	= explode(',',$relate_value_rs['module_value1']);
		foreach ($tmp_vid_ar as $value) {
			$value_name_str	.= $_SESSION['assayvalueC'][$value]."，";
		}
		$value_name_str	= substr($value_name_str, 0,-3);
	}
	$panduan_yiju	= $relate_value_rs['module_value2'];
	$note			= $relate_value_rs['module_value3'];
	/*$panduan_yiju	= $note	= '';
	if(!empty($relate_value_rs['module_value2'])){
		$json_arr		= json_decode($relate_value_rs['module_value2']);
		$panduan_yiju	= $json_arr[0];
		$note	= $json_arr[1];
	}*/
	//操作按钮
	$caozuo	= "<button class='btn btn-xs btn-primary value_set value_set' set_id='{$relate_value_rs['id']}'>设置</button>&nbsp;&nbsp;<button class='btn btn-xs btn-primary' onclick=\"if(confirm('删除后无法恢复，确定要删除吗？'))location.href='$rooturl/system_settings/relate_value/relate_value_del.php?set_id={$relate_value_rs['id']}';\">删除</button>";
	//行模板：序号、相关项目、判断依据、备注、操作
	$lines	.= "<tr><td>$xuhao</td><td title='$value_name_str' style='min-width:200px;max-width:330px;'>$value_name_str</td><td title='$panduan_yiju' style='min-width:100px;max-width:200px;'>{$panduan_yiju}</td><td title='$note' style='min-width:100px;max-width:200px;'>{$note}</td><td>$caozuo</td></tr>";
}
//添加新的关联项目的 按钮
$lines	.= '<tr><td colspan="5"><button class="btn btn-xs btn-primary value_set" set_id="">添加关联项目</button></td></tr>';
//vid转换成名称,注意宽度不要太宽，鼠标放上应该显示全部信息

disp("relate_value_list.html");
?>