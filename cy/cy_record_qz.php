<?php
/**
  * 功能：采样记录表采样人签字和验收人签字保存
  * 作者：zhengsen
  * 时间：2014-04-22
**/

include "../temp/config.php";
if($u['userid'] == '') {
	nologin();
}
//通过id查询出cy这条数据
$cyd = get_cyd( $_GET['cyd_id'] );
if( $_GET['action'] == 'cy_user_qz' ){

    //获取化验员人数

  /*  if($cyd['status']<1) {
        prompt('你还没有接受任务！请先接受任务再来这儿签字');
        gotourl("cy_tzd.php?cyd_id=$_GET[cyd_id]&action=cy");
    }
// 采样人签字时，不一定全部都采样完成，所以要将采样完成的和未完成的  分批处理
	$queryC = $DB->query("select id,sid,cy_date,assay_values,zk_flag from `cy_rec` where cyd_id='".$_GET['cyd_id']."' and cy_time!='' and cy_time is not null and cy_time !='00:00:00'");
	$qz_site_vids=array();
	if(!mysql_num_rows($queryC)){
		gotourl('cy_record.php?cyd_id='.$_GET['cyd_id'],"请填写采样时间！");
	}
	while($rsC  = $DB->fetch_assoc($queryC)){
		if(!empty($rsC['assay_values'])){
			$qz_site_vid=explode(",",$rsC['assay_values']);
			$qz_site_vids=array_merge($qz_site_vid,$qz_site_vids);
		}
		if($rsC['zk_flag']=='-6'||in_array($rsC['zk_flag'],$global['xcpx_flag'])){
			$px_rs=$DB->fetch_one_assoc("select * from cy_rec where cyd_id='".$_GET['cyd_id']."' and sid='".$rsC['sid']."' and (cy_time='' or cy_time is null or cy_time='00:00:00')");
			if(!empty($px_rs)){
				gotourl('cy_record.php?cyd_id='.$_GET['cyd_id'],"平行样或者全程序空白不能被单独分为批次，请填写采样时间！");
			}
		}
		$qz_site_vids=array_unique($qz_site_vids);//已经签字站点的所有项目
		$rec_id[]=$rsC['id'];
		if($rsC['sid']!='-1'){
			$qz_site_arr[]= $rsC['sid'];
		}
	}
	if(count($qz_site_arr)==1&&in_array(0,$qz_site_arr)){
		gotourl('cy_record.php?cyd_id='.$_GET['cyd_id'],"平行样或者全程序空白不能被单独分为批次，请填写采样时间！");
	}
	//print_rr($qz_site_arr);exit();
	$rowC   = $DB->num_rows($queryC);
	$queryZ = $DB->query("select id from `cy_rec` where cyd_id='".$_GET['cyd_id']."'");
	$rowZ   = $DB->num_rows($queryZ);
	if($rowC!=$rowZ && $rowC!=0){//有且不是全部站点时
		$queryCy = $DB->query("select * from `cy` where id='".$_GET['cyd_id']."'");
		$rsCy    = $DB->fetch_assoc($queryCy);
		$cy_data_str= "";
		foreach($rsCy as $key=>$val){//需要修改新分批的cy表的数据时 在这里修改
			if($key=="sites"){
				$site_arr=explode(',',$val);
				$val     =implode(',',$qz_site_arr);
			}
			if($key!='id')$cy_data_str .= ",'".$val."'";
		}
		$site_diff=array_diff($site_arr,$qz_site_arr);
		if(empty($site_diff)){
			gotourl('cy_record.php?cyd_id='.$_GET['cyd_id'],"平行样或者全程序空白不能被单独分为批次，请填写采样时间！");
		}
		$site_diff_str=implode(',',$site_diff);
		//查询未签字站点的所有项目
		$rec_sql="select assay_values from cy_rec where cyd_id='".$_GET['cyd_id']."' and sid in (".$site_diff_str.")";
		$rec_query=$DB->query($rec_sql);
		$fp_site_vids=array();
		while($rec_rs=$DB->fetch_assoc($rec_query)){
			if(!empty($rec_rs['assay_values'])){
				$fp_site_vid=explode(",",$rec_rs['assay_values']);
				$fp_site_vids=array_merge($fp_site_vid,$fp_site_vids);
			}
			$fp_site_vids=array_unique($fp_site_vids);//分批出去站点的所有项目
		}
		$DB->query("insert into `cy` values(''".$cy_data_str.")");
		$newId      = $DB->insert_id();
		$qz_rec_id_str=implode(',',$rec_id);
		$DB->query("update `cy_rec` set cyd_id='".$newId."' where id in(".$qz_rec_id_str.") and cyd_id='".$_GET['cyd_id']."'");
		$idNum      = $DB->affected_rows();
		$DB->query("update assay_order set cyd_id='".$newId."' where cyd_id='".$_GET['cyd_id']."' and cid in (".$qz_rec_id_str.") ");
		$sql_pay="select * from assay_pay where cyd_id='".$_GET['cyd_id']."'";
		$query_pay=$DB->query($sql_pay);
		while($rs_pay=$DB->fetch_assoc($query_pay)){
			if(in_array($rs_pay['vid'],$qz_site_vids)){
				$pay_data_str='';
				foreach($rs_pay as $k2=>$v2){//需要修改新分批的cy表的数据时 在这里修改
					if($k2=="cyd_id"){
						$v2=$newId;
					}
					if($k2!='id')$pay_data_str .= ",'".$v2."'";
				}
				$DB->query("insert into `assay_pay` values(''".$pay_data_str.")");
				$tid= $DB->insert_id();
				$DB->query("update assay_order set tid='".$tid."' where cyd_id='".$newId."' and vid='".$rs_pay['vid']."' ");
			}
			if(!in_array($rs_pay['vid'],$fp_site_vids)&&!empty($fp_site_vids)){
				$DB->query("delete from assay_pay where id='".$rs_pay['id']."'");
			}
		}

		if($idNum!='0'){
			$DB->query("update `cy` set sites='".$site_diff_str."' where id='".$_GET['cyd_id']."'");
		}
		$_GET['cyd_id']=$newId;
	}
	if($rowC=='0'){
		gotourl('cy_record.php?cyd_id='.$_GET['cyd_id'],"请先填写采样数据,采样时间不能为空");
	}
	*/
	//采样接受人签字时更新cy的采样接收人和接受时间
	$query_cy = $DB->query("select * from cy where id ='".$_GET['cyd_id']."'");
	$rs_cy = $DB->fetch_assoc($query_cy);
	//如果采样人没有接受任务
	if(1==$rs_cy['status']){
		if($u['userid']==$rs_cy['cy_user']&!empty($rs_cy['cy_user'])){
			$cy_json_arr= json_decode($rs_cy['json'],true);
			$cy_json_arr['userid_img']['cy_rwjs_user']	= $u['userid_img'];//电子签名信息
			$cy_json	= JSON($cy_json_arr);
			$DB->query("update `cy` set `cy_rwjs_user`='".$u['userid']."', cy_rwjs_qz_date = curdate(), status='2',`json`='{$cy_json}' where id='".$_GET['cyd_id']."'");
		}
		if($u['userid']==$rs_cy['cy_user2']&!empty($rs_cy['cy_user2'])){
			$cy_json_arr= json_decode($rs_cy['json'],true);
			$cy_json_arr['userid_img']['cy_rwjs_user2']	= $u['userid_img'];//电子签名信息
			$cy_json	= JSON($cy_json_arr);
			if($rs_cy['cy_rwjs_user']){
				$DB->query("update `cy` set `cy_rwjs_user2`='".$u['userid']."',status='2',`json`='{$cy_json}' where id='".$_GET['cyd_id']."'");
			}else{
				$DB->query("update `cy` set `cy_rwjs_user2`='".$u['userid']."',cy_rwjs_qz_date = curdate(), status='2',`json`='{$cy_json}' where id='".$_GET['cyd_id']."'");
			}
		}
	}
	//更新cy表
	//通过id查询出cy这条数据
    $cyd = get_cyd( $_GET['cyd_id'] );
	if($cyd['status']<3){
		$status='3';
	}else{
		$status=$cyd['status'];
	}
	$assay_pay_user = '';
	if(($u['userid']==$rs_cy['cy_user']&!empty($rs_cy['cy_user'])) || $u['admin']){
		$cy_json_arr= json_decode($cyd['json'],true);
		$cy_json_arr['userid_img']['cy_user_qz']	= $u['userid_img'];//电子签名信息
		$cy_json	= JSON($cy_json_arr);
		$DB->query("update `cy` set `cy_user_qz`='".$u['userid']."',cy_user_qz_date = if(`cy_user_qz_date` != '0000-00-00' AND `cy_user_qz_date` != '' AND `cy_user_qz_date` IS NOT NULL,`cy_user_qz_date`,CURDATE()),status='".$status."',`json`='{$cy_json}' where `id`='".$_GET['cyd_id']."'");
		$assay_pay_user .= " ,`sign_01`='{$u['userid']}',`sign_date_01`=curdate() ";
	}
	if(($u['userid']==$rs_cy['cy_user2'] || $u['admin'])&!empty($rs_cy['cy_user2'])){
		$cy_json_arr= json_decode($cyd['json'],true);
		$cy_json_arr['userid_img']['cy_user_qz2']	= $u['userid_img'];//电子签名信息
		$cy_json	= JSON($cy_json_arr);
		if($rs_cy['cy_user_qz']){
			$DB->query("update `cy` set `cy_user_qz2`='".$u['userid']."',status='".$status."',`json`='{$cy_json}' where `id`='".$_GET['cyd_id']."'");
		$assay_pay_user .= " ,`sign_012`='{$u['userid']}'";
		}else{
			$DB->query("update `cy` set `cy_user_qz2`='".$u['userid']."',`cy_user_qz_date`=if(`cy_user_qz_date` != '0000-00-00' AND `cy_user_qz_date` != '' AND `cy_user_qz_date` IS NOT NULL,`cy_user_qz_date`,CURDATE()),status='".$status."',`json`='{$cy_json}' where `id`='".$_GET['cyd_id']."'");
		$assay_pay_user .= " ,`sign_012`='{$u['userid']}',`sign_date_012`=curdate() ";
		}
		
	}
	//处理现场检测项目的状态over,以便后边报告上获取数据
    $over       = '已完成';
   //签字后更新order表中，现场项目的状态。
	if($cyd['xc_exam_value']){
		$DB->query("UPDATE `assay_pay` SET `over`='{$over}' $assay_pay_user WHERE `cyd_id`='{$_GET['cyd_id']}' AND `is_xcjc`='1' ");
		$DB->query("update assay_order set assay_over = '1' where cyd_id ='".$_GET['cyd_id']."' and vid in (".$cyd['xc_exam_value'].")");
	}
}
//判断是否需要验收模块
if($yanshou_peizhi=='有验收'){
	$ys_status = '4';
}elseif($yanshou_peizhi=='没有验收'){
	$ys_status = '5';
}else{
	$ys_status = '5';
}
if($_GET['action']=='ypjs_user_qz'){
	if($cyd['status']>=$ys_status){
		$status=$cyd['status'];
	}else{
		$status=$ys_status;
	}
	$cy_json_arr= json_decode($cyd['json'],true);
	$cy_json_arr['userid_img']['sh_user_qz']	= $u['userid_img'];//电子签名信息
	$cy_json	= JSON($cy_json_arr);
    $DB->query("UPDATE cy SET sh_user_qz = '".$u['userid']."',status='".$status."', sh_user_qz_date = if(`sh_user_qz_date` != '' AND `sh_user_qz_date` IS NOT NULL,`sh_user_qz_date`,CURDATE()),`json`='{$cy_json}' WHERE id = '".$_GET['cyd_id']."'");
	//处理现场检测项目的状态over,以便后边报告上获取数据
    if(!empty($qzjb)){
        $over   = $qzjb;
    }else{
        $over   = '已审核';
    }
    $DB->query("UPDATE `assay_pay` SET `over`='{$over}' WHERE `cyd_id`='{$_GET['cyd_id']}' AND `is_xcjc`='1' ");
}
//gotourl($url[$_u_][1]);
gotourl("$rooturl/cy/cy_record.php?cyd_id=$_GET[cyd_id]");
?>
