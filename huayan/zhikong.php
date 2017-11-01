<?php
/**
* 功能：化验单质控操作
* 作者：Mr Zhou
* 日期：2014-04-09
* 描述：实现对化验单添加删除，室内平行，添加删除修改加标，空白等质控操作
*/
require_once('../temp/config.php');
require_once('./assay_form_func.php');
$_GET['id'] = intval($_GET['id']);
if(empty($_GET['id'])){
    goback('非法请求！');
    exit();
}
/**
 * 函数名：get_zk_bar
 * 功能：获得质控样品编号
 * 作者：Mr Zhou
 * 日期：2014-04-09
 * 参数：int $bar_code 样品编号
 * 参数：int $flag [20室内平行 40加标]
 * 返回值：string $bar_code 样品编号
 * 功能描述：得到每个单位不同的质控样品编号
*/
function get_zk_bar($bar_code,$flag){
  $zk_bar_code = array(
    20 => 'P',
    40 => 'J'
  );
  return $bar_code.$zk_bar_code[$flag];;
}
//将要执行操作的样品
$sample = $DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE `id`='{$_GET['id']}'");
if(is_array($dhy_arr['xm'][$sample['vid']])){
	$vid_str = implode(',', $dhy_arr['xm'][$sample['vid']]);
	$vid_str = (''==$vid_str)?0:$vid_str;
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$sample['cyd_id']}' AND `sid`='{$sample['sid']}' AND `hy_flag`='{$sample['hy_flag']}' AND vid IN($vid_str)";
	$query = $DB->query($sql);
	while ($pay=$DB->fetch_assoc($query)) {
		$content = zhikong($pay);
	}
}else{
	$content = zhikong($sample);
}
if(''==$content){
	die(json_encode(array('error'=>'0','content'=>'')));
}else{
	die(json_encode(array('error'=>'1','content'=>$content)));
}
function zhikong($sample){
	global $DB,$global;
	//两个化验员同时操作化验单时的处理
	if(isset($_GET['flag']) && $sample['hy_flag'] != $_GET['flag']){
		$content = '该化验单的其他化验员对该站点已经进行了一些质控的操作,请刷新后重试！';
	    $_GET['action']='';
	}
	//获取当前编号id样品在assay_pay中的所有信息
	$pay	= $DB->fetch_one_assoc("SELECT * FROM `assay_pay` WHERE `id`='$sample[tid]'");
	//根据不同的action来执行相应操作，20代表操作添加平行，-20删除平行，40添加加标，-40删除加标。
	switch ($_GET['action'])
	{
	  case  '2'://添加空白
	  {
	  	$sql	= "SELECT * FROM `assay_order` WHERE `hy_flag` = -2 AND `tid` = '{$sample['tid']}' ";
	  	$snkb	= $DB->query($sql);
	  	$rows	= $DB->num_rows($snkb);
	  	if($rows == 0)
	  	{
	  		$sid		= -1;
	  		$bar_code	= '空白1';
	  	}else if($rows == 1){
	  		$kong	= $DB->fetch_assoc($snkb);
	  		if($kong['sid']==-2)
	  		{
	  			$sid		= -1;
	  			$bar_code	= '空白1';
	  		}else{
	  			$sid		= -2;
	  			$bar_code	= '空白2';
	  		}
	  		
	  	}else if($rows >= 2){
	  		$content = '本化验单已存在两条室内空白，不可再添加！';
	  		break;
	  	}
		$sql	 = "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='{$sid}',`site_name`='室内空白',`hy_flag`='-2',`bar_code`='{$bar_code}',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='{$sample['assay_over']}',`vd28`='{$_GET['vd28']}'";
		$result  = $DB->query($sql);
		$content = $result ? '' : '添加空白样失败';
		
	  }break;
	  case '-2'://删除空白
	  {
	  	$sql	= "DELETE FROM `assay_order` WHERE id='{$sample['id']}' OR (sid = {$sample['sid']} AND hy_flag = -40 AND `tid` = '{$sample['tid']}')";
		$result	= $DB->query($sql);
		$DB->query("UPDATE `assay_order` SET `ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `hy_flag`='-2' AND `tid` = '{$sample['tid']}'");
		//$result2 = $DB->query("UPDATE `assay_order` SET `sid` = '-1',`bar_code` = '空白1' WHERE `hy_flag`='-2' AND `tid` = '{$sample['tid']}'");
		//$aff_row = $DB->affected_rows($result2);
		//$result3 = $DB->query("UPDATE `assay_order` SET `sid` = '-1',`bar_code` = '空白1J' WHERE `hy_flag`='-40' AND `tid` = '{$sample['tid']}'");
		if(!$result)
		{
			$content = '删除空白失败';
		//}else if($aff_row == 1){
			//$content = '您删除的是空白1，系统已将空白2编号做了处理，改为了空白1';
		}
		
	  }break;
	  case '22'://修改空白
	  {
	  	$sql	 = "UPDATE `assay_order` SET `vd28` = '{$_GET['vd28']}',`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE id='{$_GET['id']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : '空白信号值未修改';
		
	  }break;
	  case '20'://添加平行
	  {
	  	$yxRows	= 0;	//影响条数
		if($sample['hy_flag']>=0){//正常样品
			$px_flag	= '-20';
			$result		= $DB->query("UPDATE `assay_order` SET `hy_flag` = hy_flag+20 ,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `id` = {$sample['id']}");
			$yxRows		= $DB->affected_rows();
		}else{//现场平行样品或者加标样品
			$yxRows = 1;
			$px_flag= $sample['hy_flag']-20;
		}
		if($yxRows == 1){
			$bar	= get_zk_bar($sample['bar_code'],20);//获取室内平行样品编码
			$sql	= "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='{$sample['sid']}',`hy_flag`='{$px_flag}',`site_name`='{$sample['site_name']}',`river_name`='{$sample['river_name']}',`bar_code`='{$bar}',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='{$sample['assay_over']}',`water_type`='{$sample['water_type']}'";
			$result = $DB->query($sql);
			if($result){
				//$content = '添加平行样成功';
				break;
			}
		}
		$content = '添加平行样失败';
		break;
	  }
	  case '-20'://删除室内平行
	  {
		$flagDel= '-20,-60';	//
		if($sample['hy_flag'] =='-20'){//在室内平行样上删除
			//将原样的flag-20
			$sql    = "UPDATE `assay_order` SET `hy_flag`=`hy_flag`-20,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `tid`='{$sample['tid']}' AND `hy_flag`>=20 AND `sid`='{$sample['sid']}'";
		}else if($sample['hy_flag'] >= 20){//在原样上删除
			//在原样上执行删除室内平行操作，需要将flag-20
			$sql    = "UPDATE `assay_order` SET `hy_flag`=`hy_flag`-20,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE  `id` = {$sample['id']}";
		}else{
			//现场平行样品或质控样品或者加标样品即原样的flag是负数的样品
			$flagDel= "-26,-66";
			$sql    = "UPDATE `assay_order` SET `ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `tid`='$sample[tid]' AND `hy_flag`='-60' AND `sid`='{$sample['sid']}'";
		}
		$result		= $DB->query($sql);
		$sql2		= "DELETE FROM `assay_order` WHERE `tid`='{$sample['tid']}' AND `hy_flag`IN($flagDel) AND `sid`='{$sample['sid']}'";
		$result2	= $DB->query($sql2);
		//$content = '删除平行样成功';
		break;
	  }
	  case '40'://添加加标
	  {
	  	$yxRows	= 0;	//影响条数
	  	if($sample['hy_flag']>=0){//正常样品
	  		$jb_flag	= '-40';
			$result		= $DB->query("UPDATE `assay_order` SET `hy_flag` = hy_flag+40 ,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `id` = {$sample['id']} AND `hy_flag` >= 0");
			$yxRows		= $DB->affected_rows();
		}else if( in_array($sample['hy_flag'],array('-1','-2'))){//-1,-2分别为室内空白1,2
			$yxRows = 1;
			$jb_flag= '-40';
		}else{//现场平行样品或者室内平行
			$yxRows = 1;
			$jb_flag= $sample['hy_flag']-40;
		}
		if($yxRows == 1){
			$bar	= get_zk_bar($sample['bar_code'],40);//获取室内平行样品编码
			$sql	= "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='{$sample['sid']}',`hy_flag`='{$jb_flag}',`water_type`='{$sample['water_type']}',`site_name`='{$sample['site_name']}',`river_name`='{$sample['river_name']}',`bar_code`='{$bar}',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='{$sample['assay_over']}',`vd28`='{$_GET['vd28']}',`vd29`='{$_GET['vd29']}',`vd30`='{$_GET['vd30']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}'";
			$result= $DB->query($sql);
			if($result){
				//$content = '添加加标样成功';
				break;
			}
		}
		$content = '添加加标样失败';
	  	break;
	  }
	  case '-40'://删除加标
	  {
		if($sample['hy_flag'] =='-60'){//在室内平行B+加标 的加标样 上执行删除
			$flagDel= '-60';
		}else if($sample['hy_flag'] =='-40'){//在原样的加标样上执行删除
			$flagDel= '-40';
			$sql    = "UPDATE `assay_order` SET `hy_flag`=`hy_flag`-40,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `tid`='{$sample['tid']}' AND `hy_flag`>=40 AND `sid`='{$sample['sid']}'";
		}else if($sample['hy_flag'] >= 40){//在原样上执行删除
			$flagDel= '-40';
			$sql    = "UPDATE `assay_order` SET `hy_flag`=`hy_flag`-40,`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `id`='{$sample['id']}'";
		}else if($sample['hy_flag'] == '-26'){//在现场平行B样的室内平行原样上执行删除
			$flagDel= '-66';
		}else{//现场平行样品 -6 -46 -66
			$flagDel= "-46";
		}
		if($sql)
		{
			$result  = $DB->query($sql);
		}
		
		$sql2    = "DELETE FROM `assay_order` WHERE `tid`='{$sample['tid']}' AND `hy_flag`='$flagDel' AND `sid`='{$sample['sid']}'";
		$result2 = $DB->query($sql2);
		//$content = '删除加标样成功';
		break;
	  }
	  case '4040'://修改加标
	  {
	  	$sql	 = "UPDATE `assay_order` SET `vd28`='{$_GET['vd28']}',`vd29`='{$_GET['vd29']}',`vd30`='{$_GET['vd30']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}',`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE id='{$_GET['id']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : '加标信息未修改';
		
	  }break;
	  case '4'://添加单点标液
	  {
	  	$zk_set = $global['zk']['zhikong'];
	  	$bar	= (''==$zk_set['zky_name']) ? '自控样' : $zk_set['zky_name'];
	  	$sql	= "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='-4',`hy_flag`='-4',`bar_code`='{$bar}',`site_name`='{$bar}',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='{$sample['assay_over']}',`vd28`='{$_GET['piHao']}',`vd29`='{$_GET['biaoZhunZhi']}',`vd30`='{$_GET['buQueDingDu']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}'";
	  	$addZkyGs = intval($_GET['addZkyGs']) ? intval($_GET['addZkyGs']) : 1;
	  	for ($i=0; $i < $addZkyGs; $i++) {
	  		$result	 = $DB->query($sql);
	  	}
		$content = $result ? '' : "添加{$bar}失败";
	  }break;
	  case '-4'://删除单点标液
	  {
	  	$sql	 = "DELETE FROM `assay_order` WHERE id='{$_GET['id']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : "{$bar}删除失败";
	  }break;
	  case '44'://修改单点标液
	  {
	  	$sql	= "UPDATE `assay_order` SET `vd28`='{$_GET['piHao']}',`vd29`='{$_GET['biaoZhunZhi']}',`vd30`='{$_GET['buQueDingDu']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}',`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`=''";
	  	if( '1' != $_GET['add_all']){
	  		$sql .= " WHERE `id`='{$_GET['id']}'";
	  	}else{
	  		$sql .= " WHERE `tid`='{$sample['tid']}' AND `hy_flag`='-4'";
	  	}
		$result	 = $DB->query($sql);
		$content = $result ? '' : "{$bar}信息未修改";
	  }break;
	  case '7'://添加常规平行样（不同稀释倍数的统一样品）
	  	$bar	= $sample['bar_code'];
		$sql	= "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='{$sample['sid']}',`hy_flag`='-7',`bar_code`='{$bar}',`site_name`='{$sample['site_name']}',`river_name`='{$sample['river_name']}',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='{$sample['assay_over']}',`water_type`='{$sample['water_type']}',`vd28`='{$_GET['piHao']}',`vd29`='{$_GET['biaoZhunZhi']}',`vd30`='{$_GET['buQueDingDu']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : '添加常规平行样失败';
	  	break;
	  case '-7'://添加常规平行样（不同稀释倍数的统一样品）
	  	$sql	 = "DELETE FROM `assay_order` WHERE id='{$_GET['id']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : '常规平行样删除失败';
	  	break;
	  case '8'://添加单点标液
	  {
	  	$bar	= '0.2C和0.8C';
	  	$zk_set = $global['zk']['zhikong'];
	  	$sql	= "INSERT INTO `assay_order` SET `cyd_id`='{$sample['cyd_id']}',`vid`='{$sample['vid']}',`cid`='{$sample['cid']}',`mid`='{$sample['mid']}',`tid`='{$sample['tid']}',`sid`='-8',`hy_flag`='-8',`bar_code_position`='{$sample['bar_code_position']}',`assay_over`='S',`vd28`='{$_GET['piHao']}',`vd29`='{$_GET['biaoZhunZhi']}',`vd30`='{$_GET['buQueDingDu']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}'";
	  	$sql_f_1 = "SELECT `id` FROM `assay_order` WHERE `tid`='{$sample['tid']}' AND `site_name`='0.2C' AND `hy_flag`='-8'";
	  	$row = $DB->fetch_one_assoc($sql_f_1);
		if(empty($row)) {
			$result	 = $DB->query($sql . ",`bar_code`='0.2C',`site_name`='0.2C'");
		}
		$sql_f_2 = "SELECT `id` FROM `assay_order` WHERE `tid`='{$sample['tid']}' AND `site_name`='0.8C' AND `hy_flag`='-8'";
		$row = $DB->fetch_one_assoc($sql_f_2);
		if(empty($row)) {
			$result	 = $DB->query($sql . ",`bar_code`='0.8C',`site_name`='0.8C'");
		}
		$content = $result ? '' : "添加{$bar}失败";
	  }break;
	  case '-8'://删除单点标液
	  {
	  	$sql	 = "DELETE FROM `assay_order` WHERE id='{$_GET['id']}'";
		$result	 = $DB->query($sql);
		$content = $result ? '' : "{$bar}删除失败";
	  }break;
	  case '88'://修改单点标液
	  {
	  	$sql	= "UPDATE `assay_order` SET `vd28`='{$_GET['piHao']}',`vd29`='{$_GET['biaoZhunZhi']}',`vd30`='{$_GET['buQueDingDu']}',`vd31`='{$_GET['vd31']}',`vd32`='{$_GET['vd32']}',`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`=''";
	  	if( '1' != $_GET['add_all']){
	  		$sql .= " WHERE `id`='{$_GET['id']}'";
	  	}else{
	  		$sql .= " WHERE `tid`='{$sample['tid']}' AND `hy_flag`='-8'";
	  	}
		$result	 = $DB->query($sql);
		$content = $result ? '' : "{$bar}信息未修改";
	  }break;
	}
	return $content;
}
