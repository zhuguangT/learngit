<?php
/*
*功能：ajax修改 报告设置内容
*作者：hanfeng
*时间：2014-08-21
*/
include '../temp/config.php';
if($u['userid'] == ''){
	nologin();
}
//ajax修改 报告信息
if($_POST['ajax']=="1"){
	$zt	= 'no';
	if(!empty($_POST['id']) && !empty($_POST['name']) && !empty($_POST['value'])){
		$result_set_json	= $gx_set_json	= array();
		$sql_set	= '';
		$old_baogao_list	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='{$_POST['id']}' ");
		//获取“报告获取内容参数”信息（站点、项目、时间等）
		if(!empty($old_baogao_list['result_set'])){
			$result_set_json= json_decode($old_baogao_list['result_set'],true);
		}
		//获取“报告个性设置参数”信息（模板、是否显示备注、按照什么排序等）
		if(!empty($old_baogao_list['gx_set'])){
			$gx_set_json	= json_decode($old_baogao_list['gx_set'],true);
		}
		//如果是多维数组里的键值修改，先将键名转换成数组，再进行识别并修改
		if(stristr($_POST['name'],'[') && stristr($_POST['name'],']')){
			$field_name	= str_replace(array('][]','[]','][','[',']'),array("@","@","@","@",''),$_POST['name']);
			$field_arr	= explode('@',$field_name);
		}else{
			$field_arr	= $_POST['name'];
		}
		//更改对应的结果值
		if($_POST['modify_field'] == 'result_set'){
			if($_POST['group_value'] == 'yes'){
				$tmp_value	= @explode(',',trim($_POST['value'],','));
				if($_POST['del'] == 'replace'){//全部信息替换
					$field_arr	= array_filter($field_arr);
					$result_set_json= field_change($result_set_json,$field_arr,$tmp_value,'0',$_POST['del']);//json结果值的更改
				}else{
					foreach ($tmp_value as $key => $value) {
						$result_set_json= field_change($result_set_json,$field_arr,$value,'0',$_POST['del']);//json结果值的更改
					}	
				}
			}else{
				$result_set_json= field_change($result_set_json,$field_arr,$_POST['value'],'0',$_POST['del']);//json结果值的更改
			}
		}else if($_POST['modify_field'] == 'gx_set'){
			if($_POST['group_value'] == 'yes'){
				$tmp_value	= @explode(',',trim($_POST['value'],','));
				if($_POST['del'] == 'replace'){//全部信息替换
					$field_arr	= array_filter($field_arr);
					$gx_set_json= field_change($gx_set_json,$field_arr,$tmp_value,'0',$_POST['del']);//json结果值的更改
				}else{
					foreach ($tmp_value as $key => $value) {
						$gx_set_json= field_change($gx_set_json,$field_arr,$value,'0',$_POST['del']);//json结果值的更改
					}
				}
			}else{
				$gx_set_json	= field_change($gx_set_json,$field_arr,$_POST['value'],'0',$_POST['del']);//json结果值的更改
			}
		}else{
			$sql_set		= " ,`{$_POST['name']}`='{$_POST['value']}' ";
		}
		//将json数组转换成json字符串
		$result_set_str	= JSON($result_set_json);
		$gx_set_str		= JSON($gx_set_json);
		//将修改内容更新到数据库
		switch ($old_baogao_list['count_type']) {
			case '日报':
				$moren_str	= " and `day`='moren' ";
				break;
			case '周报':
				$moren_str	= " and `week`='moren' ";
				break;
			case '月报':
				$moren_str	= " and `month`='moren' ";
				break;
			case '年报':
				$moren_str	= " and `year`='moren' ";
				break;
			default:
				$moren_str	= '';
				break;
		}
		$moren_modify	= '';
		if($moren_str != ''){//同步更改moren配置
			$moren_modify	= " OR (`name_str`='{$old_baogao_list['name_str']}' AND `baogao_name`='{$old_baogao_list['baogao_name']}' AND `count_type`='{$old_baogao_list['count_type']}' {$moren_str}) ";
		}
		$DB->query("UPDATE `baogao_list` SET `result_set`='{$result_set_str}',`gx_set`='{$gx_set_str}'{$sql_set} WHERE `id`='{$old_baogao_list['id']}' {$moren_modify}");
		$zt	= 'yes';
	}else{
		$zt	= 'no';
	}
	echo json_encode(array('zt'=>$zt));
	exit;
}
//根据传进的多维数组，和键名数组，和结果值。将结果值替换到多维数组中对应键名的值
function field_change($value_arr,$field_arr,$field_value,$i=0,$del){
	//如果是个数组就不能赋值，要找到最终的键值的地方再赋值
	if(is_array($field_arr) && !empty($field_arr[$i])){
		$a	= $i;
		$i++;
		$value_arr[$field_arr[$a]]	= field_change($value_arr[$field_arr[$a]],$field_arr,$field_value,$i,$del);
	}else{
		//处理传过来的字符串为 a[b]的情况
		if(count($field_arr)==$i && is_array($field_arr)){//!empty($field_arr[$i]) && 
			if($del	== 'yes'){
				unset($value_arr);//有疑问
			}else{
				$value_arr	= $field_value;
			}
		}else{//处理传过来的字符串为a[b][]的情况
			$key		= @array_search($field_value, $value_arr);
			//删除此元素
			if($del	== 'yes'){//($key === 0 || !empty($key))
				if($i < 1 && !is_array($field_arr)){
					unset($value_arr[$field_arr]);
				}else{
					unset($value_arr[$key]);
				}
			}else{//添加或更改此元素
				if($i < 1 && !is_array($field_arr)){
					$value_arr[$field_arr]	= $field_value;
				}else{
					if(empty($key)){
						$value_arr[]		= $field_value;
					}else{
						$value_arr[$key]	= $field_value;
					}
				}
			}
		}
	}
	return $value_arr;
}
?>
