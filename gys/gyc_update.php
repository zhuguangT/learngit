<?php
include "../temp/config.php";
$fzx_id	= $u['fzx_id'];
$id	= trim($_POST["id"]);
$wpname	= trim($_POST["wpname"]);//所购物品名称
$sname	= trim($_POST["sname"]);//供应商公司
$dz		= trim($_POST["dz"]);//地址
$lxr	= trim($_POST["lxr"]);//联系人
$lxdh	= trim($_POST["lxdh"]);//电话
$pjr	= trim($_POST["pjr"]);//评价人 
$pjbh	= trim($_POST["pjbh"]);//编号
$cpzl	= trim($_POST["cpzl"]);//产品质量
$fuwu	= trim($_POST["fuwu"]);//服务
$xinyu	= trim($_POST["xinyu"]);//信誉
$jiage	= trim($_POST["jiage"]);//价格
// $fujian	= trim($_POST["fujian"]);//附件
$swdjz=trim($_POST['swdjz']);//税务登记证
$zzjgdm=trim($_POST['zzjgdm']);//组织机构代码
$dqdate=trim($_POST['dqdate']);//到期时间
$yyzz=trim($_POST['yyzz']);//营业执照颁发日期
 $gy_fwly=trim($_POST['gy_fwly']);//供应服务领域
 if($gy_fwly=='请选择'){
	 $gy_fwly='';
 }
 $first_dengji_time=$_POST['first_dengji_time'];//初次登记时间
 $dengji_name=trim($_POST['dengji_name']);//登记人
 $gs_xinyong=trim($_POST['gs_xinyong']);//公司信用代码
$times=$_POST['times'];
//id不为空 可以更新该年的评价表
if(!empty($_FILES['fujian']['name'])){
	//处理
	$newname=array();
	foreach($_FILES['fujian']['name'] as $key=>$value){
		if(!empty($_FILES['fujian']['name'])){
			$xxx     = explode('.',$_FILES['fujian']['name'][$key]);
			$cnt     = count($xxx);
			$newname[] = date(ymdhis).$xxx[0].".".$xxx[$cnt-1];
			foreach($newname as $k=>$v){
				 $path    = "./upfiles/".$v;
			}
			if(file_exists($_FILES['fujian']['tmp_name'][$key])){//判断上传的文件是否存在
				if(move_uploaded_file($_FILES['fujian']['tmp_name'][$key],iconv('utf-8','gb2312',$path))){//把上传的文件重命名并移到系统upfile目录下
				   $lujing[] = $path;
				}
			}
	    }
	}
	  //路径存入数据库
}
//插入数据前先对文件进行处理
$sql	= "select g.*,g.id as id from `gys_gl` g
		where g.id='$parent_id'";
$row	= $DB->fetch_one_array($sql);
if(empty($_FILES['fujian']['name'][0])){
	$fujian=$row['fujian'];
}else{
	$fujian=array();
	foreach($_FILES['fujian']['name'] as $key=>$value){
		foreach($newname as $k=>$v){
			$fujian[$k]=$v;
		}
	}
	if(!empty($fujian)){
		$re=json_decode($row['fujian'],true);
		if(is_array($re)){
			$fujian=JSON(array_merge($re,$fujian));
		}else{
			$fujian=JSON($fujian);
		}
	}else{
		$fujian=$row['fujian'];
	}
}
//备用联系人和联系方式 处理
$lxr_arr=$_POST['lxr2'];
$lxdh2_arr=$_POST['lxdh2'];
foreach($lxr_arr as $k =>$v){
	if($v){
		$arr['lxr']=$v;
		$arr['lxdh']=$lxdh2_arr[$k];
		$lianxi_arr[]=$arr;
	}
}
$lianxi=json_encode($lianxi_arr,JSON_UNESCAPED_UNICODE);

//年度评价处理
$niandu=$_POST['niandu'];
$zonghe_pingjia=$_POST['zonghe_pingjia'];
$pingjia_ren=$_POST['pingjia_ren'];
$pingjia_time=$_POST['pingjia_time'];
foreach($niandu as $k =>$v){
	if($v){
		$niandu_arr['niandu']=$v;
		$niandu_arr['zonghe_pingjia']=$zonghe_pingjia[$k];
		$niandu_arr['pingjia_ren']=$pingjia_ren[$k];
		$niandu_arr['pingjia_time']=$pingjia_time[$k];
		$niandu_all_arr[]=$niandu_arr;
	}
}
$year_pingjia=json_encode($niandu_all_arr,JSON_UNESCAPED_UNICODE);

//print_r($_POST);exit;
//判断修改还是添加
if($_POST['handle']=='add'){
	//现增加列表页供应商
	$sql_add = "INSERT INTO `gys_gl` (`sname` , `dz` , `gy_fwly` ,`lxr` , `lxdh` , `fujian` , `gid` , `dengji_name` , `first_dengji_time`,`lianxi`,`year_pingjia`) VALUES('$sname'  , '$dz','$gy_fwly','$lxr','$lxdh','$fujian','$gid','$dengji_name','$first_dengji_time','$lianxi','$year_pingjia')";
	if($DB->query($sql_add)){
		$id=$DB->insert_id();
		echo "<script>alert('添加成功');window.location.href='$rooturl/gys/pingjia.php?parent_id=$id';</script>";
	}
}else{
	$sql_up = "UPDATE `gys_gl` SET `sname` = '$sname' , `dz` = '$dz' , `lxr` = '$lxr' , `lxdh` = '$lxdh' , `pjdate` = '$year' , `cpzl` = '$cpzl' , `fuwu` = '$fuwu' , `jiage` = '$jiage' , `fujian` = '$fujian' ,`gy_fwly`='$gy_fwly',`first_dengji_time`='$first_dengji_time', `dengji_name` = '$dengji_name',`gs_xinyong`='$gs_xinyong',`lianxi`='$lianxi',`year_pingjia`='$year_pingjia'  WHERE `id` = '$parent_id'" ;
	if($DB->query($sql_up)){
		echo "<script>alert('修改成功');window.location.href='$rooturl/gys/pingjia.php?parent_id=$parent_id';</script>";
	}
}
die;
if($id!='' && $year!=''){
	$inid	= $gid=$row['gid'];  
	$idg	= $row['id'];
	//id不为空 可以更新该年的评价表
	if($gid!='' && $year!=''){
		//供应商的名称等信息是统一的所以所有年份评价都要统一更改
		$DB->query("update `gys_gl` set wpname='$wpname',sname='$sname',dz='$dz' where gid='$gid'");
		//单个年份的相关信息是不同的，进行单独修改
			$DB->query("update `gys_gl` set wpname='$wpname',sname='$sname',dz='$dz',scdate='$year',pjbh='$pjbh',
				lxr='$lxr',lxdh='$lxdh',pjr='$pjr',pjdate='$year',
				cpzl='$cpzl',fuwu='$fuwu',xinyu='$xinyu',jiage='$jiage',fujian='$fujian',cunfang='$cunfang',zzjgdm='$zzjgdm',dqdate='$dqdate',swdjz='$swdjz',yyzz='$yyzz',beizhu='$beizhu'
				where id='$yqid' and scdate like '%$scdate%'");
		$yrow	= $DB->affected_rows();
		$DB->query("update `n_set` set `fzx_id`='$fzx_id',`module_value1`='$sname',`module_value2`='$year' WHERE id='$row[gid]'");
	}else{
		 $DB->query("insert into `gys_gl` values(null,'$sname','$wpname','$year','$pjbh','$dz','$lxr','$lxdh','$pjr','$year','$cpzl','$fuwu','$xinyu','$jiage','$cunfang','$id','','','','','','','$zzjgdm','$dqdate','$swdjz','$yyzz','$beizhu')");
	}
		gotourl("$rooturl/gys/pingjia.php?id=$gid&year=$year");		
			exit();
}else{
	$DB->query("insert into `n_set` set `fzx_id`='$fzx_id',`module_name`='gys',`module_value1`='$sname',`module_value2`='$year'");	//
		$inid	= $DB->insert_id();
		if(!empty($inid)){
			$DB->query("insert into `gys_gl` values(null,'$sname','$wpname','$year','$pjbh','$dz','$lxr','$lxdh','$pjr','$year','$cpzl','$fuwu','$xinyu','$jiage','$fujian','$cunfang','$inid','','','','','','','$zzjgdm','$dqdate','$swdjz','$yyzz','$beizhu')");
			$idg	= $DB->insert_id();
				gotourl("$rooturl/gys/pingjia.php?id=$inid");//&year=$year&scdate=$year
			exit();
		}else{
			echo "<script>alert('保存失败');location.href='$rooturl/gys/pingshen.php?gid=$idg&nid=$inid'</script>";
		}
}
?>

	





