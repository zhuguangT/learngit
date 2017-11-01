<?php
/*
*功能：月统计报告查看页面
*作者：zhengsen
*时间：2015-05-15
*系统：兰州
*/
include '../temp/config.php';

if($u['userid'] == ''){
	nologin();
}
$fzx_id	= $u['fzx_id'];
//获取年份
$year	= $_GET['year'];
if(empty($year)){
	$year	= date('Y');
}
//获得月份
$month	= $_GET['month'];
if(empty($month)){
	$month	= date('m');
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'常规月报','href'=>"./data_statistics/tjbg_month.php?year={$year}&month={$month}");
$_SESSION['daohang']['tjbg_month']	= $trade_global['daohang'];
$trade_global['js']	= array("fuelux/fuelux.spinner.min.js");
//ajax添加报告
if($_POST['action'] == 'ajax_add_bg'){
	//判断为空
	if(!empty($_POST['bg_name']) && !empty($_POST['bg_id']) && !empty($_POST['bg_px'])){
		//判断重复
		$old_bg_name	= $DB->fetch_one_assoc("SELECT `id` FROM `baogao_list` WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$_POST['bg_name']}'");
		if(!empty($old_bg_name)){
			echo "该名称已存在，请勿重复添加！";
		}else{
			//根据排序更新所有记录
			//$px_i	= 0;
			$old_bg_sql	= $DB->query("SELECT `px`,`baogao_name` FROM `baogao_list` WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `month`='moren' ORDER BY `px`*1");
			while ($old_bg_rs = $DB->fetch_assoc($old_bg_sql)) {
				/*
				//对报告列表重新排序
				$px_i++;
				$DB->query("UPDATE `baogao_list` SET `PX`='{$px_i}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$old_bg_rs['baogao_name']}'");*/
				if($old_bg_rs['px'] >= $_POST['bg_px']){
					$old_bg_rs['px']++;
					$DB->query("UPDATE `baogao_list` SET `px`='{$old_bg_rs['px']}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$old_bg_rs['baogao_name']}'");
				}
			}
			//获取模板的全部信息
			$muban_bg	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='{$_POST['bg_id']}'");
			//插入新的记录，只插默认记录即可，一刷新页面本月就有了
			$insert_set	= array();
			foreach ($muban_bg as $key => $value) {
				if(in_array($key,array('id','status','modify_time'))){
					continue;
				}else if($key == 'px'){
					$value	= $_POST['bg_px'];
				}else if($key == 'month'){
					$value	= 'moren';
				}else if($key == 'baogao_name'){
					$value	= $_POST['bg_name'];
				}
				$insert_set[]	= "`{$key}`='{$value}'";
			}
			$insert_set	= implode(',',$insert_set);
			$DB->query("INSERT INTO `baogao_list` SET $insert_set ");
			//返回是否成功的信息
			if($DB->affected_rows()){
				echo "yes";
			}else{
				echo "添加失败，请刷新页面重试！";
			}
		}
	}else{
		echo "未获取到类型名称，请刷新页面重试！";
	}
	exit;
}else if($_POST['action'] == 'ajax_modify_bg'){
	if(!empty($_POST['mdi_id']) && !empty($_POST['mdi_name']) && !empty($_POST['mdi_val'])){
		//取出修改前的信息
		$sql_old	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='{$_POST['mdi_id']}'");
		if(!empty($sql_old[$_POST['mdi_name']])){
			if($sql_old[$_POST['mdi_name']] == $_POST['mdi_val']){
				//echo "未修改任何信息";
				echo "yes";
			}else{
				switch ($_POST['mdi_name']) {
					case 'baogao_name'://重名验证
						$old_bg_name	= $DB->fetch_one_assoc("SELECT `id` FROM `baogao_list` WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$_POST['mdi_val']}'");
						if(!empty($old_bg_name)){
							echo "该名称已存在，请勿重复添加！";
							exit;
						}
						break;
					case 'px':
						//$px_i	= 0;
						$old_bg_sql	= $DB->query("SELECT `px`,`baogao_name` FROM `baogao_list` WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `month`='moren' ORDER BY `px`*1");
						while ($old_bg_rs = $DB->fetch_assoc($old_bg_sql)) {
							/*//对报告列表重新排序
							$px_i++;
							$DB->query("UPDATE `baogao_list` SET `px`='{$px_i}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$old_bg_rs['baogao_name']}'");*/
							//根据修改信息重新排列顺序
							$modify_zt	= 'no';
							if($sql_old[$_POST['mdi_name']] > $_POST['mdi_val'] && $old_bg_rs['px'] >= $_POST['mdi_val'] && $old_bg_rs['px'] <= $sql_old[$_POST['mdi_name']]){
								$old_bg_rs['px']++;
								$modify_zt	= 'yes';
							}else if($sql_old[$_POST['mdi_name']] < $_POST['mdi_val'] && $old_bg_rs['px'] <= $_POST['mdi_val'] && $old_bg_rs['px'] >= $sql_old[$_POST['mdi_name']]){
								$old_bg_rs['px']--;
								$modify_zt	= 'yes';
							}
							//更改对应的排序
							if($modify_zt == 'yes'){
								$DB->query("UPDATE `baogao_list` SET `px`='{$old_bg_rs['px']}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$old_bg_rs['baogao_name']}'");
							}
						}
						break;
					default:
						# code...
						break;
				}
				//更改相应的内容
				$DB->query("UPDATE `baogao_list` SET `{$_POST['mdi_name']}`='{$_POST['mdi_val']}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='month_bg' AND `baogao_name`='{$sql_old['baogao_name']}'");
				if($DB->affected_rows()){
					echo "yes";
				}
			}
		}else{
			echo "要修改的内容不存在！";
		}
	}else{
		echo "修改失败，请刷新页面重试";
	}
	exit;
}
//查询出月报统计表的模板信息
$tjbg_month_line= $option_list = '';
$xuhao	= '';
$month_bg_arr	= array();
$sql_baogao_list= $DB->query("SELECT * FROM `baogao_list` WHERE `fzx_id`='".$fzx_id."' AND `name_str`='month_bg' AND ((`year`='{$year}' AND `month`='{$month}') OR `month`='moren') ORDER BY `px`,`month`");
while($rs_baogao_list = $DB->fetch_assoc($sql_baogao_list)){
	//sql的month排序上是先正常月份后moren所以当读到moren却还没有正常月份的记录时，就说明没有正常月份的记录
	if($rs_baogao_list['month'] == 'moren'){
		if(!in_array($rs_baogao_list['baogao_name'],$month_bg_arr)){
			//新插入一条记录
			$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='$fzx_id',`px`='{$rs_baogao_list['px']}',`name_str`='month_bg',`baogao_name`='{$rs_baogao_list['baogao_name']}',`count_type`='{$rs_baogao_list['count_type']}',`year`='$year',`month`='$month',`result_set`='{$rs_baogao_list['result_set']}',`gx_set`='{$rs_baogao_list['gx_set']}'");
			$rs_baogao_list['id']	= $DB->insert_id();
			$rs_baogao_list['month']= $month;
		}else{
			//默认配置的记录不需要显示，只显示本月的记录即可
			continue;//去掉重名报告
		}
	}else{
		//如果本月有配置，就记录下来
		if(@in_array($rs_baogao_list['baogao_name'],$month_bg_arr)){
			continue;
		}else{
			$month_bg_arr[]	= $rs_baogao_list['baogao_name'];
		}
	}
	$gx_set_json	= array();
	if(!empty($rs_baogao_list['gx_set'])){
		$gx_set_json= json_decode($rs_baogao_list['gx_set'],true);
	}
	if(empty($gx_set_json['set_mb_name'])){//管网合格率报表的模板
		if($rs_baogao_list['name_str']=='month_bg' && stristr($rs_baogao_list['baogao_name'],'管网合格率')){
			$gx_set_json['set_mb_name']	= "month_bg_gw_hgl";
			$gx_set_json['result_mb_name']     = "month_bg_gw_hgl";
			$gx_set_json_str	= JSON($gx_set_json);
			$DB->query("UPDATE `baogao_list` SET `gx_set`='{$gx_set_json_str}' WHERE `id`='{$cg_rs['id']}'");
		}
	}
	//判断报告是否审核，如果审核就不能再修改设置了。
	$can_click	= '';
	if($rs_baogao_list['status']){
		$can_click	= ' onclick="alert(\'报告已签发,不允许再修改\');return false;" ';
	}
	//显示行信息
	if(!empty($gx_set_json['set_mb_name'])){
		$link_php	= $gx_set_json['set_mb_name'].'.php';
	}else{
		$link_php	= 'result_set.php';
	}
	$xuhao++;
	//校正报表排序
	if($rs_baogao_list['px'] != $xuhao){
		$DB->query("UPDATE `baogao_list` SET `px`='{$xuhao}' WHERE `fzx_id`='{$fzx_id}' AND `name_str`='{$rs_baogao_list['name_str']}' AND `baogao_name`='{$rs_baogao_list['baogao_name']}'");
	}
	$option_list	.= "<option value='{$rs_baogao_list[id]}' old_name='{$rs_baogao_list['baogao_name']}'>（{$xuhao}）{$rs_baogao_list['baogao_name']}</option>";
	$tjbg_month_line	.= "<tr>
								<td class='modify_td'><span  class='{$rs_baogao_list[id]}'>{$xuhao}</span><input type='hidden' name='px' class='{$rs_baogao_list[id]}' value='{$xuhao}' placeholder='不能为空!'  style='width:100%'  /></td>
								<td class='modify_td'><span class='{$rs_baogao_list[id]}'>{$rs_baogao_list['baogao_name']}</span><input type='hidden' name='baogao_name' class='{$rs_baogao_list[id]}' value='".$rs_baogao_list['baogao_name']."' style='width:100%' placeholder='清空不修改!' /></td>
								<td>".$year."年".$month."月</td>
								<td>-/-</td>
								<td nowrap>
									<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs_baogao_list[id]&year={$year}&month={$month}&action=see&bg=yuebao\">查看</a>&nbsp;
									<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs_baogao_list[id]&year={$year}&month={$month}&action=xia&bg=yuebao\">下载</a>&nbsp;
									<a class=\"btn btn-xs btn-primary\" target=\"main\" href=\"$link_php?type=月报&set_id=$rs_baogao_list[id]&action={$rs_baogao_list[name_str]}\" $can_click>设置</a>
								</td>
							</tr>";
}
$moren_px	= $xuhao+1;//新添加报告时的默认排序
//年份下拉列表的显示
$now_year=date('Y');
$year_option='';
for($begin_year;$begin_year<=$now_year;$begin_year++){
	if($year==$begin_year){
		$year_option.="<option value=".$begin_year." selected=\"selected\">".$begin_year."</option>";
	}else{
		$year_option.="<option value=".$begin_year." >".$begin_year."</option>";
	}
}
//月份下拉列表的显示
$month_option='';
if($year<date('Y')){
	$max_month=12;
}else{
	$max_month=date('m');
}
for($m=1;$m<=$max_month;$m++){
	if($m<10){
		$m='0'.$m;
	}
	if($month==$m){
		$month_option.="<option value=".$m." selected=\"selected\">".$m."</option>";
	}else{
		$month_option.="<option value=".$m.">".$m."</option>";
	}
}
disp("bg/tjbg_month");
?>
