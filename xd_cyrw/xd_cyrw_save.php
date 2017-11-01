<?php
/**
 * 功能：下达采样任务后台处理页面
 * 作者：韩枫
 * 日期：2014-05-26
 * 描述
*/
include("../temp/config.php");
include(INC_DIR."cy_func.php");
$fzx_id	= $u['fzx_id'];
$success_info="批任务下达成功";
//查询所有人员
$renarr = array();
$rensql = $DB->query("select id,userid from users where fzx_id='".$fzx_id."' and `group`<>'0'");
while($ren = $DB->fetch_assoc($rensql)){
	$renarr[$ren['userid']] = $ren['id'];
}
if(!empty($_POST['cyd_id'])){
	$success_info="批任务修改成功";
	$cy_rs	= $cy_rec_old	= $cy_rec_old_zk	= $cy_rec_sites	= array();
	$cy_rs	= $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'");
	$_POST['site_type']=$cy_rs['site_type'];
	$sql_cy_rec	= $DB->query("SELECT * FROM `cy_rec` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_cy_rec	= $DB->fetch_assoc($sql_cy_rec)) {
		if($rs_cy_rec['sid']>0){
			$cy_rec_sites[]	= $rs_cy_rec['sid'];
		}
		if($rs_cy_rec['zk_flag']>=0){
			$cy_rec_old[$rs_cy_rec['sid']]	= $rs_cy_rec;//批次修改之前正常站点及全程序空白的信息
		}else{
			$cy_rec_old_zk[$rs_cy_rec['sid']]	= $rs_cy_rec;//批次修改之前现场平行站点的信息
		}
	}
	$pay_old	= $order_old	= array();
	$sql_assay_pay	 = $DB->query("SELECT * FROM `assay_pay` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_assay_pay = $DB->fetch_assoc($sql_assay_pay)) {
		$pay_old[$rs_assay_pay['vid']]	= $rs_assay_pay;//不同方法时有可能会重复？？？
	}
	$sql_assay_order	= $DB->query("SELECT * FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_assay_order = $DB->fetch_assoc($sql_assay_order)) {
		$order_old[$rs_assay_order['cid']][$rs_assay_order['vid']]	= $rs_assay_order;
	}
	//如果是修改采样任务要把之前的任务删除掉重新生成新的一批
	//$DB->query("DELETE FROM `cy` WHERE id='".$_POST['cyd_id']."'");
	//$DB->query("DELETE FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."'");
	$DB->query("DELETE FROM `assay_pay` WHERE cyd_id='".$_POST['cyd_id']."' AND `is_xcjc`='1'");
	//$DB->query("DELETE FROM `assay_order` WHERE cyd_id='".$_POST['cyd_id']."'");
}
/*if($u['admin']=='1'){
	echo "<font color='red'>调试中...，admin账户会终止'下达采样任务'进程。</font>";
	print_rr($_POST);
	exit;
}*/
#########取出所有水样类型
$water_type_all	= array();
$sql_water_type	= $DB->query("SELECT * FROM `leixing` WHERE 1");
while($rs_water_type=$DB->fetch_assoc($sql_water_type)){
	$water_type_all[$rs_water_type['id']]	= $rs_water_type['parent_id'];
	$water_type_name_arr[$rs_water_type['id']]	= $rs_water_type['lname'];
}
#########取出现场平行项目
$xcpx_value     = $DB->fetch_one_assoc("SELECT module_value1 FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='xcpx_value' AND module_value2='{$_POST['site_type']}' ORDER BY id DESC LIMIT 1");
$xcpx_value_arr = @explode(',',$xcpx_value['module_value1']);
#########取出全程序空白项目
$qckb_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='qckb_value' and module_value2='{$_POST['site_type']}' order by id DESC limit 1");
$qckb_value_arr = @explode(',',$qckb_value['module_value1']);
//获取站点有没有多个垂线和层面
$sql_site_line_vertical		= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id' AND (`site_code`!='' OR `site_code` is not NULL) ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)){
	//奇怪，sql里面的为空限制不管用。这里只能再加一道关
	if($rs_site_line_vertical['site_code'] !=''){
			$site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
			//$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
	}
}

#####循环传过来的数组，将每个批次的数据插入cy表
$xy_i	= 0;//下达采样任务成功的批次数
foreach($_POST as $key=>$value){
	//区分出每个批次的信息
	$cy_info	 = $xcpx_sites	= $pay_vids = array();
	$all_assay_values= $water_type_arr = $xc_exam_arr = $xc_water_type = array();
	if(is_array($value)&&@array_key_exists('sites',$value)){
		//修改采样任务时，先将去掉的站点的cy_rec表的记录删除掉
		if(!empty($_POST['cyd_id'])){
			$old_sites	= array_diff($cy_rec_sites, $value['sites']);
			$old_sites_str	= implode(',', $old_sites);
			if($old_sites_str!=''){
				$DB->query("DELETE FROM `cy_rec` WHERE cyd_id='{$_POST['cyd_id']}' and sid in ($old_sites_str)");
				$DB->query("DELETE FROM `assay_order`	WHERE cyd_id='{$_POST['cyd_id']}' and sid in ($old_sites_str)");
			}
		}
		########生成记录到cy表
		$cy_info['site_type']	= $_POST['site_type'];//任务性质
		$cy_info['cy_flag']	= $_POST['cy_flag'];//是否是委托检测
		$cy_info['fzx_id']	= $fzx_id;//分中心id
		
		$cy_info['group_name']	= ($key=='jdrw' && $_POST['group_name']!='') ? $_POST['group_name'] : $key;//批名
		$cy_info['cy_user']	= ($_POST['cy_flag']=='1') ? $value['cy_user'] : "委托方";//采样人1
		$cy_info['cy_user2']	= ($_POST['cy_flag']=='1') ? $value['cy_user2']:"";//采样人2
		$cy_info['status']	= ($_POST['cy_flag']=='1') ? "0" : "4";//批次样品状态,如果是委托方送样，那状态直接到“样品接受”
		$cy_info['cy_date']	= $_POST['cy_riqi'];//采样日期
		$cy_info['create_date']	= date( 'Y-m-d' );//创建日期
		$cy_info['create_user'] = $u['userid'];//创建人
		$cy_info['sites']	= implode(",",$value['sites']);//站点集合
		$cy_info['xc_exam_value']= @implode(",",$_POST['xcjc_value']);//现场检测项目集合
		$cy_info['snkb']	= ($value['snkb']=='yes') ? 1 : 0;//是否同时检测室内空白
		//计算样品数量
		$cy_info['yp_count']	= count($value['sites']);
		if(!empty($value['xcpx'])){
			$cy_info['yp_count']	= $cy_info['yp_count']+count($value['xcpx']);
		}
		if(isset($value['qckb'])){
			$cy_info['yp_count']	= $cy_info['yp_count']+1;
		}
		//现场检测项目数组
		if(!empty($_POST['xcjc_value'])){
			$xc_exam_arr	= $_POST['xcjc_value'];
		}
		if(empty($_POST['cyd_id'])){
			$cy_info["cyd_bh"]	= new_cyd_bh($cy_info['site_type'],$cy_info['cy_date']);//生成采样单编号,函数在cy_func.php中
			$cyd_id = new_record( 'cy', $cy_info );//将数据插入cy表，函数在function.php中
			if(!$first_cyd_id){
				$first_cyd_id=$cyd_id;
			}
			if( !$cyd_id ) die( "生成采样单失败!" );
		}else{
		########修改采样单的时候只更新不生成
			//去除掉不需要更新的字段
			unset($cy_info["cyd_bh"]);
			unset($cy_info["status"]);
			unset($cy_info["create_date"]);
			unset($cy_info["create_user"]);
			$sum_cyd	= update_rec('cy', $cy_info,$_POST['cyd_id']);
			$cyd_id		= $_POST['cyd_id'];
			if(!$first_cyd_id){
				$first_cyd_id=$cyd_id;
			}
		}
		#########生成记录到cy_rec表
		$xcpx_sites	= $value['xcpx'];//检测现场平行的站点
		$where_sites	= '';
		if($key!='jdrw'){
			 $where_sites	.= "AND sg.group_name = '{$cy_info['group_name']}'";
		}
		//可以用gr_id
		if(!empty($value['gr_ids'])){
			$where_sites	.= "AND sg.id in (".implode(",",$value['gr_ids']).")";
		}
		$sql_siteArr	= $DB->query("
	SELECT s.id AS sid, s.river_name,s.site_code,s.site_line,s.site_vertical,s.site_name,s.water_type,sg.id, sg.assay_values, curdate() AS create_date,sg.xcpx_values,s.jingdu, s.weidu
	FROM `site_group` AS sg LEFT JOIN sites AS s ON s.id = sg.site_id
	WHERE sg.fzx_id='$fzx_id' $where_sites AND sg.`site_id` IN({$cy_info['sites']}) AND sg.act='1' ORDER BY s.sort,s.id");
		while($rs_siteArr = $DB->fetch_assoc($sql_siteArr)){
			//监督任务的项目可能是由页面设置的，如果设置过了，这里用设置的项目，不用数据库里存储的
			if(!empty($value['sites_value'][$rs_siteArr['id']])){
				$rs_siteArr['assay_values']	= $value['sites_value'][$rs_siteArr['id']];
			}
			unset($rs_siteArr['id']);
			//判断相同站码但水样类型不同的站点
			$line_vertical  = '';
			if(count($site_line_vertical[$rs_siteArr['site_code']])>1){
				//相同站码 不同水样类型的情况，如果需要提醒。就把下面代码的注释去掉
				//$line_vertical	.= "(".$water_type_name_arr[$rs_siteArr['water_type']].")";
			}
			//判断出该站点的垂线和层面
			if(count($site_line_vertical[$rs_siteArr['site_code']][$rs_siteArr['water_type']])>1){
				$str_site_line		=  $global['site_line'][$rs_siteArr['site_line']];
				$str_site_vertical	=  $global['site_vertical'][$rs_siteArr['site_vertical']];
				$line_vertical		.= "(".$str_site_line.$str_site_vertical.")";
			}
			$rs_siteArr['site_name'].= $line_vertical;
			unset($rs_siteArr['site_code']);
			unset($rs_siteArr['site_line']);
			unset($rs_siteArr['site_vertical']);
			unset($rs_siteArr['jingdu']);
			unset($rs_siteArr['weidu']);
			$cy_rec_info	= array();
			$xcpx_values	= $rs_siteArr['xcpx_values'];//现场平行样检测的项目
			unset($rs_siteArr['xcpx_values']);//向rec表添加数据的时候，不需要这个字段
			$cy_rec_info	= $rs_siteArr;//站点的基本信息
			$cy_rec_info['cyd_id']	= $cyd_id;//采样单id
			//委托任务时，将采样日期直接写入cy_rec表，方便后期数据获取
			if($cy_info['cy_flag'] != '1'){
				$cy_rec_info['cy_date']	= $cy_info['cy_date'];
			}
			$cy_rec_info['zk_flag']	= @in_array( $rs_siteArr['sid'],$xcpx_sites) ? 5 : 0;//质控标识
			//根据小类查出大类
			$fater_water	= $cy_rec_info['water_type'];
			/*if(!array_key_exists($fater_water,$global['bar_code']['water_type'])){
				if($water_type_all[$fater_water]!='0'){
					$fater_water	= $water_type_all[$fater_water];
				}else{
					echo "<script>alert('请联系管理员：水样类型$fater_water没有配置样品编号');</script>";
				}
			}*/
			$cy_rec_info['bar_code']= '';//生成样品编号,函数在cy_func.php中
			//统计出一共有集中 水样类型并存到数组中
			if(!in_array($rs_siteArr['water_type'],$water_type_arr)){
				$water_type_arr[]       = $rs_siteArr['water_type'];
			}
			//将默认采样容器瓶数写入采样rec表的json字段
			$rq_sql		= "SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
			$rq_query	= $DB->query($rq_sql);
			$tmp_rec_json	= array();
			if(!empty($cy_rec_old[$cy_rec_info['sid']]['json'])){
				$tmp_rec_json	= json_decode($cy_rec_old[$cy_rec_info['sid']]['json'],true);
				if($tmp_rec_json['rq']){
					unset($tmp_rec_json['rq']);//清除历史容器信息
				}
			}
			$avarr		= explode(',',$cy_rec_info['assay_values']);
			while($rq_rs=$DB->fetch_assoc($rq_query)){
				$rq_value=explode(',',$rq_rs['vid']);
				if(array_intersect($avarr,$rq_value)){
					$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
				}
			}
			$cy_rec_info['json']= JSON($tmp_rec_json);
			//采样容器存储结束
			if(empty($_POST['cyd_id']) || empty($cy_rec_old[$cy_rec_info['sid']]['id'])){
				$cid	= new_record('cy_rec', $cy_rec_info);//将数据插入cy_rec表，函数在function.php中
			}else{
				$cid	= $cy_rec_old[$cy_rec_info['sid']]['id'];
				//unset($cy_rec_info['bar_code']);
				$cy_rec_info['bar_code']	= $cy_rec_old[$cy_rec_info['sid']]['bar_code'];
				$update_cy_rec_info	= $cy_rec_info;
				unset($update_cy_rec_info['create_date']);
				unset($update_cy_rec_info['create_man']);
				update_rec('cy_rec', $update_cy_rec_info,$cid);
			}
			$assay_values_arr       = explode(',',$cy_rec_info['assay_values']);//此站点所测的项目（数组）
			//获得一批中所有的项目(后面取交集用)
			$all_assay_values       = array_unique(array_merge($all_assay_values,$assay_values_arr));
			##########现场平行站点的处理
			if($cy_rec_info['zk_flag'] == 5){
				$yuan_cid	= $cid;//原样的cid
				$xcpx_bar_code	= $cy_rec_info['bar_code'];//记录现场平行原样的样品编号，后面现场项目插入order表用
				$cy_rec_info['bar_code']= '';//现场平行样的新样品编号
				$cy_rec_info['zk_flag']	= '-6';//现场平行质控标识
				$rs_siteArr['assay_values']     = explode(",",$rs_siteArr['assay_values']);
				if(empty($xcpx_values)){
					$cy_rec_info['assay_values']	= implode(",",array_intersect($rs_siteArr['assay_values'],$xcpx_value_arr));//该现场平行样检测的项目（取交集的形式）
				}else{
					$xcpx_values_arr	= explode(",",$xcpx_values);
					$cy_rec_info['assay_values']	= implode(",",array_intersect($xcpx_values_arr,$rs_siteArr['assay_values'],$xcpx_value_arr));;//该现场平行样检测的项目(批次表里存储的)
				}
				//将默认采样容器瓶数写入采样rec表的json字段
				$rq_sql		= "SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
				$rq_query	= $DB->query($rq_sql);
				$tmp_rec_json	= array();
				if(!empty($cy_rec_old_zk[$cy_rec_info['sid']]['json'])){
					$tmp_rec_json	= json_decode($cy_rec_old_zk[$cy_rec_info['sid']]['json'],true);
					if($tmp_rec_json['rq']){
						unset($tmp_rec_json['rq']);//清除历史容器信息
					}
				}
				$avarr		= explode(',',$cy_rec_info['assay_values']);
				while($rq_rs=$DB->fetch_assoc($rq_query)){
					$rq_value=explode(',',$rq_rs['vid']);
					if(array_intersect($avarr,$rq_value)){
						$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
					}
				}
				$cy_rec_info['json'] = JSON($tmp_rec_json);
				//采样容器存储结束
				if(empty($_POST['cyd_id']) || empty($cy_rec_old_zk[$cy_rec_info['sid']]['id'])){
					$cid	= new_record( 'cy_rec', $cy_rec_info);
				}else{
					$cid	= $cy_rec_old_zk[$cy_rec_info['sid']]['id'];
					$cy_rec_info['bar_code']= $cy_rec_old_zk[$cy_rec_info['sid']]['bar_code'];
					$update_cy_rec_info		= $cy_rec_info;
					unset($update_cy_rec_info['create_date']);
					unset($update_cy_rec_info['create_man']);
					update_rec('cy_rec', $update_cy_rec_info,$cid);

				}
			}
			//修改采样批次时，去除修改之前选择但现在去掉的现场平行样
			if(!empty($_POST['cyd_id']) && $cy_rec_info['zk_flag'] == 0 && !empty($cy_rec_old_zk[$cy_rec_info['sid']]['id'])){
				$DB->query("DELETE FROM `cy_rec` where id='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");
				//$DB->query("DELETE FROM `assay_pay` where id='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");
				$DB->query("DELETE FROM `assay_order` where cid='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");//原样的质控标识在后面回进行覆盖
			}
			#########现场检测项目的处理:往assay_order表里插入数据
			//采样批次修改时先去掉无关的order表记录
			if(!empty($_POST['cyd_id'])){
				//去掉被取消选择的现场检测项目
				$old_xc_value	= explode(",",$cy_rs['xc_exam_value']);
				$new_xc_value	= explode(",",$cy_info['xc_exam_value']);
				$del_xc_value	= implode(",",array_diff($old_xc_value, $new_xc_value));
				if($del_xc_value!=''){
					$DB->query("DELETE FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}' AND vid in ({$del_xc_value})");
				}
				//再去掉站点里取消选择的现场检测项目
				if($cy_rec_info['zk_flag']	== '-6'){
					$old_cid	= $cy_rec_old_zk[$cy_rec_info['sid']]['id'];
					$old_vid	= explode(",",$cy_rec_old_zk[$cy_rec_info['sid']]['assay_values']);
					$old_sites_values_str	= implode(",",array_diff($old_vid,$assay_values_arr));
					if($old_sites_values_str!=''){
						//现场平行的原样处理
						$DB->query("DELETE FROM `assay_order` WHERE cid='{$old_cid}' and vid in ({$old_sites_values_str})");
					}
				}
				$old_cid	= $cy_rec_old[$cy_rec_info['sid']]['id'];
				$old_vid	= explode(",",$cy_rec_old[$cy_rec_info['sid']]['assay_values']);
				$old_sites_values_str	= implode(",",array_diff($old_vid,$assay_values_arr));
				if($old_sites_values_str!=''){
					//现场平行的平行样处理
					$DB->query("DELETE FROM `assay_order` WHERE cid='{$old_cid}' and vid in ({$old_sites_values_str})");
				}
			}
			if(!empty($cy_info['xc_exam_value'])){
				$insert_order	= array();
				$insert_order['cyd_id']		= $cy_rec_info['cyd_id'];//采样单id
				$insert_order['cid']		= $cid;//cy_rec表id
				$insert_order['sid']		= $cy_rec_info['sid'];//站点表id
				$insert_order['water_type']	= $cy_rec_info['water_type'];//水样类型
				$insert_order['site_name']	= $cy_rec_info['site_name'];//站点名称
				$insert_order['river_name']	= $cy_rec_info['river_name'];//河流名称
				$insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
				$insert_order['assay_over']	= '0';//化验单某样品完成状态
				$insert_order['create_date']= $cy_rec_info['create_date'];//化验单创建时间
				//将所有的现场检测项目插入到assay_order表
				$assay_values	= array_intersect($assay_values_arr,$xc_exam_arr);//取出这个站点所测的现场检测项目（取交集）
				if(empty($xc_water_type[$insert_order['water_type']])){
					$xc_water_type[$insert_order['water_type']]	= array();
				}
				$xc_water_type[$insert_order['water_type']]	= array_unique(array_merge($assay_values,$xc_water_type[$insert_order['water_type']]));
				foreach($assay_values as $vid){
					$insert_order['vid']    = $vid;//化验项目id
					$xcpx_value_arr_rec	= explode(",",$cy_rec_info['assay_values']);//站点所测的项目
					if($cy_rec_info['zk_flag'] == '-6' && !in_array($vid,$xcpx_value_arr_rec)){
						$insert_order['cid']		= $yuan_cid;
						$insert_order['hy_flag']	= '0';//质控标识
						$insert_order['bar_code']	= $xcpx_bar_code;//现场平行原样的样品编号
						
					}else if($cy_rec_info['zk_flag'] == '-6'){
						$insert_order['hy_flag']	= '-6';//现场平行样的hy_flag
						$insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
						$insert_order['cid']		= $cid;//cy_rec表id
						//修改采样任务时，现场平行样的修改
						if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
							new_record('assay_order' ,$insert_order);//将现场平行样信息插入到assay_order表
						}else{
							$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
							$update_order	= $insert_order;
							unset($update_order['bar_code']);
							unset($update_order['create_date']);
							unset($update_order['cyd_id']);
							unset($update_order['vid']);
							unset($update_order['sid']);
							update_rec('assay_order', $update_order,$order_id);
						}
						$insert_order['hy_flag']	= '5';//现场平行原样的hy_flag
						$insert_order['cid']		= $yuan_cid;
						$insert_order['bar_code']	= $xcpx_bar_code;//现场平行原样的样品编号
					}else{
						//正常的标识恢复
						$insert_order['hy_flag']	= $cy_rec_info['zk_flag'];//质控标识
					}
					//正常样的修改
					if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
						new_record('assay_order' ,$insert_order);//将信息插入到assay_order表
					}else{
						$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
						$update_order	= $insert_order;
						unset($update_order['bar_code']);
						unset($update_order['create_date']);
						unset($update_order['cyd_id']);
						unset($update_order['vid']);
						unset($update_order['sid']);
						update_rec('assay_order', $update_order,$order_id);
					}
				}
			}
		}
		##########全程序空白样的处理
		if(isset($value['qckb'])){
			if($value['qckb']==''){
				$cy_info['qckb_item']   = array_intersect($all_assay_values,$qckb_value_arr);//该批次全程序空白样的检测项目（取交集的形式）
				$cy_info['qckb_item']	= @implode(",",$cy_info['qckb_item']);
			}else{
				$qckb_values_arr		= explode(",",$value['qckb']);
				$cy_info['qckb_item']   = implode(",",array_intersect($qckb_values_arr,$all_assay_values,$qckb_value_arr));//该批次全程序空白样的检测项目(随着表单提交的)
			}
			//去除全程序空白修改后不测的项目
			if(!empty($_POST['cyd_id'])){
				$old_cid	= $cy_rec_old[0]['id'];
				$old_vid	= explode(",",$cy_rec_old[0]['assay_values']);
				$old_sites_values_str	= implode(",",array_diff($old_vid,explode(",",$cy_info['qckb_item'])));
				if($old_sites_values_str!=''){
					$DB->query("DELETE FROM `assay_order` WHERE cid='{$old_cid}' and vid in ({$old_sites_values_str})");
				}
			}
			$last_water_type= $cy_rec_info['water_type'];//上一个样品的水样类型，全程序空白生成编号时用
			/*if(!array_key_exists($last_water_type,$global['bar_code']['water_type'])){
					if($water_type_all[$last_water_type]!='0'){
							$last_water_type    = $water_type_all[$last_water_type];
					}
			}*/
			$cy_rec_info	= array();
			$cy_rec_info['bar_code']= '';//全程空白样的新样品编号  全程序空白没有水样类型 按照上一个样品的水样类型来编号
			$cy_rec_info['cyd_id']		= $cyd_id;
			$cy_rec_info['zk_flag'] 	= '1';
			$cy_rec_info['sid']			= '0';//全程序空白样品的 sid规定为0
			$cy_rec_info['river_name']	= '质控';
			$cy_rec_info['site_name']	= '全程序空白';
			$cy_rec_info['water_type']	= '';//全程序空白数据库中的水样类型应该存为空
			$cy_rec_info['assay_values']= $cy_info['qckb_item'];
			$cy_rec_info['create_date']	= date( 'Y-m-d' );
			//将默认采样容器瓶数写入采样rec表的json字段
			$rq_sql="SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
			$rq_query=$DB->query($rq_sql);
			$tmp_rec_json	= array();
			if(!empty($cy_rec_old[$cy_rec_info['sid']]['json'])){
				$tmp_rec_json	= json_decode($cy_rec_old[$cy_rec_info['sid']]['json'],true);
				if($tmp_rec_json['rq']){
					unset($tmp_rec_json['rq']);//清除历史容器信息
				}
			}
			$avarr = explode(',',$cy_rec_info['assay_values']);
			while($rq_rs=$DB->fetch_assoc($rq_query)){
				$rq_value=explode(',',$rq_rs['vid']);
				if(array_intersect($avarr,$rq_value)){  
					$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
				}
			}
			$cy_rec_info['json'] = JSON($tmp_rec_json);
			//采样容器存储结束
			if(empty($cy_rec_old[$cy_rec_info['sid']]['id'])){
				$cid 	= new_record( 'cy_rec', $cy_rec_info );
			}else{
				$cid	= $cy_rec_old[$cy_rec_info['sid']]['id'];
				//unset($cy_rec_info['bar_code']);
				$cy_rec_info['bar_code']= $cy_rec_old[$cy_rec_info['sid']]['bar_code'];
				$update_cy_rec_info		= $cy_rec_info;
				unset($update_cy_rec_info['create_date']);
				unset($update_cy_rec_info['create_man']);
				update_rec( 'cy_rec', $update_cy_rec_info,$cid);
			}
			//将全程序空白的现场检测项目插入到assay_order表
			if(!empty($cy_info['xc_exam_value'])){
				$insert_order   = array();
				$insert_order['cyd_id'] 	= $cy_rec_info['cyd_id'];//采样单id
				$insert_order['cid']		= $cid;//cy_rec表id
				$insert_order['sid']		= $cy_rec_info['sid'];//站点表id
				$insert_order['water_type']	= $cy_rec_info['water_type'];//水样类型
				$insert_order['hy_flag']	= $cy_rec_info['zk_flag'];//质控标识
				$insert_order['site_name']	= $cy_rec_info['site_name'];//站点名称
				$insert_order['river_name']	= $cy_rec_info['river_name'];//河流名称
				$insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
				$insert_order['assay_over']	= '0';//化验单某样品完成状态
				$insert_order['create_date']= $cy_rec_info['create_date'];//化验单创建时间
				//将所有的现场检测项目插入到assay_order表
				$qckb_values	= explode(',',$cy_rec_info['assay_values']);//此批次全程序空白样所测项目
				$assay_values	= array_intersect($xc_exam_arr,$qckb_values);//取出全程序空白样所测的现场检测项目
				foreach($assay_values as $vid){
					$insert_order['vid']    = $vid;//化验项目id
					if(empty($order_old[$cid][$vid])){
						new_record('assay_order' ,$insert_order);//将信息插入到assay_order表
					}else{
						$order_id	= $order_old[$cid][$vid]['id'];
						//unset($insert_order['bar_code']);
						$update_order	= $insert_order;
						unset($update_order['create_date']);
						unset($update_order['cyd_id']);
						unset($update_order['vid']);
						update_rec('assay_order', $update_order,$order_id);
					}
				}
			}
		}else if(!empty($_POST['cyd_id']) && !empty($cy_rec_old[0]['id'])){
			$DB->query("DELETE FROM `cy_rec` where id='{$cy_rec_old[0]['id']}'");
			//$DB->query("DELETE FROM `assay_pay` where id='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");
			$DB->query("DELETE FROM `assay_order` where cid='{$cy_rec_old[0]['id']}'");
		}
		###############查询出需要插入assay_pay表的现场检测项目的所有信息并插入到assay_pay表中
		$water_types	= implode(',',$water_type_arr);
		$xc_exam_arr	= array_intersect($all_assay_values,$xc_exam_arr);//所选现场检测项目与所有站点项目取交集，去掉站点不测却又被选中的现场检测项目
		$cy_info['xc_exam_value']	= implode(",",$xc_exam_arr);
		if(!empty($cy_info['xc_exam_value'])){
			//取出水样类型的大类和小类
			$water_type_fenlei	= array();
			$sql_leixing		= $DB->query("SELECT * FROM `leixing` WHERE (fzx_id='$fzx_id' OR `parent_id`='0') AND act='1' AND id in($water_types)");
			while($rs_leixing = $DB->fetch_assoc($sql_leixing)){
				if($rs_leixing['parent_id']=='0'){
					$water_type_fenlei[$rs_leixing['id']]	= $rs_leixing['id'];
				}else{
					$water_type_fenlei[$rs_leixing['id']]   = $rs_leixing['parent_id'];
				}
			}
			$insert_pay	= $values_name	= array();
			$insert_pay['cyd_id']		= $cy_rec_info['cyd_id'];
			$insert_pay['fzx_id']		= $fzx_id;
			$insert_pay['fp_id']		= $fzx_id;
			$insert_pay['create_date']	= $cy_rec_info['create_date'];
			$insert_pay['is_xcjc']		= '1';
			//取出不同水样类型下的项目名称
			$where_water_type	= implode(",",$water_type_fenlei);
			$sql_pay_value	= $DB->query("SELECT bz.vid,bz.value_C,n_set.module_value2 FROM `n_set` INNER JOIN `assay_jcbz` AS bz ON n_set.id = bz.jcbz_bh_id
									  WHERE n_set.module_name='jcbz_bh' AND n_set.module_value2 in ($where_water_type) AND n_set.module_value3 = '1' AND bz.vid IN ({$cy_info['xc_exam_value']})");
			while($rs_pay_value = $DB->fetch_assoc($sql_pay_value)){
					//格式：arr[vid][water_type]=>项目名
					if(empty($rs_pay_value['value_C'])){//如果标准里没有此项目名就到session中去找
						$rs_pay_value['value_C']	= $_SESSION['assayvalueC'][$rs_pay_value['vid']];
					}
					$values_name[$rs_pay_value['vid']][$rs_pay_value['module_value2']] = $rs_pay_value['value_C'];
			}
			$values_fangfa  = array();
			foreach($xc_water_type as $key_water_type=>$value_vids){
				foreach($value_vids as $value_vid){
					//取出方法表配置的一些信息
					//$values_fangfa	= array();
					$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id
												WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='$key_water_type' AND xmfa.xmid='$value_vid' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
					$num_pay_xmfa	= $DB->num_rows($sql_pay_xmfa);
					//如果小类中没有方法，就取大类中的方法
					if($num_pay_xmfa<1){
						$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='{$water_type_fenlei[$key_water_type]}' AND xmfa.xmid='$value_vid' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
					}
					while($rs_pay_xmfa = $DB->fetch_assoc($sql_pay_xmfa)){
						$insert_pay['vid']	= $rs_pay_xmfa['xmid'];
						//如果标准中没有此项目的名称，就到session中去找
						if(!empty($values_name[$rs_pay_xmfa['xmid']][$water_type_fenlei[$rs_pay_xmfa['lxid']]])){
							$insert_pay['assay_element']	= $values_name[$rs_pay_xmfa['xmid']][$water_type_fenlei[$rs_pay_xmfa['lxid']]];
						}else{
							$insert_pay['assay_element']	= $_SESSION['assayvalueC'][$rs_pay_xmfa['xmid']];
						}
						$insert_pay['userid']   = $cy_info['cy_user'];
						$insert_pay['userid2']  = $cy_info['cy_user2'];
						$insert_pay['uid']   = $renarr[$cy_info['cy_user']];
						$insert_pay['uid2']  = $renarr[$cy_info['cy_user2']];
						$insert_pay['unit']     = $rs_pay_xmfa['unit'];
						$insert_pay['td2']      = $rs_pay_xmfa['method_number'];//$rs_pay_xmfa['fangfa'];
						$insert_pay['td3']      = $rs_pay_xmfa['jcx'];
						$insert_pay['td4']      = $rs_pay_xmfa['yq_mingcheng'];
						$insert_pay['td5']      = $rs_pay_xmfa['yq_sbbianhao'];
						$insert_pay['table_id'] = $rs_pay_xmfa['hyd_bg_id'];
						$insert_pay['fid']		= $rs_pay_xmfa['id'];//项目法表的id
						if(!@array_key_exists($rs_pay_xmfa['fangfa'],$values_fangfa[$rs_pay_xmfa['xmid']])){
							$tid    = new_record('assay_pay' ,$insert_pay);//将信息插入到assay_pay表
							$values_fangfa[$rs_pay_xmfa['xmid']][$rs_pay_xmfa['fangfa']]    = $tid;
						}
						$tid	= $values_fangfa[$rs_pay_xmfa['xmid']][$rs_pay_xmfa['fangfa']];
						//全程序空白没有水样类型，默认都更新它
						$DB->query("UPDATE `assay_order` SET tid='$tid' WHERE cyd_id='{$insert_pay['cyd_id']}' AND vid='{$insert_pay['vid']}' AND (water_type='$key_water_type' OR hy_flag='1')");
					}
				}
			}
		}
		//将统计的 水样类型 插入到cy表里
		$DB->query("UPDATE `cy` SET water_type='$water_types',`xc_exam_value`='{$cy_info['xc_exam_value']}' WHERE id='$cyd_id'");
		$xy_i++;
		##################委托任务将 客户信息插入 客户管理表中
		if($_POST['site_type'] == '3' && $_POST['new_kehu'] == 'yes'){
			//检查客户信息是否存在
			$kehu_name	= trim($key);//批次名称，也是委托单位
			$old_kehu	= $DB->fetch_one_assoc("SELECT id,act FROM `kehu` WHERE `name`='{$kehu_name}'");
			if(empty($old_kehu['id'])){
				//插入委托客户的信息
				$insert_kehu	= $DB->query("INSERT INTO `kehu` SET `name`='{$kehu_name}',`act`='1' ");
				$old_kehu		= array();
				$old_kehu['id']	= $DB->insert_id();
			}else if($old_kehu['act'] == '0'){
				//将隐藏的 客户信息改为显示状态
				$DB->query("UPDATE `kehu` SET `act`='1' WHERE `id`='{$old_kehu['id']}'");
			}
			//检查本批次有没有对应信息，如果没有就插入
			$kid		= $old_kehu['id'];//kehu表id
			$wt_date	= $_POST['cy_riqi'];//委托日期
			$wt_sites	= implode(',',$value['sites']);//本次委托站点信息
			$wt_group	= $kehu_name;//批次名称
			if($_POST['cy_flag'] == 1){
				$wt_cyfs= '采样';
			}else{
				$wt_cyfs= '送样';
			}
			//插入本次委托信息
			$DB->query("INSERT INTO `kehu_wt` SET `kid`='{$kid}',`wt_date`='{$wt_date}',`sites`='{$wt_sites}',`cyfs`='{$wt_cyfs}',`group_name`='{$wt_group}',`act`='1' ");
		}
	}
}
if($xy_i>0){
	echo "<script>alert('{$xy_i}{$success_info}');location.href='$rooturl/cy/modi_csrw_tzd.php?cyd_id={$first_cyd_id}';</script>";
}else{
	echo "<script>alert('未选择站点');location.href='$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_POST['site_type']}';</script>";
}
?>
