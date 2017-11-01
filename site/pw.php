<?php
//站点上传
include "../temp/config.php";
$fzx_id = $u['fzx_id'];
$flag   = $_POST['flag'];
$date   = date("Y-m-d H:i:s");
$mbse   = $count = '';
//$sort	= '22';//批次排序
##############//得到模板的下拉菜单
$sqlXmmb = $DB->query( "select * from `n_set` where fzx_id='$fzx_id' AND module_name='xmmb'" );
while( $row = $DB->fetch_assoc( $sqlXmmb ) ) {
	$mbse.= "<option value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
}
##############//得到模板的下拉菜单
//站点上传处理
if(!empty($_POST['fsub'])&&(!empty($_FILES['fullfile']['name'])||!empty($_FILES['pifile']['name'])))
{
	set_time_limit(0);
	$flag == '';
	include '../inc/classes/PHPExcel/IOFactory.php';
	############分批站点excel导入
	if(!empty($_FILES['pifile']['name'])){
		$xxx     = explode('.',$_FILES[pifile][name]);
		$cnt     = count($xxx);
		$newname = date(ymdhis).".".$xxx[$cnt-1];
		$path    = "upfile/".$newname;
		$miao    = date('s');
		if($xxx[$cnt-1]!='xls'&&$xxx[$cnt-1]!='xlsx'){
			echo "<script>alert('请上传excel格式的文件');location.href='pw.php'</script>";
			exit;
		}
		if(file_exists($_FILES[pifile][tmp_name]))
		{//判断上传的文件是否存在
			if(move_uploaded_file($_FILES[pifile][tmp_name],$path))
			{//把上传的文件重命名并移到系统upfile目录下
			   $inputFileName = $path;
			   $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
			   $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			   if($sheetData[1][A]!='站点名称' && $sheetData[1][A]!='site_name')
			   {//文件错误判断
					echo "<script>alert('上传文件内容不附');location.href='pw.php'</script>";
					exit;
			   }
			}
		}
	}

	$kaishi = '';
	if($sheetData[1][A] == 'site_name' && $sheetData[1][C] == 'water_type'){
		$lie = $sheetData[1];
		$kaishi = 2;
	}else{
		$lie = array('A'=>'site_name','B'=>'site_code','C'=>'water_type','D'=>'water_system','E'=>'site_type','F'=>'area','G'=>'xz_area','H'=>'river_name','I'=>'site_address','J'=>'site_line','K'=>'site_vertical','L'=>'jingdu','M'=>'weidu','N'=>'banjing','O'=>'sgnq','P'=>'sgnq_code','Q'=>'sgnq_type','R'=>'fp_id','S'=>'tjcs','T'=>'note');
		$kaishi = 1;
	}
	######获取每个分中心的名称及id
	$fzx_arr    = array();
	$zzx_id     = '';
	$sql_fzx    = $DB->query("SELECT * FROM `hub_info` WHERE 1");
	while($rs_fzx= $DB->fetch_assoc($sql_fzx))
	{
	   if($rs_fzx['id']==$fzx_id)
	   {
		   $zzx_id = $rs_fzx['parent_id'].",";
	   }
	   $fzx_arr[$rs_fzx['hub_name']]  = $rs_fzx['id'];
	}

	######水样类型与id
	$water_type_arr = array();
	$sql_water_type = $DB->query("SELECT * FROM `leixing` where (`fzx_id`='$fzx_id' OR `fzx_id`='0') AND `act`='1'");
	while($rs_water_type= $DB->fetch_assoc($sql_water_type))
	{
	   $water_type_arr[$rs_water_type['lname']]    = $rs_water_type['id'];
	}
	######任务类型对应id
	$site_type_arr = array('监督任务'=>0,'常规任务'=>1,'临时任务'=>2,'委托任务'=>3);
	#####垂线 对应的编号
	$site_line_arr = array("左"=>'1',"中"=>'2',"右"=>'3',"I"=>'1',"II"=>'2',"III"=>'3',"Ⅰ"=>'1',"Ⅱ"=>'2',"Ⅲ"=>'3');

	#####层面 对应的编号
	$site_vertical_arr  = array("上"=>'1',"中"=>'2',"下"=>'3',"I"=>'1',"II"=>'2',"III"=>'3',"Ⅰ"=>'1',"Ⅱ"=>'2',"Ⅲ"=>'3');

	####统计参数的名称及id
	$tjcs_arr   = array();
	$zzx_id     .= $fzx_id;
	$sql_tjcs   = $DB->query("SELECT * FROM `n_set` WHERE fzx_id in($zzx_id) AND module_name='tjcs'");
	while ($rs_tjcs= $DB->fetch_assoc($sql_tjcs)) 
	{
	   $tjcs_arr[$rs_tjcs['module_value1']]   = $rs_tjcs['id'];
	}
	//更改批次排序
	if(!empty($sort)){
		$paixu_group	= $DB->query("SELECT `id`,`sort`,`group_name` FROM `site_group` WHERE `fzx_id`='$fzx_id' AND `act`='1' AND `site_type`='{$site_arr[site_type]}' AND `sort`>='$sort' group by `group_name` order by `sort`");
		while($paixu_group_rs = $DB->fetch_assoc($paixu_group)){
			if(!empty($paixu_group_rs['sort'])){
				$paixu_group_rs['sort']++;
			}
			echo "UPDATE `site_group` set sort='{$paixu_group_rs['sort']}' WHERE `fzx_id`='$fzx_id' AND `site_type`='{$site_arr[site_type]}' AND `group_name`='{$paixu_group_rs['group_name']}'";
			//$DB->query("UPDATE `site_group` set sort='{$paixu_group_rs['sort']}' WHERE `fzx_id`='$fzx_id' AND `site_type`='{$site_arr[site_type]}' AND `group_name`='{$paixu_group_rs['group_name']}'");
		}
	}
	//开始循环
	$i = 1;
	$error_water_type   = '';
	$site_sort= 0;
	foreach($sheetData as $kk => $vv){
	   if($i>$kaishi){
	   		
			if($vv[A] == '' && $vv[B] == '' && $vv[C] == ''){
				continue;
			}
			foreach($vv as $zimu => $row){
				$site_arr[$lie[$zimu]] = $row;
			}
			//分配中心替换成id
			if(isset($site_arr['fp_id']) && array_key_exists($site_arr['fp_id'], $fzx_arr) && !empty($site_arr['fp_id'])){
				//如果不是总中心的人上传站点，只能上传到自己中心
				if($u['is_zz']=='1'){
					$site_arr['fp_id']  = $fzx_arr[$site_arr['fp_id']];
				}else{
					$site_arr['fp_id']  = $fzx_id;
				}
			}else{
				$site_arr['fp_id'] = 0;
			}
			############站点类别替换成id
			if(isset($site_arr['site_type']) && array_key_exists($site_arr['site_type'],$site_type_arr) && !empty($site_arr['site_type'])){
				$site_arr['site_type'] = $site_type_arr[$site_arr['site_type']];
			}else{
				$site_arr['site_type'] = '1';
			}
			//水样类型替换成id
			if(isset($site_arr['water_type']) && array_key_exists($site_arr['water_type'],$water_type_arr) && !empty($site_arr['water_type'])){
				$site_arr['water_type'] = $water_type_arr[$site_arr['water_type']];
			}else{
				$site_arr['water_type'] = $water_type_arr['地表水'];//如果识别不出水样类型就插入地表水的id
			}

			//判断垂线替换成id
			if(isset($site_arr['site_line']) && array_key_exists($site_arr['site_line'],$site_line_arr) && !empty($site_arr['site_line'])){
				$site_arr['site_line']  = $site_line_arr[$site_arr['site_line']];
			}else{
				$site_arr['site_line']  = '1';//垂线默认为1
			}
			//判断层面替换成id
			if(isset($site_arr['site_vertical']) && array_key_exists($site_arr['site_vertical'],$site_vertical_arr) && !empty($site_arr['site_vertical'])){
				$site_arr['site_vertical'] = $site_vertical_arr[$site_arr['site_vertical']];
			}else{
				$site_arr['site_vertical'] = '1';
			}

			############根据站码、垂线、层面来判断该站点在数据库中是否已存在 
			$sql_site_old   = $DB->fetch_one_assoc("SELECT id,site_code,tjcs FROM `sites` WHERE site_name = '{$site_arr['site_name']}' AND site_code='{$site_arr['site_code']}' AND `site_line`='{$site_arr['site_line']}' AND `site_vertical`='{$site_arr['site_vertical']}'");
//AND site_type='{$site_arr['site_type']}'
			$update = 'no';
			if(!empty($sql_site_old['id'])){
				//旧统计参数，如果存在，需要对site_group进行更新
				$old_tjcs = $sql_site_old['tjcs'];
				if($old_tjcs!=''&&$old_tjcs!=',,'){
					$old_tjcs  = @explode(",",$old_tjcs);
				}else{
					$old_tjcs ='';
				}
				//旧有的id
				$update = $sql_site_old['id'];
			}

			//经度的转换  
			if(isset($site_arr['jingdu'])){
				$jingdu = str_replace(array("度","分","秒","°","′","″"), '|', $site_arr['jingdu']);
				if(stristr($jingdu,"|")){
					$jingdu_du  = $jingdu_fen = $jingdu_miao   = 0;
					$tmp_jingdu = explode("|",$jingdu);
					if(!empty($tmp_jingdu[0])){
						$jingdu_du  = $tmp_jingdu[0];
					}
					if(!empty($tmp_jingdu[1])){
						$jingdu_fen = $tmp_jingdu[1];
					}    
					if(!empty($tmp_jingdu[2])){
						$jingdu_miao= $tmp_jingdu[2];
					}
					$site_arr['jingdu'] = ($jingdu_miao/60+$jingdu_fen)/60+$jingdu_du;
				}
			}                      
			
			//纬度的转换
			if(isset($site_arr['weidu'])){
				$weidu = str_replace(array("度","分","秒","°","′","″"), '|', $site_arr['weidu']);
				if(stristr($weidu,"|")){
					$weidu_du  = $weidu_fen = $weidu_miao   = 0;
					$tmp_weidu = explode("|",$weidu);
					if(!empty($tmp_weidu[0])){
						$weidu_du  = $tmp_weidu[0];
					}
					if(!empty($tmp_weidu[1])){
						$weidu_fen = $tmp_weidu[1];
					}    
					if(!empty($tmp_jingdu[2])){
						$weidu_miao= $tmp_weidu[2];
					}
					$site_arr['weidu']     = ($weidu_miao/60+$weidu_fen)/60+$weidu_du;
				}
			}
			//统计参数替换成id
			if(isset($site_arr['tjcs'])){
				$tjcs   = '';
				$site_tjcs_arr  = array();
				$site_arr['tjcs'] = str_replace(array("，","，","，"), ',', $site_arr['tjcs']);
				if($site_arr['tjcs']!=''){
					$tmp_tjcs   = @explode(",",$site_arr['tjcs']);
					foreach ($tmp_tjcs as $value) {
						if(array_key_exists($value, $tjcs_arr)){
							$tjcs   .= ",".$tjcs_arr[$value];
						}else{
							$sql_insert_tjcs    = $DB->query("INSERT INTO `n_set` SET fzx_id='$fzx_id',module_name='tjcs',module_value1='{$value}'");
							$tjcs_new_id        = $DB->insert_id();
							$tjcs_arr[$value]   = $tjcs_new_id;
							$tjcs   .= ",".$tjcs_arr[$value];
						}
						$site_tjcs_arr[]    = $tjcs_arr[$value];
					}
					$tjcs .= ",";
				}
				$site_arr['tjcs']   = $tjcs;
			}
			//站点任务类型判断
			$site_arr['fzx_id'] = $fzx_id;
			if(($site_arr['fzx_id'] != $site_arr['fp_id']) && ($site_arr['fp_id'] != 0)){
				$site_arr['site_type'] = 0;
			}
			//banjing
			if($site_arr['banjing'] == ''){
				$site_arr['banjing'] = '200';
			}
			$xm = $xmshu = '';
			$xmshu = explode('*',$_POST[xmmb]);
			$xm = $xmshu[0];

			//参数判断
			if($update=='no'){
					$sql_insert_site    = 'INSERT INTO `sites` SET ';
			}else{
				$sql_insert_site    = 'UPDATE `sites` SET ';
			}

			foreach ($site_arr as $key => $value) {
				$sql_insert_site    .= "`$key`='$value',";
			}
			$sql_insert_site   = substr($sql_insert_site,0,-1);
			if($update!='no'){
				$sql_insert_site    .= " WHERE id='$update'";
			}
			$charu = $DB->query($sql_insert_site);
			if($charu){
				if($update=='no'){
					$site_new_id = $DB->insert_id();
					$count++;
				}
				$cun++;
				
			}
			//如果该站点为监督站点
			if($site_arr['site_type'] == 0){
				if($update  == 'no'){
					if((int)$site_new_id>0){
						if(count($site_tjcs_arr)>0){
							foreach ($site_tjcs_arr as $value) {
								$sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='$value',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");
							}
						}else{
							$sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");
						}
					}
				}else{
					if(count($old_tjcs)>0){
						foreach ($old_tjcs as $value) {
							$sql_insert_group   = $DB->query("delete from `site_group` where `site_id`='$update' and `group_name`='$value'");
						}
					}
					if(count($site_tjcs_arr)>0){
						foreach ($site_tjcs_arr as $value) {
							$sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$update',`group_name`='$value',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");
						}
					}else{
						$sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");
					}
					
				}
			}else{//如果不为监督站点先判断有没有上传批名
				if(!empty($_POST[piming])){
					$site_sort++;
					if($update  != 'no'){
						$cunzai = $DB->fetch_one_assoc("SELECT id,site_id FROM `site_group` WHERE site_id='$update' AND `group_name`='{$_POST[piming]}' AND `site_type`='{$site_arr['site_type']}'");
						if($cunzai){
							$DB->query("update `site_group` SET `fzx_id`='$fzx_id',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`sort`='$sort',`site_sort`='$site_sort' where id='{$cunzai[id]}'");
						}else{
							$sql_insert_group = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='{$_POST[piming]}',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}',`sort`='$sort',`site_sort`='$site_sort'");  
						}

					}else{
						if($site_new_id !='' && (int)$site_new_id>0){
							 $sql_insert_group = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='{$_POST[piming]}',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}',`sort`='$sort',`site_sort`='$site_sort'");  
						}
					}
				}else{
					if($update  != 'no'){
						$cunzai = $DB->fetch_one_assoc("SELECT id,site_id FROM `site_group` WHERE site_id='$update' AND `group_name`='' AND `site_type`='{$site_arr['site_type']}'");
						if($cunzai){
							$DB->query("update `site_group` SET `fzx_id`='$fzx_id',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1' where id='{$cunzai[id]}'");
						}else{
							$sql_insert_group = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='{$_POST[piming]}',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");  
						}
					}else{
						if($site_new_id !='' &&(int)$site_new_id>0){
							$sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1',`site_type`='{$site_arr[site_type]}'");
						}
					}
				}
				
			}
   
	   }
	   ++$i; 
	   $site_new_id = '';
	}

	if($cun>0){
				echo "<script>alert('数据导入成功，对".($cun-$count)."个已存在的站点进行了更新，新导入了".$count."个站点');location.href='pw.php';</script>"; 
		exit;
	}else{
		  echo "<script>alert('站点上传失败，请按照规定格式上传');location.href='pw.php';</script>"; 
		exit;
	} 

}else{
	disp("shangchuan");exit;
}
