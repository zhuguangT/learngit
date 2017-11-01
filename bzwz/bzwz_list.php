<?php
/**
 * 功能：标准溶液，标准样品列表页面
 * 作者: Mr Zhou
 * 日期: 2014-10-17
 * 描述: 标准溶液，标准样品管理
*/
include ('../temp/config.php');
$fzx_id = FZX_ID;
$wz_status=array('全部','有效','已失效');
if(''==$_GET['_wz_type'])	$_GET['_wz_type']='有效';
if(''==$_GET['wz_type'])	$_GET['wz_type']='标准溶液';
if(!empty($_GET['manufacturer']) && $_GET['manufacturer'] != '全部'){
	$manufacturer_select = "<option value='{$_GET['manufacturer']}' selected>{$_GET['manufacturer']}</option>";
	$manufacturer_sql = " AND `manufacturer` = '{$_GET['manufacturer']}' ";
}else{
	$manufacturer_select = $manufacturer_sql = '';
}
$wz_type_subdivide = empty($_GET['wz_type_subdivide']) ? '全部' : $_GET['wz_type_subdivide'];
//详细分类
if($wz_type_subdivide=='全部'){
	$wz_type_subdivide_sql = '';
	//显示全部类别的时候需要显示每个物品的细分类别
	$wz_type_subdivide_title = "<th style='width:10%;'>类别</th>";
}else{
	$wz_type_subdivide_sql = " AND `wz_type_subdivide` = '$wz_type_subdivide'";
	$wz_type_subdivide_title = '';
}
if($_GET['wz_type']=='标准溶液'){
	$tabs = '#tabs-1';
	$bzwz_content = 'tabs_1_bzry';
}else{
	$tabs = '#tabs-2';
	$bzwz_content = 'tabs_2_bzyp';
}
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$current_url.$tabs));
foreach ($wz_status as $i => $value) {
	$selected = ($value==$_GET['_wz_type']) ? 'selected':'';
	$_wz_types.='<option '.$selected.' value="'.$value.'">'.$value.'</option>';
}
$wz_type_="AND `wz_type`='{$_GET['wz_type']}'";
//物质名称
if('' == $_GET['wz_name'] || '全部' == $_GET['wz_name']){
	// $wz_name = '全部';
	$_wz_name='';
}else{
	$wz_name = $_GET['wz_name'];
	$_wz_name="AND `wz_name`='{$_GET['wz_name']}'";
}
if(!empty($_GET['wz_name']) && $_GET['wz_name']!='全部'){
	$wz_name_selected = "<option selected>$_GET[wz_name]</option>";
}else{
	$wz_name_selected = "";
}
//显示详细分类默认选中项
if(!empty($_GET['wz_type_subdivide'])){
	$wz_type_subdivide_selected = "<option value='$_GET[wz_type_subdivide]' selected>$_GET[wz_type_subdivide]</option>";
}else{
	$wz_type_subdivide_selected = '';
}
//物质状态 是否有效
switch($_GET['_wz_type']){
	case "有效":
		$__wz_type="AND `time_limit`>curdate()";
		break;
	case "已失效":
		$__wz_type="AND `time_limit`<=curdate()";
		break;
	case "全部":
		$__wz_type='';
		break;
}
$wz_type = $_GET['wz_type'];
// 得到 名称 详细分类 的下拉菜单
$sql="SELECT distinct `wz_name` , `wz_type_subdivide` , `manufacturer` FROM  `bzwz` WHERE `fzx_id`='$fzx_id' $wz_type_ $__wz_type ORDER BY CONVERT( `wz_name` USING gbk ),`time_limit` ASC";
$query=$DB->query($sql);
while($row=$DB->fetch_assoc($query)){
	$manufacturer_arr[] = $row['manufacturer'];
	$ryline.='<option value="'.$row['wz_name'].'">'.$row['wz_name'].'</option>';
	if(!empty($row['wz_type_subdivide']) && !@in_array($row['wz_type_subdivide'],$subdivide_name_arr)){
		$wz_type_subdivide_line .='<option value="'.$row['wz_type_subdivide'].'">'.$row['wz_type_subdivide'].'</option>';
		$subdivide_name_arr[] = $row['wz_type_subdivide'];
	}
}

$manufacturer_arr = array_unique($manufacturer_arr);
foreach($manufacturer_arr as $key => $value){
	$manufacturer_select .= "<option value='{$value}'>{$value}</option>";
}

//========================================================================================================================================================================================//

$sql = "SELECT bz.*,xuhao,bd.id as bdid FROM `bzwz` as bz left join bzwz_detail as bd on bz.id =bd.wz_id WHERE `fzx_id`='$fzx_id' $wz_type_ $__wz_type $_wz_name $wz_type_subdivide_sql $manufacturer_sql AND `wz_status` = '0' ORDER BY CONVERT( `wz_type` USING gbk ),  xuhao ASC ,CONVERT( `wz_name` USING gbk ),`time_limit` ASC";
$query=$DB->query($sql);
$xuhao = $i	= 0;
//向后推1个月，如果有效期小于 提示日期 那么就将有效期变为红色
$promit_date = date('Y-m-d' , strtotime("now +30 days"));
// print_rr($promit_date);
while($r=$DB->fetch_assoc($query)){
	$files = '';
	// print_rr($r);
	$del = '<a href="bzwz.php?action=删除&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'" onclick="if(confirm(\'确定要删除吗？\'))return true;else return false;">删除</a>';
	$edi = '<a href="bzwz.php?action=修改&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'">修改</a>';
	$ruk = '<a href="bzwz.php?action=入库&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'&wz_name='.$r['wz_name'].'">入库</a>';
	$chu = '<a href="bzwz.php?action=出库&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'&wz_name='.$r['wz_name'].'">出库</a>';
	$kan = '<a href="bzwz.php?action=查看&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'">查看详情</a>';
	$operation=(!$u['bzwz_manage']) ? $kan : $ruk.' | '.$chu.' | '.$edi.' | '.$del;
	//显示全部类别的时候需要显示每个物品的细分类别
	if($wz_type_subdivide=='全部'){
		$wz_type_subdivide_lines = "<td style='min-wide:70px;'>{$r['wz_type_subdivide']}</td>";
	}else{
		$wz_type_subdivide_lines = '';
	}
	if(!empty($r['dilution_method_file']) && $r['dilution_method_file'] != '[]'){
            $file_new_name_arr = json_decode($r['dilution_method_file'], true);
            $file_old_name_arr = json_decode($r['dilution_method'], true);
            foreach($file_new_name_arr as $key=>$value){
                        $files.="<a href='./upfile/{$value}' target=_blank;>{$file_old_name_arr[$key]}</a><span class='glyphicon glyphicon-remove red' style='cursor:pointer;margin-left:10px;' onclick=delete_file(this,'{$value}','{$key}','{$r['id']}');></span>";
                    }
        }else{
        	$files = '';
        }
//增加有效期提示
	if(!empty($r['time_limit'])){
		if($r['time_limit'] <= $promit_date){
			$color_time_limit = "color:red;";
		}else{
			$color_time_limit = '';
		}
	}else{
		$color_time_limit = '';
	}
//增加提示数量的提示
	if(!empty($r['limit_num'])){
		if($r['limit_num'] >= $r['amount']){
			$color_amount_limit = "color:red";
		}else{
			$color_amount_limit = '';
		}
	}else{
		$color_amount_limit = '';
	}
$i++;
	if($_GET['_wz_type']=='有效')
	{	
		if( $r['amount']>0){
			$xuhao++;
			$lines.=temp('bzwz/bzwz_list_line');
		}
	}
	else{
		$xuhao++;
		$lines.=temp('bzwz/bzwz_list_line');
	}
}
$yearOption = '<option value="'.date('Y').'" selected>'.date('Y').'</option>';
if($_GET['wz_type']=='标准溶液'){
	$$bzwz_content = temp('bzwz/bzwz_list_content');
}else{
	$$bzwz_content = temp('bzwz/bzwz_list_content');
}
disp('bzwz/bzwz_list');
?>
