<?php
//地图上显示站点 
//2012-06-29  lisongsen

include "../temp/config.php";
include "../inc/cy_func.php";//包含get_water_type_max函数
include "../baogao/bg_func.php";//包含is_chaobiao函数
$fzx_id	= $_SESSION['u']['fzx_id'];
if(!empty($_GET['fzx']) && $_GET['fzx']!='undefined'){
	$fzx_id	= $_GET['fzx'];
}
//得到项目 的数组用于获取项目名称
/*$water_quality = array(
    1 => "<font color='green' weight='355px'>Ⅰ类</font>",
    2 => "<font color='blue'>Ⅱ类</font>",
    3 => "<font color='red'>Ⅲ类</font>",
    4 => "<font color='#333300'>Ⅳ类</font>",
    5 => "<font color='#990066'>Ⅴ类</font>",
    6 => "<font color='#6600FF'>>Ⅴ类</font>",
);*/
//这里的颜色应该按照，目标水质来判断。达到目标水质就绿色。达不到就红色
$water_quality = array(
    1 => "<font color='green' weight='355px'>Ⅰ类</font>",
    2 => "<font color='green'>Ⅱ类</font>",
    3 => "<font color='green'>Ⅲ类</font>",
    4 => "<font color='blue'>Ⅳ类</font>",
    5 => "<font color='red'>Ⅴ类</font>",
    6 => "<font color='red'>>Ⅴ类</font>",
);
$varr	= $_SESSION['assayvalueC'];
$sid	= get_int($_GET['sid']);
$y		= get_int($_GET['y']);
$m		= get_int($_GET['m']);
$lx		= $_GET['lx'];//需要获取数据的  lx=0 获取站点 列表 lx=1 是获取单个站点的数据
if(!$y){
	$y	= date('y');
}
if(!$m){
	$m	= date('m');
}
if($m<10){
	$m	= '0'.$m;
}
$stlx	= get_int($_GET['slx']);

if($stlx!='' || $stlx === 0){
	$sqlstr	= " and cy.site_type='$stlx'";
}
$where_sql	= '';
if($fzx_id != '全部'){
	$where_sql	= " AND cy.fzx_id='{$fzx_id}' ";
	$sqlstr	.= $where_sql;
}
if($lx==0){//获取站点信
	$sql= "SELECT DISTINCT cy.id,cy_rec.sid, s.site_name, s.river_name, s.banjing, s.jingdu, s.weidu
		FROM  `cy` , cy_rec, sites AS s
		WHERE cy_rec.cyd_id = cy.id AND s.id = cy_rec.sid and cy.cy_date like '$y-$m%' $sqlstr  ";
	$str= array();
	$R	= $DB->query($sql);
	while($r = $DB->fetch_assoc($R)){
		$r['jingdu']	= trans_jwd($r['jingdu']);
		$r['weidu']		= trans_jwd($r['weidu']);
		if(empty($r['banjing'])){
			$r['banjing']	= '未填写';
		}
		if($r['jingdu']!='' && $r['weidu']!=''){
			$str[]	= $r;
		}
	}
	//没有数据的时候应该，提示一下
	echo json_encode($str);
	exit;
}elseif($sid>0 && $lx==1){ //如果得到站点iｄ
	//得到ｃｉｄ
	$rsSite	= $DB->fetch_one_assoc("select * from `sites` where id='".$sid."'");
	//获取用哪个水样类型的标准
	$jcbz_bh	= $DB->fetch_one_assoc("SELECT group_concat(id) as jcbz_bh_id,group_concat(`module_value1`) as jcbz_name,group_concat(`module_value4`) as jcbz_fuhao FROM `n_set` WHERE `module_name`='jcbz_bh' AND `module_value2`='{$rsSite['water_type']}' ");
	//如果站点里选择的是小类这里要获取大类的标准信息
	if(empty($jcbz_bh['jcbz_bh_id'])){
		$water_max_type	= get_water_type_max($rsSite['water_type'],$rsSite['fzx_id']);
		$jcbz_bh        = $DB->fetch_one_assoc("SELECT group_concat(id) as jcbz_bh_id,group_concat(`module_value1`) as jcbz_name,group_concat(`module_value4`) as jcbz_fuhao FROM `n_set` WHERE `module_name`='jcbz_bh' AND `module_value2`='{$water_max_type}' ");
	}
	$xz_arr	= array();
	$jcbz_name_arr	= array_combine(explode(',',$jcbz_bh['jcbz_bh_id']),explode(',',$jcbz_bh['jcbz_name']));
	$jcbz_fuhao_arr	= array_combine(explode(',',$jcbz_bh['jcbz_bh_id']),explode(',',$jcbz_bh['jcbz_fuhao']));
	if(!empty($jcbz_bh)){
		$jcbz_arr	= array();
		$lei		= 0;
		$sql_jcbz	= $DB->query("SELECT * FROM `assay_jcbz` WHERE `jcbz_bh_id` in ({$jcbz_bh['jcbz_bh_id']}) ORDER BY jcbz_bh_id");
		while($rs_jcbz = $DB->fetch_assoc($sql_jcbz)){
			if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'一类')){
				$lei	= 1;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'二类')){
				$lei	= 2;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'三类')){
				$lei	= 3;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'四类')){
				$lei	= 4;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'五类')){
				$lei	= 5;
			}else if($lei== 0 || $lei>6){
				if($lei === 0){
					$lei	= 6;
				}
				$lei++;
				//$lei = 7;
			}
			if(!empty($jcbz_fuhao_arr[$rs_jcbz['jcbz_bh_id']])){
				$jcbz_arr[$rs_jcbz['vid']][$lei]['name']	= $jcbz_fuhao_arr[$rs_jcbz['jcbz_bh_id']];
			}else{
				$jcbz_arr[$rs_jcbz['vid']][$lei]['name']	= $jcbz_name_arr[$rs_jcbz['jcbz_bh_id']];
			}
			if(!empty($rs_jcbz['panduanyiju'])){
				$jcbz_arr[$rs_jcbz['vid']][$lei]['xz']	= $rs_jcbz['panduanyiju'];
			}else{
				$jcbz_arr[$rs_jcbz['vid']][$lei]['xz']	= $rs_jcbz['xz'];
			}
			$jcbz_arr[$rs_jcbz['vid']][$lei]['dw']	= $rs_jcbz['dw'];
			$xz_arr[$rs_jcbz['vid']][$lei]	= $jcbz_arr[$rs_jcbz['vid']][$lei]['name'].":".$rs_jcbz['xz']."\n";
		}
	}
	$cr			= $DB->fetch_one_assoc("SELECT  cy.cy_date,cy.status,cr.id,cr.site_name,cr.bar_code FROM  `cy_rec` as cr ,  `cy` WHERE cr.sid =  '$sid' AND cr.`zk_flag`>=0$where_sql AND cr.cyd_id = cy.id and cy.cy_date like '$y-$m%' ORDER BY  `cr`.`id` DESC  limit 1");
	//找到这个站点最后一次化验数据  得到 ｃｉｄ 和 cyd_id
	$sitename	= $cr['site_name'];
	$cid		= $cr['id'];
	$quality	= 0;
	$ao			= $DB->query("select  water_type,vid,tid,vd0 from assay_order  where sid='$sid' and cid='$cid' and hy_flag>'-1' ORDER BY `vid` ASC");//有cid，sid留着干熊？
	while($r = $DB->fetch_assoc($ao)){
		$vid	= $r['vid'];
		$xm		= $varr[$vid];
		$vd0	= str_replace(' ', '', $r['vd0']);//这个地方用vd0 ，会有小于号的情况。为什么不用_vd0?
		$tmp_quality	= 0;
		$xz	= '';
		if(!empty($jcbz_arr[$vid]) && $vd0 != ''){
			ksort($xz_arr[$vid]);
			$xz	= implode('',$xz_arr[$vid]);//将限值，排序后集中显示到title中
			ksort($jcbz_arr[$vid]);//按照排序，这样I类水会最先判断
			$key_old	= '';
			foreach ($jcbz_arr[$vid] as $key => $bz_arr) {
				//判断单位是否一样
				//判断是否符合标准
				$key_old	= $key;
				//$key_add	= ++$key;
				$is_chaobiao	= is_chaobiao($vid,$r['water_type'],$bz_arr['xz'],$vd0);
				if($is_chaobiao['status'] == 0){
					$tmp_quality	= $key;
					break;
				}else{
					$tmp_quality	= ++$key;
				}
			}
			if($tmp_quality > 0 && $tmp_quality <= 3){
				$vd0	= "<font color='green'>$vd0</font>";
			}else if($tmp_quality == 4){
				$vd0	= "<font color='blue'>$vd0</font>";
			}else if($tmp_quality > 4 && $tmp_quality <= 6){
				$vd0	= "<font color='red'>$vd0</font>";
			}else{
				if($tmp_quality != $key_old){
					$vd0    = "<font color='red'>$vd0</font>";
					$quality_name	= "<font color='red'>未达标</font>";//$bz_arr['name'];
				}
			}
		}
		if($tmp_quality > $quality){
			$quality	= $tmp_quality;
		}
		//calc_water_quality($vd0, $r['vid'], $quality, $rsSite["st_type"]);
		if(empty($xz)){
			$xz	= '无';
		}
		$xmstr	.= temp('map_data_line');
	}
	if(empty($xmstr)){
		if($cr['status']<3){
			//没有采样的
			$xmstr	= "<tr><td colspan='3' style='color:red;text-align:center;padding:8px 3px;'>{$y}年{$m}月 （{$rsSite['site_name']}）还没有完成采样</td></tr>";
		}else if ($cr['status']<6){
			//没有生成化验单的
			$xmstr	= "<td colspan='3' style='color:red;text-align:center;'>{$y}年{$m}月 （{$rsSite['site_name']}）还没有下达化验任务</td>";
		}
	}
	if($water_quality[$quality]){
		$szlb	= "(水质类别：".$water_quality[$quality].")";
	}else if($quality >6){
		if($quality_name == ''){
			$quality_name = "<font color='green'>达标</font>";
		}
		$szlb	= "(水质类别：$quality_name)";
	}
	echo temp('map_data');
}
//将度分秒格式的经纬度转换成十进制的格式
function trans_jwd($jwd){
	if(preg_match("/[\x{4e00}-\x{9fa5}]+/u",$jwd)){
        $jd_xiugaihou	= preg_replace("/[\x{4e00}-\x{9fa5}]+/u",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[1]/60) + ($jd_arr[2]/3600);
        return $jwd;
	}elseif(preg_match("/[º|′|″|°|\'|\"]/", $jwd)){
		$jd_xiugaihou	= preg_replace("/[º|′|″|°|\'|\"]/",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[2]/60) + ($jd_arr[3] ? $jd_arr[3]/3600 : $jd_arr[5]/3600);
        return $jwd;
	}elseif(preg_match("/[度|分]/", $jwd)){
		$jd_xiugaihou	= preg_replace("/[度|分|\.]/",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[2]/60) + ($jd_arr[3] ? $jd_arr[3]/3600 : $jd_arr[5]/3600);
        return $jwd;
	}else{
		return $jwd;
	}
}
?>
