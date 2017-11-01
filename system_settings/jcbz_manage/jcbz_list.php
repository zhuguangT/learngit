<?php
/**
 * 功能：显示、修改、弃用、启用、增加检测标准列表
 * 作者：zhengsen
 * 时间：2014-03-12
*/
include '../../temp/config.php';

if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'检测标准限值列表','href'=>"$rooturl/system_settings/jcbz_manage/jcbz_list.php");
$_SESSION['daohang']['jcbz_list']	= $trade_global['daohang'];
//启用检测标准
if($_GET['action']=='open' && $_GET['jcbz_bh_id']){   
    //找到父类下面有检测标准是否被启动
	$sql = "SELECT a.id from leixing as a WHERE id = (select b.module_value2 from `n_set` as  b WHERE `id` = '".$_GET['jcbz_bh_id']."')";  

	$module_value = $DB->query($sql);
	$module_value2 =$DB->fetch_assoc($module_value);
	
	$sql = "SELECT id from n_set WHERE module_value2=".$module_value2['id']." and module_value3=1";
	$id = $DB->query($sql);
	$id = $DB->fetch_assoc($id);
	if($id){
		 $DB->query("UPDATE `n_set` SET `module_value3` ='0' WHERE `id` = '".$id['id']."'");
		 $DB->query("UPDATE `n_set` SET `module_value3` ='1' WHERE `id` = '".$_GET['jcbz_bh_id']."'");
	}
	//当父类下面没有检测标准被启动时，直接启用该标准
	$sql = "SELECT id from n_set WHERE module_value2=".$module_value2['id']." and module_value3=0";
	$id = $DB->query($sql);
	$id = $DB->fetch_assoc($id);
	if($id){
		$DB->query("UPDATE `n_set` SET `module_value3` ='1' WHERE `id` = '".$id['id']."' AND `id`= '".$_GET['jcbz_bh_id']."'");
	}
}
/*
*显示检测标准状态的下拉框
*/
if(!isset($_GET['jcbz_zt'])){
	$_GET['jcbz_zt']='1';
}
$zt_arr=array('1'=>'正在使用','0'=>'已弃用');
foreach($zt_arr as $key=>$value){
	if($_GET['jcbz_zt']==$key){
		$jcbz_zt_select.='<option value="'.$key.'" selected="selected">'.$value.'</option>';
	}else{
		$jcbz_zt_select.='<option value="'.$key.'" >'.$value.'</option>';
	}
}

$hub_rs=$DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE id='".$fzx_id."'");
//显示弃用或者启用按钮
if($hub_rs['parent_id']=='0'){
$change_zt = ($_GET['jcbz_zt']==0) ? "<button onclick='return diag(this)' value='open' class='but'>点击启用</button>" : '';
}

$jcbz_list	 = '';
$jcbz_list_sql="SELECT * FROM `n_set` WHERE module_name='jcbz_bh' AND module_value3='".$_GET['jcbz_zt']."' ";
$jcbz_list_query=$DB->query($jcbz_list_sql);
while($jcbz_list_rs=$DB->fetch_assoc($jcbz_list_query)){
	if(empty($_GET['jcbz'])){
		$_GET['jcbz']=$jcbz_list_rs['id'];
		$water_type	 =$jcbz_list_rs['module_value2'];
	}
	if($_GET['jcbz']==$jcbz_list_rs['id']){
		$select		= 'selected="selectd"';
		$water_type	= $jcbz_list_rs['module_value2'];
	}else{
		$select		= '';
	}
	$jcbz_list.='<option value='.$jcbz_list_rs['id'].' '.$select.'>'.$jcbz_list_rs['module_value1'].'</option>';
}
$value_group_list = '';
$jcbz_bh_arr = array();
$value_group_sql="SELECT av.fenlei FROM `assay_value` av JOIN `xmfa` x ON av.id=x.xmid WHERE x.act='1' AND x.fzx_id='".$fzx_id."' GROUP BY av.fenlei ORDER BY av.seq,av.value_C";
$value_group_query=$DB->query($value_group_sql);
while($value_group_rs=$DB->fetch_assoc($value_group_query)){
	if(empty($value_group_rs['fenlei'])){
		$value_group_rs['fenlei']='未分类';
	}
	$select=($_GET['quanbu']==$value_group_rs['fenlei'])?'selected="selected"':'';
	$value_group_list.='<option value='.$value_group_rs['fenlei'].' '.$select.'>'.$value_group_rs['fenlei'].'</option>';
}

//搜索
$search = '';
$sql = "SELECT value_C from assay_jcbz WHERE jcbz_bh_id='".$_GET['jcbz']."' AND value_C!='' GROUP BY value_C";
$r = $DB->query($sql); 
while ($a = $DB->fetch_assoc($r)) {
	$select = ($_GET['search']==$a['value_C']) ? 'selected="selected"' : '';
	$search.= '<option value="'.$a['value_C'].'" '.$select.'>'.$a['value_C'].'</option>';
}
$sql_WHERE	= '';
$sql_WHERE .= empty($_GET['search']) ? '' : " AND aj.value_C = '{$_GET['search']}'";
if(!empty($_GET['quanbu'])){
	$_GET['quanbu'] = ($_GET['quanbu'] == '未分类') ? '' : $_GET['quanbu'];
	$sql_WHERE .=  " AND av.fenlei = '{$_GET['quanbu']}'";
}
//地表水标准用 标准文件的格式显示（先改地表水）
if($water_type == '1'){
	$jcbz_arr	= array();
	$value_C_arr= array();
	$sql	= $DB->query("SELECT n.module_value4,av.fenlei,aj.* FROM `n_set` n left JOIN `assay_jcbz` aj  ON n.id =aj.jcbz_bh_id
		JOIN `assay_value` av ON  aj.vid =av.id WHERE  n.module_value2='1' AND n.module_value4 !='' $sql_WHERE ORDER BY aj.id");
	while ($rs	= $DB->fetch_assoc($sql)) {
		$value_C_arr[$rs['vid']]	= $rs['value_C'];
		switch ($rs['module_value4']) {
			case 'Ⅰ类':
				$jcbz_i	= 1;
				break;
			case 'Ⅱ类':
				$jcbz_i	= 2;
				break;
			case 'Ⅲ类':
				$jcbz_i	= 3;
				break;
			case 'Ⅳ类':
				$jcbz_i	= 4;
				break;
			case 'Ⅴ类':
				$jcbz_i	= 5;
				break;
			default:
				$jcbz_i	= 'wu';
				break;
		}
		$jcbz_arr[$jcbz_i][$rs['vid']]['xz']	= $rs['xz'];
		$jcbz_arr[$jcbz_i][$rs['vid']]['unit']	= $rs['unit'];
	}
	$i	= 0;
	foreach ($value_C_arr as $key => $value) {
		$i++;
		if(in_array($key,array('97','99'))|| (empty($jcbz_arr['1'][$key]['xz']) && !empty($jcbz_arr['3'][$key]['xz']))){// 
			foreach (array('1','2','3','4','5','wu') as  $value_i) {
				if(!empty($jcbz_arr[$value_i][$key]['xz'])){
					$value_jcbz	= $jcbz_arr[$value_i][$key]['xz'];
					continue;
				}
			}
			$xm_list	.= "<tr><td>{$i}</td><td>{$value}</td><td colspan='6'>{$value_jcbz}</td></tr>";
		}else{
			$xm_list	.= "<tr><td>{$i}</td><td>{$value}</td><td>{$jcbz_arr[1][$key]['xz']}</td><td>{$jcbz_arr[2][$key]['xz']}</td><td>{$jcbz_arr[3][$key]['xz']}</td><td>{$jcbz_arr[4][$key]['xz']}</td><td>{$jcbz_arr[5][$key]['xz']}</td></tr>";
		}
		
	}
	disp("jcbz_list2");
	exit;
}
//查询检测标准数据
/*$sql="SELECT av.fenlei,aj.* FROM `n_set` n JOIN `assay_jcbz` aj  ON n.id =aj.jcbz_bh_id
		 LEFT JOIN `assay_value` av ON  aj.vid =av.id LEFT JOIN `xmfa` x ON av.id=x.xmid WHERE n.id = '".$_GET['jcbz']."'
		$sql_WHERE AND x.act='1' AND x.fzx_id='".$fzx_id."' ORDER BY av.id";*/
$sql="SELECT av.fenlei,aj.* FROM `n_set` n JOIN `assay_jcbz` aj  ON n.id =aj.jcbz_bh_id
		 LEFT JOIN `assay_value` av ON  aj.vid =av.id  WHERE n.id = '".$_GET['jcbz']."'
		$sql_WHERE  ORDER BY av.id";
$r = $DB->query($sql);
$data_list = $fenlei_arr = array();
while ($a = $DB->fetch_assoc($r)){
	$a['fenlei'] = empty($a['fenlei']) ? '未分类':$a['fenlei'];
	$data_list[$a['fenlei']][$a['value_C']] =$a; 
}

if(!empty($data_list)){
	foreach ($data_list as $key => $a) {
		$nums=count($a);
		$i=1;
			foreach($a as $k=>$v){
				if($i==1){
					$xm_list .= '<tr align=center class="xm" >
							  <td rowspan='.$nums.' style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070">'.$key.'</td>
							  <td height="18" >'.$k.'</td>
							  <td height="18" >'.$v['xz'].'</td>
							  <td height="18" >'.$v['dw'].'</td>
							  </tr>';
				}else{
					$xm_list .= '<tr align=center class="xm" >
							  <td height="18" >'.$k.'</td>
							  <td height="18" >'.$v['xz'].'</td>
							  <td height="18" >'.$v['dw'].'</td>
							  </tr>';
				}
				$i++;
			}
	}
}
disp("jcbz_list");
