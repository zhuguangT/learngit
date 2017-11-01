<?php
/**
 * 功能：化验项目列表页面
 * 作者：韩枫 张登胜
 * 日期：2014-03-17
 * 描述：fzx_id 分中心Id
*/
include("../../temp/config.php");
//导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'系统维护','href'=>'system_settings/assay_value/assay_value_list.php'),
        array('icon'=>'','html'=>'化验项目管理','href'=>'system_settings/assay_value/assay_value_list.php')
);
$trade_global['daohang'] = $daohang;
###先判断是否有“化验项目管理”权限
/*if ( !$u['user_manage'] )
    exit( '对不起, 你权限不够' );*/
//分中心id  先手动赋值
$fzx_id     = '1';
$breadcrumb = 'ff';
if($_GET['actValue']!=''){
	$actValue = $_GET['actValue'];//“1”代表：只显示本单位化验的项目
}else{
	$actValue = '1';
}
if(!empty($_GET['morenUserid'])){
	$morenUserid = $_GET['morenUserid'];//页面选择的 默认化验员
}else{
	$morenUserid = '全部';
}
if(!empty($_GET['morenXm'])){
	$morenXm = $_GET['morenXm'];//页面选择的 默认化验员
}else{
	$morenXm = '全部';
}
if(!empty($_GET['morenFenlei'])){
	$morenFenlei = $_GET['morenFenlei'];//页面选择的项目分类
}else{
	$morenFenlei = '全部';
}
$fenLeiArr = $linesArr = $jcbzArr = $useridArr  = array();//获取出assay_value表中已有的分类
$lines     = $where    = $htmlOnlyAct = $fenleiOption = $valueOption = $userOption = '';
$actCount  = $allCount = 0;
############取出标准表(assay_jcbz)中项目名和水样类型的名称
$sql_assay_jcbz = $DB->query("SELECT aj.*,lx.lname FROM `assay_jcbz` AS aj INNER JOIN `n_set` ON aj.jcbz_bh_id=n_set.id INNER JOIN `leixing` AS lx ON n_set.module_value2=lx.id WHERE lx.fzx_id='".$fzx_id."' AND n_set.module_value3='1' AND n_set.module_name='jcbz_bh'");
while($rs_array_jcbz = $DB->fetch_assoc($sql_assay_jcbz)){
	if(!empty($rs_array_jcbz['value_C'])){
		$jcbzArr[$rs_array_jcbz['vid']][$rs_array_jcbz['lname']] = $rs_array_jcbz['value_C'];
		$jcbzArr[$rs_array_jcbz['vid']]['html'] .= $rs_array_jcbz['lname'].":".$rs_array_jcbz['value_C']."<br>";
	}
}
############取出本中心(fzx_id)所有assay_value表的数据
$sql_assay_value = $DB->query("SELECT * FROM `assay_value`  WHERE fzx_id='".$fzx_id."' ORDER BY seq,id");
$allCount        = $DB->num_rows($sql_assay_value);//获取出本单位所有化验项目的数量
$sqll_assay_value = $DB->query("SELECT * FROM `assay_value` WHERE fzx_id='".$fzx_id."' and act='1' ORDER BY seq,id");
$actCount        = $DB->num_rows($sqll_assay_value);//获取出本单位本中心化验的项目的数量

if($actValue=='1'){//只获取本中心化验的项目
	$where          .= " and act='1'";
	$where1         .= " and act='1'";
	$sql_assay_value = $DB->query("SELECT * FROM `assay_value` WHERE fzx_id='".$fzx_id."' ".$where." ORDER BY seq,id");
	$actCount        = $DB->num_rows($sql_assay_value);
	if($actCount<=10)$where = '';
	$htmlOnlyAct     = "checked";
}
if($morenUserid!='全部'&&$morenFenlei!='未知'){//获取某化验员化验的项目
	$where  .= " and userid='".$morenUserid."'";
}
if($morenUserid=='未知'){//获取某化验员化验的项目
	$where  .= " and userid=''";echo $morenUserid;
}
if($morenXm!='全部'){//获取某化验员化验的项目
	$where .= " and av.id='".$morenXm."'";
}
if($morenFenlei!='全部'&&$morenFenlei!='未分类'){
	$where  .= " and xm.fenlei='".$morenFenlei."'";
	$where1 .= " and xm.fenlei='".$morenFenlei."'";
}
if($morenFenlei=='未分类'){
	$where  .= " and xm.fenlei=''";
	$where1 .= " and xm.fenlei=''";
}

############取出项目分类
$sqll="SELECT xm.fenlei AS fenlei FROM `assay_value` AS av RIGHT JOIN `xm` ON av.vid=xm.id WHERE fzx_id='".$fzx_id."'ORDER BY seq,av.id";
$sqll_assay_value = $DB->query($sqll);
while($rsl_assay_value = $DB->fetch_assoc($sqll_assay_value)){
	if(empty($rsl_assay_value['fenlei'])){
		$rsl_assay_value['fenlei'] = "未分类";
	}
	if(!in_array($rsl_assay_value['fenlei'],$fenLeiArr)&& !empty($rsl_assay_value['fenlei'])){
		$fenLeiArr[]   = $rsl_assay_value['fenlei'];//获取出assay_value表中已有的分类
		if($morenFenlei!='全部' && $morenFenlei==$rsl_assay_value['fenlei']){
			$fenleiOption .= "<option value='".$rsl_assay_value['fenlei']."' selected>".$rsl_assay_value['fenlei']."</option>";
		}else{
			$fenleiOption .= "<option value='".$rsl_assay_value['fenlei']."'>".$rsl_assay_value['fenlei']."</option>";
		}
	}
}
############取出化验员
$sqel="SELECT DISTINCT userid FROM `assay_value` AS av JOIN `xm` ON av.vid=xm.id WHERE fzx_id='".$fzx_id."' ".$where1." ORDER BY seq,av.id";
$sqel_assay_value = $DB->query($sqel);
while($res_assay_value = $DB->fetch_assoc($sqel_assay_value)){
	if(empty($res_assay_value['userid'])){
		$res_assay_value['userid'] = "未知";
	}
	if($morenUserid!='全部' && $morenUserid==$res_assay_value['userid']){
		echo $resl_assay_value['userid'];
			$userOption .= "<option value='".$morenUserid."' selected>".$morenUserid."</option>";
	}else{
			$userOption .= "<option value='".$res_assay_value['userid']."'>".$res_assay_value['userid']."</option>";
	}
}
$sqexl="SELECT DISTINCT userid FROM `assay_value` AS av JOIN `xm` ON av.vid=xm.id WHERE fzx_id='".$fzx_id."' ORDER BY seq,av.id";
$sqexl_assay_value = $DB->query($sqexl);
while($resx_assay_value = $DB->fetch_assoc($sqexl_assay_value)){
	if(empty($resx_assay_value['userid'])){
		$resx_assay_value['userid'] = "未知";
	}
	$userOption1 .= "<option value='".$resx_assay_value['userid']."'>".$resx_assay_value['userid']."</option>";
}
#################检索联动
$sql="SELECT * FROM `assay_value` AS av JOIN `xm` ON av.vid=xm.id WHERE fzx_id='".$fzx_id."' ".$where." ORDER BY seq,av.id";
$sql_assay_value = $DB->query($sql);
while($rs_assay_value = $DB->fetch_assoc($sql_assay_value)){
	if(empty($rs_assay_value['fenlei'])){
		$rs_assay_value['fenlei'] = "未分类";
	}
	if($morenXm!='全部' && $morenXm==$rsl_assay_value['id']){
		$valueOption .= "<option value='".$rs_assay_value['id']."' fenlei='".$rs_assay_value['fenlei']."' selected>".$rs_assay_value['value_C']."</option>";
	}else{
		$valueOption .= "<option value='".$rs_assay_value['id']."' fenlei='".$rs_assay_value['fenlei']."'>".$rs_assay_value['value_C']."</option>";
	}
	//如果是admin，项目名后加上id  这里的判断以后得改为判断权限而不是admin???
	if($u['userid']=='admin'){
		$rs_assay_value['vid']="(".$rs_assay_value['id'].")";
	}
	//把信息存放到 数组中
	$linesArr[$rs_assay_value['fenlei']][$rs_assay_value['id']] = $rs_assay_value;
}
############循环显示出每个项目的信息 (行信息)
$i = 0;
//$actCount  = 0;
foreach($linesArr as $fenlei=>$valueArr){
	foreach($valueArr as $rs_assay_value){
		$i++;//序号
		//本单位是否化验
        	if($rs_assay_value['act']=='1'){
                	$act = '是';
                	//$actCount++;
        	}else{
                	$act = '否';
        	}
		//判断 标准表 里是不是有别名   $jcbzArr[vid]当这个数组去重复后只有两个键值，说明此项目没有别名
		if(!empty($jcbzArr[$rs_assay_value['id']]) && count(@array_unique($jcbzArr[$rs_assay_value['id']]))!=2)
		{
			$rs_assay_value['value_C'] = $jcbzArr[$rs_assay_value['id']]['html'];
		}
		if($morenFenlei!='全部' && $rs_assay_value['fenlei']!=$morenFenlei){//按照分类页面选择的分类进行显示;
                	$hideTr = "display:none; ";
        }else{
			$hideTr    = '';
		}
		if($fenlei!=$oldFenlei){//相同项目分类 纵向合并显示
			$oldFenlei = $fenlei;
			$fenleiTd  = "<td name='fenlei' rowspan='".count($linesArr[$fenlei])."'>".$rs_assay_value['fenlei']."</td>";
			$lines    .= temp("assay_value_list_line.html");//"<tr id='".$rs_assay_value['id']."' fenlei='".$rs_assay_value['fenlei']."'><td>".$i."</td><td name='fenlei' rowspan='".count($linesArr[$fenlei])."'>".$rs_assay_value['fenlei']."</td><td align='left'>".$rs_assay_value['value_C']."</td><td>".$rs_assay_value['userid']."</td><td>".$rs_assay_value['jcx']."</td><td>".$rs_assay_value['w1']."</td><td>".$rs_assay_value['w2']."</td><td>".$rs_assay_value['w3']."</td><td>".$rs_assay_value['w4']."</td><td>".$rs_assay_value['w5']."</td><td>".$act."</td><td>详细信息修改</td><td>设置方法&nbsp;| 设置标准</td></tr>";
		}else{
			$fenleiTd  = '';
			$lines    .= temp("assay_value_list_line.html");
			//"<tr  id='".$rs_assay_value['id']."' fenlei='".$rs_assay_value['fenlei']."'><td>".$i."</td><td align='left'>".$rs_assay_value['value_C']."</td><td>".$rs_assay_value['userid']."</td><td>".$rs_assay_value['jcx']."</td><td>".$rs_assay_value['w1']."</td><td>".$rs_assay_value['w2']."</td><td>".$rs_assay_value['w3']."</td><td>".$rs_assay_value['w4']."</td><td>".$rs_assay_value['w5']."</td><td>".$act."</td><td>详细信息修改</td><td>设置方法&nbsp;| 设置标准</td></tr>";
		}
	}
}
//echo temp('assay_value_list.html');
disp('assay_value_list.html');
?>
