<?php
/**
 * 函数名：check_zk
 * 功能：检查质控并计算质控结果
 * 作者：Mr Zhou
 * 日期：
 * 参数：int $ao_id $flag供本函数内部递归调用，传入true则不再递归
 * 返回值：
 * 功能描述：
*/
function check_zhi_kong( $ao_id,$jcx,$flag=false ){
	global $DB,$u;
	$fzx_id = $_SESSION['u']['fzx_id'];
	$r = $DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE `id` = '$ao_id' AND `hy_flag` != 0");
	// if(!$r || '' == $r['vd0']){return false;}
	/***********************/
	//不参与质控的项目
	//嗅味,肉眼可见物,藻类不参与质控
	$not_need_zk = array();
	if(in_array($r['vid'],$not_need_zk)){
		return false;
	}
	/***********************/
	$tid		= $r['tid'];		//化验单id
	$cid		= $r['cid'];		//采样单id
	$vid		= $r['vid'];		//项目id
	$hy_flag	= $r['hy_flag'];	//质控标识
	$bar_code	= $r['bar_code'];	//样品编号
	$r['jcx']	= floatval($jcx);
	switch( $r['hy_flag'] ) {
		case -20: //室内平行
			//找出此室内平行样的原样
			$sql = "SELECT `id`,`vd0`,`_vd0`,`water_type` FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND (`hy_flag` BETWEEN 20 AND 39 OR `hy_flag` >= 60) LIMIT 1";
			$sample = $DB->fetch_one_assoc($sql);
			if(!$sample['id']){
				error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品的室内平行数据有误,请与技术人员联络,出错行号:'.__LINE__);
			}
			$jie_guo = calc($sample,$r,$jcx,$vid);
			if($jie_guo){
				update_assay_order($jie_guo,$sample['id'],$r['id']);
			}
			break;
		case -40: case -60: case -66://加标
			if($r['hy_flag'] == -40){
				//找出加标原样 室内空白的flag是-2
				$sql_add_where = " AND (`hy_flag` BETWEEN 40 AND 69 OR `hy_flag`='-2')";
			}else if($r['hy_flag'] == -66){
				//这是现场平行B做室内平行，室内平行B的hy_flag是-26
				$sql_add_where = " AND `hy_flag` ='-26'";
			}else{
				//这是室内平行做加标，室内平行B的hy_flag是-20
				$sql_add_where = " AND `hy_flag` ='-20'";
			}
			$sql	= "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} $sql_add_where LIMIT 1";
			$sample	= $DB->fetch_one_assoc($sql);
			if(!$sample['id']){
				error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品的加标回收数据有误,请与技术人员联络,出错行号:'.__LINE__);
			}
			//如果做了室内平行，则使用平均值计算加标
			if( '' !== $sample['ping_jun'] ){
				if($sample['hy_flag']=='60'){
					$sql="SELECT * from `assay_order` where `tid`={$sample['tid']} AND `sid`={$sample['sid']} AND  `hy_flag` ='-20' LIMIT 1";
				}else{
					$sql="SELECT * from `assay_order` where `tid`={$sample['tid']} AND `sid`={$sample['sid']} AND  `hy_flag` =60 LIMIT 1";
				}
				$yy=$DB->fetch_one_assoc($sql);
				$sample['vd0']=$sample['ping_jun'];
				$sample['_vd0']=($yy['_vd0']+$sample['_vd0'])/2;
			}
			$jie_guo = jbhs($sample,$r);
			if($jie_guo){
				$sql = "UPDATE `assay_order` SET `ping_jun`='{$jie_guo['理论浓度']}',`xiang_dui_pian_cha`='{$jie_guo['加标回收率']}',`ping_jia`='{$jie_guo['评价']}' WHERE `id`={$r['id']}  ORDER BY `id` DESC LIMIT 1";
				$DB->query($sql);
			}
			break;
		case	1: //全程序空白
			//查找是否存在两条室内空白
			$sql	= "SELECT `id`,`vd0`,`_vd0`,`vd28`,`water_type` FROM `assay_order` WHERE `tid`=$tid AND `vid`=$vid AND `hy_flag`='-2' ORDER BY `bar_code`";
			$_SNKB	= $DB->query($sql);
			if($DB->rows==2){
				$_snkb_1=$DB->fetch_assoc($_SNKB);
				$_snkb_2=$DB->fetch_assoc($_SNKB);
				//vd28是过程值，如果都存在过程值则使用过程值计算
				if($r['vd28'] != '' && $_snkb_1['vd28'] != '' && $_snkb_2['vd28'] != ''){
					$r['vd0'] = $r['_vd0'] = $r['vd28'];
					$_snkb_1['vd0'] = $_snkb_1['_vd0'] = $_snkb_1['vd28'];
					$_snkb_2['vd0'] = $_snkb_2['_vd0'] = $_snkb_2['vd28'];
				}
				//两室内空白计算平均值和相对偏差
				$jie_guo=calc($_snkb_1,$_snkb_2,$jcx,$vid,'室内空白');
				if($jie_guo){
					//因为全程序空白和两室内空白的平均值计算相对偏差，所以vd0代表修约后的平均值，_vd0代表没有修约的
					$data_2 = array('vd0'=>$jie_guo[0],
									'_vd0'=>($_snkb_1['_vd0']+$_snkb_2['_vd0'])/2);
					$jie_guo=calc($data_2,$r,$jcx,$vid,'全程空白');
					if($jie_guo){
						update_assay_order( $jie_guo,'',$r['id']);
					}
				}
			}
			break;
		case	-2: //室内空白
			//同时找出两条室内空白
			$sql	= "SELECT `id`,`vd0`,`_vd0`,`vd28`,`water_type` FROM `assay_order` WHERE `tid`=$tid AND `vid`=$vid AND `hy_flag`='-2' ORDER BY `bar_code`";
			$_SNKB	= $DB->query($sql);
			if($DB->rows==2){
				$_snkb_1=$DB->fetch_assoc($_SNKB);
				$_snkb_2=$DB->fetch_assoc($_SNKB);
				$xinhao = false;
				if($_snkb_1['vd28'] != '' && $_snkb_2['vd28'] != ''){
					$xinhao = true;
					$_snkb_1['vd0'] = $_snkb_1['_vd0'] = $_snkb_1['vd28'];
					$_snkb_2['vd0'] = $_snkb_2['_vd0'] = $_snkb_2['vd28'];
				}
				$jie_guo=calc($_snkb_1,$_snkb_2,$jcx,$vid,'室内空白',$xinhao);
				if($jie_guo){
					update_assay_order($jie_guo,$_snkb_1['id'],$_snkb_2['id']);
				}
			}else{
				//error_msg('室内空白有且只有两条，您的化验单上出现了'.($DB->rows).'条！');
			}
			break;
		case	3: case 23: case 43: case 63://标准样品
			//标准样品判断是否合格
			$sql = "SELECT bzwz_detail.*
					FROM `bzwz_detail`
						LEFT JOIN `cy_rec` ON `bzwz_detail`.`wz_id`=`cy_rec`.`by_id`
						LEFT JOIN `assay_order` ON `assay_order`.`cid`=`cy_rec`.`id`
					WHERE `assay_order`.`id`=$r[id] AND `bzwz_detail`.`vid`=$r[vid]";
			$by=$DB->fetch_one_assoc($sql);
			if($by['id']){
				//preg_match('/[\d\.]+/',$by['eligible_bound'],$result);
				$e_bound	= floatval(trim($by['eligible_bound']));
				$consistence= floatval(trim($by['consistence']));
				$t_bound	= abs($r['_vd0']-$consistence);
				$pj		 = ($t_bound <= $e_bound)?'合格':'不合格';
				$pc		 = round((abs(($r['_vd0']-$consistence))/$consistence)*100,2);
				$DB->query("UPDATE `assay_order` SET `ping_jia`='$pj',`xiang_dui_pian_cha`='$pc' WHERE `id`={$r['id']} ORDER BY `id` DESC LIMIT 1");
			}
			break;
		case	-4: //自控样
		case	-8: //02C和0.8C
			if('' != $r['vd29'] && '' != $r['vd30']){
				if('mg/L' == $r['vd32']){
					//如果不确定度的单位是mg/L，则以绝对误差进行判断
					$pc = $r['_vd0']-$r['vd29'];
					$ping_jia = (abs(floatval($pc)) <= floatval($r['vd30'])) ? '合格':'不合格';
					// `ping_jun`='{$r['vd30']}',
					$DB->query("UPDATE `assay_order` SET`xiang_dui_pian_cha`='{$pc}',`ping_jia`='$ping_jia' WHERE `id`={$r['id']} ORDER BY `id` DESC LIMIT 1");
				}else if('%' == $r['vd32']){
					//如果不确定度的单位是%，则以相对误差进行判断
					$zky = array('vd0'=>$r['vd29'],'_vd0'=>$r['vd29']);
					//参数格式：calc($data_1,$data_2,$jcx,$vid,$kb_flag=false,$type_flag='p') p表示偏差,w表示误差
					$jie_guo = calc($r,$zky,$jcx,$vid,false,'w');
					if($jie_guo){
						$ping_jia = (abs(floatval($jie_guo[1])) <= floatval($r['vd30'])) ? '合格' : '不合格';
						// `ping_jun`='{$r['vd30']}',
						$DB->query("UPDATE `assay_order` SET `xiang_dui_pian_cha`='{$jie_guo[1]}',`ping_jia`='$ping_jia' WHERE `id`={$r['id']} ORDER BY `id` DESC LIMIT 1");
					}
				}
			}
			break;
		case	-6: //现场平行B样
			//找到现场平行A样
			$sql = "SELECT `id`,`vd0`,`_vd0`,`hy_flag` FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` IN(5,25,45,65)";
			$sample = $DB->fetch_one_assoc($sql);
			if(!$sample['id']){
				error_msg('化验单【'.$r['tid'].'】'.'现场平行样品数据有误,请与技术人员联络,出错行号:'.__LINE__);
			}
			//检查现场平行A样是否做了质控，需要将其质控信息先计算完
			if($sample['hy_flag'] != 5){
				//如果现场平行A样做了室内平行需要计算出平均值 45是做加标 25,65表示做了平行
				if($sample['hy_flag']==25||$sample['hy_flag']==65){
					$sql = "SELECT `id`,`vd0`,`_vd0`,`ping_jun` FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` ='-20'";
					$snpx = $DB->fetch_one_assoc($sql);
					$sample['vd0']	= $snpx['ping_jun'];
					$sample['_vd0']	= ($sample['_vd0']+$snpx['_vd0'])/2;
				}
			}
			$sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` IN('-26','-46')";
			$query = $DB->query($sql);
			while ($zk_yang=$DB->fetch_assoc($query)) {
				if($zk_yang['id']){
					//现场平行B样做室内平行
					if($zk_yang['hy_flag'] == '-26'){
						$jie_guo = calc($r,$zk_yang,$jcx,$vid);
						if($jie_guo){
							update_assay_order($jie_guo,$r['id'],$zk_yang['id']);
						}
						//现场平行做了室内平行的时候需要该样的平均值与另一个现场平行的值求相对偏差
						$r['vd0']	= $jie_guo[0];
						$r['_vd0']	= ($r['_vd0']+$zk_yang['_vd0'])/2;
					}else if($zk_yang['hy_flag'] == '-46'){
						//现场平行B样做加标
						$jie_guo = jbhs($r,$zk_yang);
						if($jie_guo){
							$sql = "UPDATE `assay_order` SET `ping_jun`='{$jie_guo['理论浓度']}',`xiang_dui_pian_cha`='{$jie_guo['加标回收率']}',`ping_jia`='{$jie_guo['评价']}' WHERE `id`={$zk_yang['id']} ORDER BY `id` DESC LIMIT 1";
							$DB->query($sql);
						}
					}
				}
			}
			$jie_guo = calc($sample,$r,$jcx,$vid);
			if($jie_guo){
				update_assay_order($jie_guo,$sample['id'],$r['id']);
			}
			break;
		case	-7: //平行样品（不同稀释倍数的样品）
			//找出此样品的平行样品
			$sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `bar_code`='{$r['bar_code']}'";
			$sum=$n=0;
			$query = $DB->query($sql);
			$reliable=$ao_id=array();
			while ($row = $DB->fetch_assoc($query)){
				if($row['id']==$_POST['reliable'][$row['id']]){
					$n++;
					$sum+=$row['_vd0'];
					$vd27sum+=$row['vd27'];//甲第鞭毛虫和隐孢子虫
				}else{
					$reliable[]=$row['id'];
				}
				$id_arr[] = $row['id'];
				$ao_id = ('-7'!=$row['hy_flag'])?$row['id']:$ao_id;
			}
			if(count($id_arr)==0){
				error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品数据有误,请与技术人员联络,出错行号:'.__LINE__);
			}
			//round_value 函数在 huayan/assay_form_func.php
			if($n>0){
				 $_avg = $sum/$n;
				 //甲第鞭毛虫和隐孢子虫
				if($r['vid']=='70'||$r['vid']=='69')
				{
					$_avg = $vd27sum;
				}
				//修约
				$avg = round_value($_avg,$r['tid']);
			}else{
				$_avg=$avg='';
			}
			$id_str = implode(',', $id_arr);
			$sql = "UPDATE `assay_order` SET `reliable`='1',`vd0`='$avg',`_vd0`='$_avg',`assay_over`='1' WHERE `id`IN($id_str)";
			$DB->query($sql);
			//舍弃的数据，将可信度调整为2
			if(count($reliable)>0){
				$id_str = implode(',', $reliable);
				$sql = "UPDATE `assay_order` SET `reliable`='2',`vd27`=concat(replace(`vd27`,'(舍)',''),'(舍)') WHERE `id` IN ($id_str)";
			}
			$DB->query($sql);
			if(false==$flag){
				check_zhi_kong( $ao_id,$jcx ,true);
			}
			break;
	}
	if(''!=$jie_guo['公式'] && (stripos($t['td30'],$jie_guo['公式'])===false)){
		$jb_jsgs = $jie_guo['公式'];
		$jb_jsgs =$t['td30']."\n".$jb_jsgs;
		$sql = "UPDATE `assay_pay` SET `td30` = '$jb_jsgs' WHERE `id`={$r['tid']}";
		$DB->query($sql);
	}

}
/**
 * 功能：计算加标回收率
 * 作者：Mr Zhou
 * 日期：2014-05-19
 * 参数：array $r		原水样信息
 * 参数：array $jbhs	加标样信息
 * 参数：float $c_shui	水样浓度		r0[_vd0]=>C水 如果是平行样品采用平均值
 * 参数：float $c_shi	加标后水样浓度	r1[_vd0]=>C实
 * 参数：float $v_shui	取样体积		r1[vd28]=>V水
 * 参数：float $c_biao	标准溶液浓度	r1[vd29]=>C标
 * 参数：float $v_biao	加标液体积		r1[vd30]=>V标
 * 参数：int	$vid	项目id
 * 参数：float $jcx	检出限
 * 返回值：array('理论浓度' => $c_li,'评价' => $ping_jia,'加标回收率' => $P)
 * 功能描述：
 * 1、C理=(C标 * V标)/(V水)
 * 2、$c_li = ($c_biao*$v_biao)/$v_shui;//获取理论浓度 加标回收率 P=(C实-C水)/C理*100%
 * 3、$r0:加标前测得数据
 * 4、$r1:加标后测得数据
*/

function jbhs($r,$jbhs){
	//加标计算分析详见https://abs.anheng.com.cn/project/issues/13690
		global $global,$u,$DB;
	$sql="SELECT * from `zk_js` where vid ='{$r[vid]}'";
	$jb_sz=$DB->fetch_one_assoc($sql);
	if($jb_sz['id'])
	{
		$jb_js=json_decode($jb_sz['jbhs'],true);
	}
	else
	{
		$jb_js=$global['zk']['jb_js'];
	}
	$v_shui	= $jbhs['vd28'];	//原水样的体积
	$c_biao	= $jbhs['vd29'];	//标准溶液的浓度
	$v_biao	= $jbhs['vd30'];	//加入标准溶液的体积
	$v_jby	= $v_shui+$v_biao;	//加标样总体积
	$ding_v = $v_shui+$v_biao;
	$vid    =$jbhs['vid'];
	$jcx    =$jbhs['jcx'];
	switch( $jb_js['sj_jg'] ) {
		case '1':
			$c_shui	= ($r['_vd0']>0)?$r['_vd0']:0;		//原水样的浓度
			$c_shi	= ($jbhs['_vd0']>0)?$jbhs['_vd0']:0;	//加标液的浓度
			break;
		case '2':
			$c_shui	= ($r['vd0']>0)?$r['vd0']:0;		//原水样的浓度
			$c_shi	= ($jbhs['vd0']>0)?$jbhs['vd0']:0;	//加标液的浓度
			break;
	}
	if($r['_vd0'] < 0){
		$c_shui = 0;						 //加标后的值小于0 那就按照0计算
	}else if($r['_vd0'] < $jcx){      //加标后的值小于检出限 那就按照配置来计算
		switch( $jb_js['jcx_jg'] ) {
			case '1':
				$c_shui=($r['_vd0']>0)?$r['_vd0']:0;
				break;
			case '2':
				$c_shui=$jcx/2;
				break;
			case '3':
				$c_shui=0;
				break;
		}	
	}
	switch( $jb_js['jsgs'] ) {
		case '1':
			if($jb_js['tjxs']=='1')
			{
				$c_li	= @(($c_biao*$v_biao)/($ding_v));		//获取理论浓度
				$c_li   =sc_to_num($c_li);
				$P		= @(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100);
				$jb_jsgs= "理论浓度：$c_li	= (($c_biao*$v_biao)/($ding_v)); 加标回收：(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100) = $P%";
			}
			else if($jb_js['tjxs']=='2')
			{
				$c_li	= @(($c_biao*$v_biao)/($v_shui));		//获取理论浓度
				$c_li   =sc_to_num($c_li);
				$P		= @(($c_shi-$c_shui)/$c_li*100);
				$jb_jsgs= "理论浓度：$c_li	= (($c_biao*$v_biao)/($v_shui)); 加标回收：(($c_shi-$c_shui)/$c_li*100) = $P%";

			}
			$jcx_jg=$r['_vd0'];
			break;
		case '2':
				$c_li	= @(($c_biao*$v_biao)/($ding_v));		//获取理论浓度
				$c_li   =sc_to_num($c_li);
				$P		= @(($c_shi-$c_shui)/$c_li*100);
				$jb_jsgs= "理论浓度：$c_li	= (($c_biao*$v_biao)/($ding_v)); 加标回收：(($c_shi-$c_shui)/$c_li*100) = $P%";
			break;
	}	
	switch( $jb_js['blws'] ) {
	case '1':
		$P=round_yxws($P,3);
		break;
	case '2':
		$P=round_value($P,1);
		break;
	}
		//有几个特殊项目需要在计算加标回收时进行系数转化，还有几个项目是兰州工艺法项目也需要单独加标回收。
	include_once './get_jb_data.php';
	$hs='getjbData_'.$r['vid'];
	if(function_exists($hs))
	{
		$jb=$hs($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$r['tid'],$r,$jbhs);
		$P=$jb['P'];
		$jb_jsgs=$jb['jb_jsgs'];
	}
	//if($u['admin']){echo "$jb_jsgs; <br /> $c_li <br />";$a = (($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100); echo "$c_li	= (($c_biao*$v_biao)/($ding_v)*$xs);";echo " $a=(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100);<br />";die;}
	//修约质控结果
	// if(!intval($jb_set['ws'])){
	// 	$P = Rounding_value($P,$vid,'J');
	// }else{
	// 	$P = Rounding_value($P,$vid,'J',intval($jb_set['ws']));
	// }
	// $c_li = round_value($c_li,$r['tid'],1);//最后一个参数表示多保留一位
	$ping_jia = get_zkfw($vid,$r['water_type'],$jbhs['_vd0'],$P,'jbhs');//调用评价加标是否合格的方法
	$jie_guo = array(
				'加标回收率'=> $P,
				'理论浓度'	=> $c_li,
				'评价'		=> $ping_jia,
				'公式'		=> $jb_jsgs
			);
	return $jie_guo;
}

/**
 * 功能：计算相对偏差函数
 * 作者：Mr Zhou
 * 日期：2014-05-06
 * 参数：float $data_1 化验结果
 * 参数：float $data_2 要比较的化验结果
 * 参数：float $jcx 检出限
 * 参数：int $vid 项目id
 * 参数：$kb_flag 默认值为 false 表示不是空白
 * 参数：$type_flag='w' 偏差 p 误差 w
 * 返回值：$r[0] 平均值 | $r[1],$r[2]: 相对偏差
 * 功能描述：
*/
function calc($data_1,$data_2,$jcx,$vid,$kb_flag=false,$type_flag='p',$xinhao=false){
	global $global,$tid,$u;
	$r = array();
	$jcx = floatval($jcx);
	$water_type = ($data_2['water_type']>0)?$data_2['water_type']:$data_1['water_type'];
	$data = get_data_vd0($data_1,$data_2,$jcx,'P',$xinhao);
	//平均值的计算修约位数与检测方法中配置的检测结果的修约位数一致
	$r[0] = $avg = ($data[0]+$data[1])/2;
	//相对偏差
	$data = get_data_vd0($data_1,$data_2,$jcx,'X');
	$vd0_1 = $data[0];
	$vd0_2 = $data[1];
	if(floatval($avg) == 0){
		$r = array($avg,'0.00','0.00','评价'=>'合格');
	}else{
		//相对偏差
		if('p'==$type_flag){
			$_xdpc = ($vd0_1-$vd0_2)/($vd0_1+$vd0_2)*2*100;
		}else if('w'==$type_flag){
			$_xdpc = ($vd0_1-$vd0_2)/($vd0_2)*100;
		}else{
			$_xdpc = ($vd0_1-$vd0_2)/($vd0_1+$vd0_2)*2*100;
		}
		$xdpc = Rounding_value(abs($_xdpc));
		if(floatval($xdpc)==0){
			$r[1] = $r[2] = '0.00';
		}else{
			$r[1] = ($_xdpc>0)? $xdpc :'-'.$xdpc;
			$r[2] = ($_xdpc<0)? $xdpc :'-'.$xdpc;
		}
	}
	//空白标准
	$kb_bz = 50;
	if($kb_flag){
		$r['评价'] = (abs($r[1])>$kb_bz) ? '不合格' : '合格';
	}else{
		$r['评价'] = get_zkfw($vid,$water_type,$data[2],abs($r[1]),'sn_jmd');
	}
	if($xinhao==false){
		//round_value 函数在 huayan/assay_form_func.php
		$r[0] = round_value($avg,$tid);
	}else{
		$r[0] = $avg;
	}
	return $r;
}
/**
 * 功能：根据质控配置信息获取数值1和数值2
 * 作者：Mr Zhou
 * 日期：2015-05-26
 * 参数：float $data_1 化验结果
 * 参数：float $data_2 要比较的化验结果
 * 参数：float $jcx 检出限
 * 参数：string $type['X'|'P']
 * 返回值：
 * 功能描述：
*/
function get_data_vd0($data_1,$data_2,$jcx,$type,$xinhao=false){
	global $global;
	$jcx	= floatval($jcx);
	$vd0	= $global['zk'][$type]['data'];	//计算平均值的值
	$XYjcx	= $global['zk'][$type]['XYjcx'];//小于检出限的配置 [jcx|_vd0|0]
	/**********************************/
	$vd0	= ($vd0=='')	? '_vd0' : $vd0;
	$XYjcx	= ($XYjcx=='')	? '_vd0' : $XYjcx;
	/**********************************/
	//小于0时按0计算，小于检出限的配置为0的结果为0
	$data_1[0] = 0;
	$data_2[0] = 0;
	$data_1['_vd0'] = ($data_1['_vd0']<0) ? 0 : $data_1['_vd0'];
	$data_2['_vd0'] = ($data_2['_vd0']<0) ? 0 : $data_2['_vd0'];

	$vd0_1	= $data_1[$vd0];	//检测结果
	$vd0_2	= $data_2[$vd0];	//检测结果
	$_vd0_1	= $data_1['_vd0'];	//原始值
	$_vd0_2	= $data_2['_vd0'];	//原始值

	//检测结果必须都有值
	if( $vd0_1 === '' || $vd0_2 === '' ){
		return false;
	}
	//小于检出限的处理
	if(floatval($jcx)>0&&$xinhao==false){
		//如果都小于检出限 则按0来计算
		if( ($_vd0_1 < $jcx) && ($_vd0_2 < $jcx) ){
			$vd0_1 = $vd0_2 = 0;
		}else{
			$vd0_1 = ($_vd0_1 < $jcx) ? $data_1[$XYjcx] : $vd0_1;
			$vd0_2 = ($_vd0_2 < $jcx) ? $data_2[$XYjcx] : $vd0_2;
		}
	}

	return array($vd0_1,$vd0_2,($vd0_1+$vd0_2)/2);
}
/**
 * 功能：获取质控范围
 * 参数：int	$vid		项目id
 * 参数：int	$water_type 水样类型id
 * 参数：float	$nd			浓度
 * 参数：float	$jieguo		需要判断是否在质控范围的数值。
 * 参数：string	$leixing	jieguo数值的数据类型，如：加标回收率（j），室内精密度(snjmd)，室间精密度（sjjmd），室内相对误差（snxdwc）,室间相对误差（sjxdwc）。
 * 返回值：传入项目id和水体类型未必传参数，如果传入浓度返回质控范围数组，传入jieguo和leixing，返回是否合格信息
 * 功能描述：jieguo参数和leixing参数必须同时存在。传入项目id和水体类型为必传参数，如果传入浓度返回质控范围数组，传入jieguo和leixing，返回是否合格信息
*/
function get_zkfw($vid,$water_type,$nd,$jieguo='',$leixing=''){
	global $DB,$global,$u,$fzx_id,$rootdir;
	if(!intval($water_type)){ return '50'; }
	//vid nd 未传参时返回空
	if(!intval($vid)||''==$nd){ return ''; }
	//判断质控类型是否匹配
	$lx_arr = array('jbhs','sn_jmd','sj_jmd','sn_xdwc','sj_xdwc');
	if(''!=$leixing&&!in_array($leixing,$lx_arr)){ return ''; }
	// 质控只配置顶级水样类型的合格范围，所以需要找到顶级水样类型
	if(!function_exists('get_water_type_max')){
		include_once "{$rootdir}/inc/cy_func.php";
	}
	$water_type=get_water_type_max($water_type,$fzx_id);
	// 根据水样类型倒序排，找到本水样类型或者默认水样类型的配置信息
	$sql = "SELECT * FROM `zk_set` WHERE `vid` = '{$vid}' AND `water_type` IN ('0','{$water_type}') AND `{$leixing}` !='' AND `{$leixing}` IS NOT NULL ORDER BY `water_type` DESC";
	$result = $DB->query($sql);
	if($DB->rows==0){
		$row=$global['zk']['zk_set'];
	}else{
		$row = array();
		while ( $r = $DB->fetch_assoc($result)) {
			foreach ($r as $key => $value) {
				$r[$key] = trim($r[$key]);
			}
			$row[] = $r;
		}
	}
	//根据浓度判断适用哪个质控范围
	foreach($row as $k)
	{
		$f = $p = 0;
		$k['nd'] = trim($k['nd']);
		//取出浓度范围
		if(strstr($k['nd'],'>')){
			$a = substr($k['nd'],1);
			if(floatval($nd) > floatval($a)){ $f = 1; }
		}else if(strstr($k['nd'],'≥')){
			$a = substr($k['nd'],3);
			if(floatval($nd) >= floatval($a)){ $f = 1; }
		}else if(strstr($k['nd'],'<')){
			$a = substr($k['nd'],1);
			if(floatval($nd) < floatval($a)){ $f = 1; }
		}else if(strstr($k['nd'],'≤')){
			$a = substr($k['nd'],3);
			if(floatval($nd) <= floatval($a)){ $f = 1; }
		}else{
			$arr = array();
			$arr = preg_split('/[^\d.]/',$k['nd']);
			$arr_count = count($arr)-1;
			if(floatval($nd) >= floatval($arr['0']) && floatval($nd) < floatval($arr[$arr_count])){
				$f = 1;
			}
		}
		$error = '';
		if($f == 1){
			//如果传入的参数没有jieguo和leixing，输出该浓度下的所有质控范围
			if($jieguo === ''){
				return $k[$leixing];
			}else if($k[$leixing]==''||$k[$leixing]=='-'){
				break;//质控范围未设置
			}else{
				$jieguo = floatval($jieguo);
				switch($leixing)
				{
					case 'jbhs':
						$arr = preg_split('/[^\d.]/',$k[$leixing]);
						$arr_count = count($arr)-1;
						if($jieguo>=floatval($arr[0])&&$jieguo<=floatval($arr[$arr_count]))
						{
							$p = 1;
						}
						break;
					case 'sn_jmd':case 'sj_jmd':case 'sn_xdwc':case 'sj_xdwc':
						//相对偏差
						preg_match('/[\d.]+/',$k[$leixing],$match);
						if($jieguo>=0&&$jieguo<=floatval($match[0]))
						{
							$p = 1;
						}
						break;
				}
				return ($p == 1)?'合格':'不合格';
			}
		}
	}
	if($f==0){
		// die(json_encode(array('error'=>'0','content'=>'质控范围未设置，没有进行质控判定！')));
	}
}
/**
 * 功能：修约质控结果
 * 作者：Mr Zhou
 * 日期：2014-04-29
 * 参数：float	$result	计算结果
 * 参数：int	 $type	结果值类型 P：平均 X：相对偏差 J：加标回收率
 * 参数：int	 $vid	项目id
 * 返回值：修约后的结果
 * 功能描述：根据配置条件，为传入的质控结果进行不同方式的修约和保留位数
*/
function Rounding_value($result,$vid=0,$type='X'){
	global $global;
	$weishu = $global['zk'][$type]['ws'];
	$result = ($result == '' || $result < 0) ? 0 : $result;

	/*********************************************************/
	$round = $global['zk'][$type]['xy'];
	$round = empty($round) ? '_round' : $round;
	/*********************************************************/

	//保留位数确认
	if($weishu == ''){
		if($result < 0){
			return false;
		}else if($result < 10){
			$weishu = 2;
		}else if($result >= 10 && $result < 100){
			$weishu = 1;
		}else if($result >= 100){
			$weishu = 0;
		}
	}
	return $round($result,$weishu);
}

/**
 * 功能：更新平均值,相对偏差及评价函数
 * 作者：Mr Zhou
 * 日期：2014-05-19
 * 参数：array	$jieguo	计算结果
 * 参数：int	$oid	原样id
 * 参数：int	$oid2	质控样id
 * 返回值：修约后的结果
 * 功能描述：更新平均值,相对偏差及评价函数
*/
function update_assay_order($jie_guo,$oid=0,$oid2=0){
	global $DB;
	if(intval($oid)){
		$DB->query("UPDATE `assay_order` SET `ping_jun`='{$jie_guo[0]}' WHERE `id`='{$oid}' ORDER BY `id` DESC LIMIT 1");
	}
	if(intval($oid2)){
		$DB->query("UPDATE `assay_order` SET `ping_jun`='{$jie_guo[0]}',`xiang_dui_pian_cha`='{$jie_guo[1]}',`ping_jia`='{$jie_guo[评价]}' WHERE `id`='{$oid2}' ORDER BY `id` DESC LIMIT 1");
	}
}