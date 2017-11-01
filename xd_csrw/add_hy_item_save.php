<?php
/**
 * 功能：完成化验单项目的添加和删除
 * 作者：zhengsen
 * 时间：2014-07-07
**/
require_once '../temp/config.php';
require '../inc/filter.php';
require (INC_DIR.'cy_func.php');
if(!$u['userid']){
	nologin();
}

$fzx_id=$u['fzx_id'];
$cyd = get_cyd(get_int($_POST['cyd_id']));
//这里是所有的项目
$all_assay_value = get_all_assay_value($_POST['cyd_id'], $xianchang_item = false);

//$add是一个标示，表明是否进行了添加
$add=0;

if(!empty($_POST['vid'])){
	//取出不同父级水样类型的项目名称
	$jcbz_sql	= $DB->query("SELECT bz.vid,bz.value_C,bz.xz,n_set.module_value2 FROM `n_set` JOIN `assay_jcbz` AS bz ON n_set.id = bz.jcbz_bh_id WHERE  n_set.module_value3 = '1'");
	while($jcbz_rs = $DB->fetch_assoc($jcbz_sql)){
		$jcbz_data[$jcbz_rs['module_value2']][$jcbz_rs['vid']]['value_C'] = $jcbz_rs['value_C'];
		$jcbz_data[$jcbz_rs['module_value2']][$jcbz_rs['vid']]['xz'] = $jcbz_rs['xz'];
	}
	if($_POST['sub'] == '添加') {
		//循环每个站点要添加或删除的项目
		foreach($_POST['vid'] as $key=>$value){
			$no_item=array();
			$cy_rec = get_cy_rec_by_cid($key);
			if($cy_rec['zk_flag']=='-6'){
				$cy_rec['site_name']=$cy_rec['site_name'].'（平行）';
			}
			//获取最大水样类型
			if(!empty($cy_rec['water_type'])){
				$water_type_max=get_water_type_max($cy_rec['water_type'],$fzx_id);
			}else{
				$water_type_bh=substr($cy_rec['bar_code'],1,1);
				$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
				$water_type_max=get_water_type_max($water_type,$fzx_id);
			}
			//现在站点的项目
			$now_item=elementsToArray($cy_rec['assay_values']);//转化成数组
			//去掉可能重复的项目
			$vid_ary=array_unique($value);
			$vid_ary=array_diff($vid_ary,$now_item);
			//判断是不是质控项目
			$no_item_str='';
			if(in_array($cy_rec['zk_flag'],$global['qckb_flag']))
			{//全程序空白
				$no_item=array_diff($vid_ary,$all_assay_value);
				$vid_ary=array_intersect($vid_ary,$all_assay_value);
				if(!empty($no_item))
				{
					$no_item_arr=get_diff_lx_xm($no_item,$water_type_max,$fzx_id);//得到这种水样类型下的项目名称
					$no_item_str='该批中不存在项目（'.implode(',',$no_item_arr).'） ';
				}
			}elseif($cy_rec['zk_flag']=='-6'){
				//现场平行样
				$xcpx_rs=$DB->fetch_one_assoc("SELECT assay_values FROM cy_rec WHERE cyd_id='".$_POST['cyd_id']."' AND sid ='".$cy_rec['sid']."' AND zk_flag>0");
				$xcpx_value_arr=elementsToArray($xcpx_rs['assay_values']);
				if(!empty($vid_ary))
				{
					foreach($vid_ary as $key=>$value)
					{
						if(!in_array($value,$xcpx_value_arr))
						{
							$xcpx_no_item[]=$value;
							unset($vid_ary[$key]);
						}
					}
				}
				$xcpx_no_item_str='';
				if(!empty($xcpx_no_item))
				{
					$xcpx_no_item_arr=get_diff_lx_xm($xcpx_no_item,$water_type_max,$fzx_id);
					$xcpx_no_item_str='原样中不存在项目（'.implode(',',$xcpx_no_item_arr).'） ';
				}

			}
			//如果$vid_ary不为空转化为字符串
			$vid_ary_str='';
			if(!empty($vid_ary))
			{
				$add=$add+1;
				$vid_name_arr=get_diff_lx_xm($vid_ary,$water_type_max,$fzx_id);
				$vid_ary_str='成功添加项目（'.implode(',',$vid_name_arr).'） ';
			}
			//信息汇集
			if(!empty($no_item_str)){
				$message[$cy_rec['site_name']]['no_item']=$no_item_str;//该批中不存在的项目
			}
			if(!empty($vid_ary_str)){
				$message[$cy_rec['site_name']]['add_item']=$vid_ary_str;//成功添加的项目
			}
			if(!empty($xcpx_no_item_str)){
				$message[$cy_rec['site_name']]['xcpx_yy_no_itme']=$xcpx_no_item_str;//现场平行原样存在的项目
			}
			//调用添加项目的函数
			if(!empty($vid_ary))
			{
				add_vid_after_create_hyd($cy_rec, $vid_ary,$fzx_id,$jcbz_data);
			}
		}
	}
	if($_POST['sub'] == '删除') {
		$xcpx_item_arrs=array();
		//查询出删除前全程序空白的项目
		$qckb_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND sid='0'");
		if(!empty($qckb_rs['assay_values'])){
			$qckb_item_arr1=explode(',',$qckb_rs['assay_values']);
		}
		//循环每个站点要添加或删除的项目
		foreach($_POST['vid'] as $key=>$value){
			$cy_rec = get_cy_rec_by_cid($key);
			//获取最大水样类型
			if(!empty($cy_rec['water_type'])){
				$water_type_max=get_water_type_max($cy_rec['water_type'],$fzx_id);
			}else{
				$water_type_bh=substr($cy_rec['bar_code'],1,1);
				$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
				$water_type_max=get_water_type_max($water_type,$fzx_id);
			}
			//如果要删除项目的站点做了现场平行记录这个站点的平行样的项目，为了执行删除操作后，通过取差知道平行样删除了哪些项目
			if(in_array($cy_rec['zk_flag'],$global['xcpx_flag'])){
				$xcpx_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE sid='".$cy_rec['sid']."' AND zk_flag='-6'");
				if(!empty($xcpx_rs['assay_values'])){
					$xcpx_item_arrs[$xcpx_rs['id']]=explode(',',$xcpx_rs['assay_values']);
				}
			}
			$vid_ary=array_unique($value);
			$now_item = elementsToArray($cy_rec['assay_values']);
			$site_no_items=array_diff($vid_ary,$now_item);
			$vid_ary = array_intersect($vid_ary, $now_item);
			$site_no_items_str='';
			$vid_ary_str='';
			if(!empty($vid_ary))
			{
				$add=$add+1;
				$vid_name_arr=get_diff_lx_xm($vid_ary,$water_type_max,$fzx_id);
				$vid_ary_str='成功删除项目（'.implode(',',$vid_name_arr).'） ';
				remove_vid_after_create_hyd($cy_rec,$vid_ary);
			}
			if($cy_rec['zk_flag']=='-6'){
				$cy_rec['site_name']=$cy_rec['site_name'].'（平行）';
			}
			if(!empty($vid_ary_str)){
				$message[$cy_rec['site_name']]['del_item']=$vid_ary_str;
			}
		}
		//如果删除项目的站点中有现场平行样
		if(!empty($xcpx_item_arrs)){
			foreach($xcpx_item_arrs as $key=>$value){
				$xcpx_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE id='".$key."'");
				if(!empty($xcpx_rs['assay_values'])){
					$xcpx_vids=explode(',',$xcpx_rs['assay_values']);
					$xcpx_del_vids=array_diff($value,$xcpx_vids);
				}
				if(!empty($xcpx_del_vids)){
					$xcpx_rs['site_name']=$xcpx_rs['site_name'].'（平行）';
					$xcpx_del_vids_arr=get_diff_lx_xm($xcpx_del_vids,$water_type_max,$fzx_id);
					$message[$xcpx_rs['site_name']]['del_item']='成功删除项目（'.implode(',',$xcpx_del_vids_arr).'） ';
				}
			}
		}
		//如果这批任务做了全程序空白
		if($qckb_rs){
			//查询出删除项目后全程序空白的项目
			$qckb_item_arr2=array();
			$qckb_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND sid='0'");
			$water_type_bh=substr($qckb_rs['bar_code'],1,1);
			$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
			$water_type_max=get_water_type_max($water_type,$fzx_id);
			if(!empty($qckb_rs['assay_values'])){
				$qckb_item_arr2=explode(',',$qckb_rs['assay_values']);
			}
			if(!empty($qckb_item_arr1)){
				$qckb_del_vids=array_diff($qckb_item_arr1,$qckb_item_arr2);//取删除前全程序空白和删除后项目的差集，即是全程序空白实际删除的项目
				if(!empty($qckb_del_vids)){
					$qckb_del_vids_arr=get_diff_lx_xm($qckb_del_vids,$water_type_max,$fzx_id);
					$message[$qckb_rs['site_name']]['del_item']='成功删除项目（'.implode(',',$qckb_del_vids_arr).'） ';
				}
			}
		}
	}


}


//结果显示和跳转

	$result="";
	$string1="";
	$string2="";
	if(!empty($message)){
		foreach($message as $key=>$value){
			foreach($value as $key1=>$value1){
					$string1.=$value1;	
			}
			$string2=$key.' : '.$string1.'\n';
			$result.=$string2;
			unset($string1);
		}
	}
	//如果$add不为空那么证明有项目添加成功
	if(empty($_POST['action'])){
		$gotourl="window.history.go(-1);";
	}
	if($add>0){
		echo "<script>alert('操作成功！\\n$result');".$gotourl."</script>";
	}
	else{
		if(!empty($result)){
			echo "<script>alert('操作失败！\\n$result');".$gotourl."</script>";
		}else{
			echo "<script>".$gotourl."</script>";
		}
	}
/**
*功能：化验项目的删除
*作者：zhengsen
*时间：2014-07-07
**/
function remove_vid_after_create_hyd($cy_rec, $vid_ary) {
    global $DB,$global;
    $cyd_id = $cy_rec['cyd_id'];
    $sid = $cy_rec['sid'];
    $cid = $cy_rec['id'];
    $new_vid_ary = array_diff(elementsToArray($cy_rec['assay_values']), $vid_ary);
	//正常站点的删除，要判断全程序空白项目中是否存在，如果存在且删除正常站点该项目后没有其他站点存在，那么全程序空白的这个项目也要删除
	if($sid>0&&$cy_rec['zk_flag']!='-6'){
		$rec_qckb=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$cyd_id."' AND sid='0'");
		if($rec_qckb){
			if(!empty($rec_qckb['assay_values'])){
				$qckb_item=explode(',',$rec_qckb['assay_values']);
				$del_qckb_vids=array_intersect($vid_ary,$qckb_item);
				if(!empty($del_qckb_vids)){
					$item_arr=array();
					$rec_sql="SELECT * FROM `cy_rec` WHERE cyd_id='".$cyd_id."' AND sid>0 AND id!='".$cid."' AND zk_flag>=0";
					$rec_query=$DB->query($rec_sql);
					while($rec_rs=$DB->fetch_assoc($rec_query)){					
						if(!empty($rec_rs['assay_values'])){
							$temp_item_arr=explode(',',$rec_rs['assay_values']);
							$item_arr=array_unique(array_merge($temp_item_arr,$item_arr));
						}
					}
					foreach($del_qckb_vids as $k=>$v){
						if(in_array($v,$item_arr)){
							unset($del_qckb_vids[$k]);
						}
					}
					if(!empty($del_qckb_vids)){
						$new_qckb_values=array_diff($qckb_item,$del_qckb_vids);
						if(!empty($new_qckb_values)){
							$new_qckb_values_str=implode(',',$new_qckb_values);
						}else{
							$new_qckb_values_str='';
						}
						//如果室内平行项目不为空，要更新snpx_item字段，如果更新后snpx_item字段为空就要把zk_flag减少20
						if(!empty($rec_qckb['snpx_item'])){
								$snpx_item_arr=explode(',',$rec_qckb['snpx_item']);
								$snpx_item_arr=array_diff($snpx_item_arr,$vid_ary);
								if(empty($snpx_item_arr)){
									$rec_qckb['zk_flag']-=20;
									$snpx_item_str='';
								}else{
									$snpx_item_str=implode(',',$snpx_item_arr);
								}
								$DB->query("UPDATE cy_rec SET snpx_item = '".$snpx_item_str."' WHERE id ='".$rec_qckb['id']."'");

						}
						//如果加标回收项目不为空，要更新jbhs_item字段，如果更新后jbhs_item字段为空就要把zk_flag减少40
						if(!empty($rec_qckb['jbhs_item'])){
								$jbhs_item_arr=explode(',',$rec_qckb['jbhs_item']);
								$jbhs_item_arr=array_diff($jbhs_item_arr,$vid_ary);
								if(empty($jbhs_item_arr)){
									$rec_qckb['zk_flag']-=40;
									$jbhs_item_str='';
								}else{
									$jbhs_item_str=implode(',',$jbhs_item_arr);
								}
								$DB->query("UPDATE cy_rec SET jbhs_item = '".$jbhs_item_str."' WHERE id ='".$rec_qckb['id']."'");
						}
						$DB->query("UPDATE cy_rec SET assay_values = '".$new_qckb_values_str."',zk_flag='".$rec_qckb['zk_flag']."' WHERE id ='".$rec_qckb['id']."'");
						
					}

				}
			}
		}
		
	}
	//如果室内平行项目不为空，要更新snpx_item字段，如果更新后snpx_item字段为空就要把zk_flag减少20
	if(!empty($cy_rec['snpx_item'])){
			$snpx_item_arr=explode(',',$cy_rec['snpx_item']);
			$snpx_item_arr=array_diff($snpx_item_arr,$vid_ary);
			if(empty($snpx_item_arr)){
				$cy_rec['zk_flag']-=20;
				$snpx_item_str='';
			}else{
				$snpx_item_str=implode(',',$snpx_item_arr);
			}
			$DB->query("UPDATE cy_rec SET snpx_item = '".$snpx_item_str."' WHERE id ='".$cid."'");

	}
	//如果加标回收项目不为空，要更新jbhs_item字段，如果更新后jbhs_item字段为空就要把zk_flag减少40
	if(!empty($cy_rec['jbhs_item'])){
			$jbhs_item_arr=explode(',',$cy_rec['jbhs_item']);
			$jbhs_item_arr=array_diff($jbhs_item_arr,$vid_ary);
			if(empty($jbhs_item_arr)){
				$cy_rec['zk_flag']-=40;
				$jbhs_item_str='';
			}else{
				$jbhs_item_str=implode(',',$jbhs_item_arr);
			}
			$DB->query("UPDATE cy_rec SET jbhs_item = '".$jbhs_item_str."' WHERE id ='".$cid."'");
	}
	//如果站点做了现场平行要更新平行样的assay_values,jbhs_item,snpx_item,如果更新后assay_values为空就把原样的zk_flag删除5
	if(in_array($cy_rec['zk_flag'],$global['xcpx_flag'])){
		$xcpx_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$cy_rec['cyd_id']."' AND sid='".$cy_rec['sid']."' AND zk_flag=-6");
		if(!empty($xcpx_rs['assay_values'])){
			$xcpx_vid_arr=explode(',',$xcpx_rs['assay_values']);
		}else{
			$xcpx_vid_arr=array();
		}
		$assay_value_arr=array_diff($xcpx_vid_arr,$vid_ary);
		if(!empty($assay_value_arr)){
			$assay_value_str=implode(',',$assay_value_arr);
		}else{
			$assay_value_str='';
		}
		if(!empty($assay_value_str)){
			$DB->query("UPDATE cy_rec SET assay_values = '".$assay_value_str."' WHERE id ='".$xcpx_rs['id']."'");
		}
		//如果室内平行项目不为空，要更新snpx_item字段，如果更新后snpx_item字段为空就要把zk_flag减少20
		if(!empty($xcpx_rs['snpx_item'])){
				$snpx_item_arr=explode(',',$xcpx_rs['snpx_item']);
				$snpx_item_arr=array_intersect($snpx_item_arr,$assay_value_arr);
				if(empty($snpx_item_arr)){
					$snpx_item_str='';
				}else{
					$snpx_item_str=implode(',',$snpx_item_arr);
				}
				$DB->query("UPDATE cy_rec SET snpx_item = '".$snpx_item_str."' WHERE id ='".$xcpx_rs['id']."'");

		}
		//如果加标回收项目不为空，要更新jbhs_item字段，如果更新后jbhs_item字段为空就要把zk_flag减少40
		if(!empty($xcpx_rs['jbhs_item'])){
				$jbhs_item_arr=explode(',',$xcpx_rs['jbhs_item']);
				$jbhs_item_arr=array_intersect($jbhs_item_arr,$assay_value_arr);
				if(empty($jbhs_item_arr)){
					$jbhs_item_str='';
				}else{
					$jbhs_item_str=implode(',',$jbhs_item_arr);
				}
				$DB->query("UPDATE cy_rec SET jbhs_item = '".$jbhs_item_str."' WHERE id ='".$xcpx_rs['id']."'");
		}
		
	}
	//更新cy_rec表里的assay_values字段
    $assay_value_str =implode(',',$new_vid_ary);
    $DB->query("UPDATE cy_rec SET assay_values = '".$assay_value_str."',zk_flag='".$cy_rec['zk_flag']."' WHERE id ='".$cid."'");
	$cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$cyd_id."'");
	//如果已经生成了化验单才对assay_order表进行相关操作(未生成化验单时order表可能有现场数据)
	if($cy_rs['status']=='6' || !empty($cy_rs['xc_exam_value'])){
	//循环每个要删除的项目
		foreach($vid_ary as $key=>$value) {
			$vid=$value;
			$tid_rs=$DB->fetch_one_assoc("SELECT tid FROM assay_order WHERE cyd_id = '".$cyd_id."' AND vid = '".$vid."' AND sid='".$cy_rec['sid']."'");
			$tid=$tid_rs['tid'];//删除项目的化验单号
			//如果是全程序空白要删除两条空白
			if(in_array($cy_rec['zk_flag'],$global['qckb_flag'])){
				$DB->query("DELETE FROM assay_order WHERE cyd_id = '".$cyd_id."' AND sid IN (0,-1,-2) AND vid = '".$vid."'");
			}elseif($cy_rec['zk_flag']=='-6'){
				$DB->query("DELETE FROM assay_order WHERE cyd_id = '".$cyd_id."' AND sid = '".$sid."' AND vid = '".$vid."' AND cid ='".$cid."'");
			}else{
				$DB->query("DELETE FROM assay_order WHERE cyd_id = '".$cyd_id."' AND sid = '".$sid."'   AND vid = '".$vid."'");
				//查询assay_order表中是正常样品是否还有这个项目，如果没有就删除全程序空白的项目
				$order_rs=$DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE cyd_id='".$cyd_id."' AND vid='".$vid."' AND sid>0");
				if(!$order_rs){
					$is_qckb=$DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE cyd_id='".$cyd_id."' AND sid='0'");//查询要删除的项目在全程序空白中是否存在
					if(!empty($is_qckb)){
						$del_qckb=$DB->query("DELETE FROM assay_order WHERE cyd_id = '".$cyd_id."' AND sid IN (0,-1,-2)  AND vid = '".$vid."'");
					}
				}
			}
			//如果删除平行样的项目，要在assay_order表找到原样的这个项目然后hy_flag要减5
			if($cy_rec['zk_flag']=='-6')
			{
				$DB->query("UPDATE assay_order SET hy_flag=hy_flag-5 WHERE cyd_id='".$cyd_id."' AND sid='".$sid."' AND vid= '".$vid."' AND hy_flag>0");
			}
			$ppdel = 'yes';
			//如果该张化验单已经没有任何化验任务，删除这张化验单
			$res=$DB->query("SELECT * FROM assay_order WHERE tid = '".$tid."' AND cyd_id='".$cyd_id."'");
			while($prow = $DB->fetch_assoc($res)){
				$sidstr = ','.$cy_rs['sites'].',';
				$psid = ','.$prow['sid'].',';
				if(strstr($sidstr,$psid)){
					$ppdel = 'no';
				}
			}
			if($ppdel == 'yes') {
				$DB->query("DELETE FROM assay_pay WHERE id = '".$tid."'");
				$zhangshu = $cy_rs['hyd_count']-1; 
				$wczhangshu = $cy_rs['hyd_wc_count']-1;
				$DB->query("UPDATE `cy` SET `hyd_count`= '$zhangshu',`hyd_wc_count`='$wczhangshu' WHERE id='{$cyd_id}'");
				//判断是不是现场检测项目，如果是就删除cy表的xc_exam_value记录
				$xc_exam_arr	= array();
				if(!empty($cy_rs['xc_exam_value'])){
					$xc_exam_arr= explode(",",$cy_rs['xc_exam_value']);
				}
				if(in_array($vid,$xc_exam_arr)){
					unset($xc_exam_arr[array_search($vid,$xc_exam_arr)]);
					$xc_exam_str	= implode(",", $xc_exam_arr);
					$DB->query("UPDATE `cy` SET `xc_exam_value`='{$xc_exam_str}'  WHERE id='{$cyd_id}'");
				}
			}
		}
	}
}
/**
*功能：化验项目的添加
*作者：zhengsen
*时间：2014-07-07
**/
function add_vid_after_create_hyd($cy_rec, $vid_ary,$fzx_id,$jcbz_data) {
	global $DB,$global;
	$cyd_id = $cy_rec['cyd_id'];
    $cid = $cy_rec['id'];
	//查询出本中心的人
	$user_arr=array();
	$user_sql="SELECT * FROM users WHERE fzx_id='".$fzx_id."'";
	$user_query=$DB->query($user_sql);
	while($user_rs=$DB->fetch_assoc($user_query)){
		$user_arr[$user_rs['id']]=$user_rs['userid'];
	}
	//获取最大水样类型和整合查询条件
	if(!empty($cy_rec['water_type'])){
		$water_type=$cy_rec['water_type'];
		$water_type_max=get_water_type_max($cy_rec['water_type'],$fzx_id);//获得最大水样类型的函数
		if($cy_rec['water_type']==$water_type_max){
			$water_type_str='= '.$water_type_max;
		}else{
			$water_type_str='in ('.$water_type_max.','.$cy_rec['water_type'].')';
		}
	}else{
		$water_type_bh=substr($cy_rec['bar_code'],1,1);
		$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
		$water_type_max=get_water_type_max($water_type,$fzx_id);
		$water_type_str='='.$water_type_max;
	}
	$rec_value_arr=array();
	if(!empty($cy_rec['assay_values'])){
		$rec_value_arr=explode(',',$cy_rec['assay_values']);	
	}
	$new_assay_value=array_filter(array_unique(array_merge($rec_value_arr,$vid_ary)));//增加项目后cy_rec表assay_values的项目
	sort($new_assay_value);//升序排列
    $new_assay_value_str =implode(',',$new_assay_value);
	//增加的项目在assay_order表里的hy_flag
	if($cy_rec['zk_flag']<0){
		$hy_flag=$cy_rec['zk_flag'];//增加平行样项目的hy_flag
	}else{
		if(in_array($cy_rec['zk_flag'],$global['qckb_flag'])){
			$hy_flag=1;//增加全程序空白项目的hy_flag
		}else{
			$hy_flag=0;//增加正常样品项目的hy_flag
		}	
	}
	//更新cy_rec表里的assay_values
    $DB->query("UPDATE `cy_rec` SET assay_values = '".$new_assay_value_str."' WHERE id = '".$cid."'");
	//循环个项目向assay_order表里插入数据
	$cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$cyd_id."'");
	//如果已经生成了化验单才对assay_order表进行相关操作
	$xc_exam_value  = array_filter(explode(',',$cy_rs['xc_exam_value']));
	$xc_value_add   = array_intersect($vid_ary, $xc_exam_value);
	if($cy_rs['status']>=6 || !empty($xc_value_add)){
		foreach($vid_ary as $key=>$value){
			//查询在assay_order表里和这个项目和这个站点的水样类型是否有方法
			$order_sql = "SELECT tid FROM `assay_order` WHERE cyd_id = '".$cyd_id."' AND vid = '".$value."' AND water_type ='".$water_type."' ";
			$res = $DB->fetch_one_assoc($order_sql);
			//如果添加的是平行样的项目要给原样项目的hy_flag加5
			if($cy_rec['zk_flag']<0){
				$up_order_sql="UPDATE assay_order SET hy_flag=hy_flag+5 WHERE cyd_id='".$cyd_id."' AND vid='".$value."' AND sid='".$cy_rec['sid']."' AND hy_flag>=0";
				$DB->query($up_order_sql);
			}
			//如果增加的项目在order表里没有查询到数据
			if(!$res) {
				$rowData = array();
				$xmfa_rs= $DB->fetch_one_assoc("SELECT x.* , yq.yq_mingcheng,yq.yq_sbbianhao FROM  `xmfa` x  LEFT JOIN `yiqi` yq ON x.yiqi = yq.id AND x.fzx_id=yq.fzx_id WHERE  x.fzx_id='".$fzx_id."'  AND x.act='1' AND x.mr='1' AND x.lxid ".$water_type_str." AND x.xmid ='".$value."' ORDER BY lxid DESC");
				$pay_rs=$DB->fetch_one_assoc("SELECT id FROM `assay_pay` WHERE userid='".$xmfa_rs['userid']."' AND fid='".$xmfa_rs['fangfa']."' AND cyd_id='".$cyd_id."' AND vid='".$value."'");
				if(!$pay_rs){
					//查询项目使用的曲线
					$sc=$DB->fetch_one_assoc("SELECT id FROM standard_curve WHERE vid='".$value."' AND fzx_id='".$fzx_id."' AND status='正在使用' AND sign_01 is not null AND sign_01!='' ORDER BY td4 DESC");
					$assay_pay_info['cyd_id']	= $cyd_id;
					$assay_pay_info['fzx_id']	= $fzx_id;
					$assay_pay_info['scid']     = $sc['id'];
					$assay_pay_info['userid']   = $user_arr[$xmfa_rs['userid']];
					$assay_pay_info['userid2']  = $user_arr[$xmfa_rs['userid2']];
					$assay_pay_info['uid']		= $xmfa_rs['userid'];
					$assay_pay_info['uid2']		= $xmfa_rs['userid2'];
					$assay_pay_info['create_date']=date('Y-m-d H:i:s');
					$assay_pay_info['unit']     = $xmfa_rs['unit'];
					$assay_pay_info['td2']      = $xmfa_rs['method_number'];//方法标准号
					$assay_pay_info['td3']      = $xmfa_rs['jcx'];
					$assay_pay_info['td4']      = $xmfa_rs['yq_mingcheng'];
					$assay_pay_info['td5']      = $xmfa_rs['yq_sbbianhao'];
					$assay_pay_info['table_id']    = $xmfa_rs['hyd_bg_id'];
					$assay_pay_info['fid']		= $xmfa_rs['id'];
					$assay_pay_info['vid']		= $value;
					$assay_pay_info['fp_id']	= '1';
					if(!empty($jcbz_data[$water_type_max][$value]['value_C'])){
						$assay_pay_info['assay_element'] =$jcbz_data[$water_type_max][$value]['value_C'] ;
					}else{
						$assay_pay_info['assay_element'] =$_SESSION['assayvalueC'][$value];
					}				
					$assay_pay_info['jc_xz']		 =$jcbz_data[$water_type_max][$value]['xz'] ;
					$tid = new_record('assay_pay',$assay_pay_info);
					$zhangshu = $cy_rs['hyd_count']+1; 
					$DB->query("UPDATE `cy` SET `hyd_count`= '$zhangshu' WHERE id='{$cyd_id}'");
				}else{
					$tid=$pay_rs['id'];
				}
			}else {
				$tid = $res['tid'];//这是在assay_order表里查询到的tid
			}	 
			$rowData = array();

			$rowData['cyd_id'] = $cyd_id;
			$rowData['cid'] = $cid;
			$rowData['sid'] = $cy_rec['sid'];
			$rowData['river_name'] = $cy_rec['river_name'];
			$rowData['site_name'] = $cy_rec['site_name'];
			$rowData['create_date'] = date('Y-m-d');
			$rowData['bar_code'] = $cy_rec['bar_code'];
			$rowData['bar_code_position'] = $cy_rec['bar_code_position'];
			$rowData['water_type'] =$cy_rec['water_type'];
			$rowData['vid'] = $value;
			$rowData['hy_flag'] = $hy_flag;
			$rowData['tid'] = $tid;
			$rowData['assay_over'] = 'S';
			new_record('assay_order', $rowData);
			//如果添加的是全程序空白判断是否要添加两条室内空白
			if(in_array($cy_rec['zk_flag'],$global['qckb_flag']))
			{
				$rowData['hy_flag']=-2;
				$rowData['site_name']='空白1';
				$rowData['bar_code']='空白1';
				new_record('assay_order',$rowData);
				$rowData['site_name']='空白2';
				$rowData['bar_code']='空白2';
				new_record('assay_order',$rowData);
			}
		}
	}
}
 /* 
 * 功能：返回不同水样类型下的项目名称
 * 作者: zhengsen
 * 时间：2014-07-07
 */
 function get_diff_lx_xm($vids,$water_type_max,$fzx_id){
	 global $DB;
	//取出项目名称
	$xm_sql	= $DB->query("SELECT av.id,av.value_C FROM `xmfa` x JOIN `assay_value` av  ON x.xmid = av.id WHERE x.fzx_id='".$fzx_id."' AND x.mr='1' ");
	while($xm_rs = $DB->fetch_assoc($xm_sql)){
		$xm_data[$xm_rs['id']] = $xm_rs['value_C'];
	 }
	 if(!empty($vids)){
		if(is_array($vids)){
			$vids=array_unique($vids);
			foreach($vids as $key=>$value){
				$assay_C[]=$xm_data[$value];
			}
			return $assay_C;
			
		}else{
			$assay_C[]=$xm_data[$value]; 
			return $assay_C;
		}
	 }else{
		return false;
	}
 }
