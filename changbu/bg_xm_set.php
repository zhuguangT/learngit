<?php
/*
*功能：检测报告项目设置
*作者：zhengsen
*时间：2016-02-19
*/
include '../temp/config.php';
$fzx_id	= FZX_ID;//中心
$water_type = $_POST['water_type'];//水样类型
//读取厂部默认的项目
$xminf = $DB->fetch_one_assoc("SELECT `module_value1` FROM `n_set` WHERE `module_value2`='{$water_type}' AND `module_name` = 'cbxm_id'");
$moren_value_arr= @explode(',', $xminf['module_value1']);
$value_checked	= $value_checkbox = $value_options = '';
$checked_num	= $checkbox_num	  = $checked_value_num 	 = 0;
$fenlei_arr	= array();
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `assay_value` AS xm  WHERE 1  ORDER BY xm.fenlei");
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
	//已经选中的项目
	if(empty($rs_xcjc_value['fenlei'])){
		$rs_xcjc_value['fenlei']	= '未分类';
	}
	if(!in_array($rs_xcjc_value['fenlei'],$fenlei_arr)){//根据项目分类显示项目
		$fenlei_arr[]	 = $rs_xcjc_value['fenlei'];
		$fenlei_num	 = count($fenlei_arr);
	}
	$value_options  .= "<option value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</option>";
	//根据条件默认选中项目
	if(in_array($rs_xcjc_value['id'],$moren_value_arr)){
		$checked_value_num++;
		if($checked_num<$fenlei_num){
			$value_checked  .= "<div class='checked_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
			$checked_num     = $fenlei_num;
		}
		$value_checked	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}' checked='true' />{$rs_xcjc_value['value_C']}</label>";
	}else{
		if($checkbox_num<$fenlei_num){
			$value_checkbox .= "<div class='checkbox_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
			$checkbox_num    = $fenlei_num;
		}
		$value_checkbox	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}'  />{$rs_xcjc_value['value_C']}</label>";
	}
}

#######显示界面
if($checked_value_num==0){//如果一个选中的项目都没有，就直接显示成全屏选项目的格式
	$lines	= "<div id='checkbox' style='overflow:hidden'>
			<p style='background-color:#FFCC99;'>
				请选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选' />
				<input type='button' class='all_check' value='反选' />
			</p>
			$value_checkbox
			<div class='fixed' id='checkbox_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
		</div>";
}else if($value_checkbox==''){
	$lines  = "<div id='checked' style='width:100%;float:left;border:1px #56932C solid;overflow:hidden'>
                        <p style='background-color:#90CA1F;'>
                                目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选' />
				<input type='button' class='all_checked' value='反选' />
                        </p>
                        <div class='fixed' id='checked_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
                        $value_checked
                </div>";
}else{//已选项目和未选项目分屏显示
	$lines	= "<div id='checked' style='width:50%;float:left;border:1px #56932C solid;overflow:hidden'>
			<p style='background-color:#90CA1F;'>
				目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选' />
				<input type='button' class='all_checked' value='反选' />
			</p>
			<div class='fixed' id='checked_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checked
		</div>
		<div id='checkbox' style='width:50%;float:left;border:1px #FFCC99 solid;overflow:hidden'>
			<p style='background-color:#FFCC99;'>
				还可以选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选' />
				<input type='button' class='all_check' value='反选' />
			</p>
			<div class='fixed' id='checkbox_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checkbox
		</div>";
}
disp('changbu/bg_xm_list');
?>