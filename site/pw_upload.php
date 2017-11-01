<?php
//站点上传
include "../temp/config.php";
$fzx_id = $u['fzx_id'];
$flag   = false;
$date   = date("Y-m-d H:i:s");
##############//得到模板的下拉菜单
$sqlXmmb = $DB->query( "select * from `n_set` where fzx_id='$fzx_id' AND module_name='xmmb'" );
$paiWuMbxm  = $diXiaMbxm = '';
$paiWuTiShi = $diXiaTiShi= "未选择 默认项目!";
while( $row = $DB->fetch_assoc( $sqlXmmb ) ) {
	$data2Arr[$row['id']] = str_replace(',','|',$row['module_value1']);
	$countData2 = count(explode(",",$row['module_value1']));//模板拥有的项目个数
	if($row['module_value3']=="paiWu"){//已经选择了默认的模板
		$paiWuMbxm.= "<option selected=\"selected\"  value='$row[id]' countData2='".$countData2."' >$row[module_value2]</option> ";
		$paiWuTiShi= "此模板拥有化验项目：$countData2 项";
	}
	else if($row['module_value3']=="diXia"){
		$diXiaMbxm.= "<option selected=\"selected\"  value='$row[id]' countData2='".$countData2."'>$row[module_value2]</option> ";
		$diXiaTiShi= "此模板拥有化验项目：$countData2 项";
	}
	else{
		$paiWuMbxm.= "<option value='$row[id]' countData2='".$countData2."'>$row[module_value2]</option> ";
		$diXiaMbxm.= "<option value='$row[id]' countData2='".$countData2."'>$row[module_value2]</option> ";
	}
}
###########
//站点上传处理
if(!empty($_POST['fsub'])&&(!empty($_FILES['paiWuUpfile']['name'])||!empty($_FILES['diXiaUpfile']['name']))){
	//error_reporting(E_ALL);
        set_time_limit(0);
        //set_include_path(get_include_path() . PATH_SEPARATOR . '../Classes/');
        include '../temp/PHPExcel/IOFactory.php';
	############排污口站点excel导入
	if(!empty($_FILES['paiWuUpfile']['name'])){
		//定义批名
                if(!empty($_POST['paiWuGroupName']))$group_name = $_POST['paiWuGroupName'];
                else $group_name = "站点模板".date("Y-m-d H:i:s");
		        $xxx     = explode('.',$_FILES[paiWuUpfile][name]);
                $cnt     = count($xxx);
                $newname = date(ymdhis).".".$xxx[$cnt-1];
                $path    = "upfile/".$newname;
                $miao    = date('s');
		if(file_exists($_FILES[paiWuUpfile][tmp_name])){//判断上传的文件是否存在
		  if(move_uploaded_file($_FILES[paiWuUpfile][tmp_name],$path)){//把上传的文件重命名并移到系统upfile目录下
                	$inputFileName = $path;
                        $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
                        $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			//排污口项目:氨氮，化学需氧量，挥发酚，二甲苯(总量)，苯，甲苯，乙苯，氯苯 +ph 全盐量
                        //104|109|35|214|89|90|91|94,383
                        $conunt= 0;
                        if($sheetData[1]['A']!='所属分中心'){//第一个 字是省份 不是的话 就是她们传错文件了
                        	echo "<script>alert('上传文件内容不附');location.href='pw_upload.php'</script>";
                                exit;
                        }
			/*if(!empty($_POST['paiWuMbxm'])){//还不支持地下水任务和排污口任务 用同一个模板
				$DB->query("update `set` set `module_value3`='paiWu' where id='".$_POST['paiWuMbxm']."'");
			}*/
                ######获取每个分中心的名称及id
                $fzx_arr    = array();
                $zzx_id     = '';
                $sql_fzx    = $DB->query("SELECT * FROM `hub_info` WHERE 1");
                while($rs_fzx= $DB->fetch_assoc($sql_fzx)){
                    if($rs_fzx['id']==$fzx_id){
                        $zzx_id = $rs_fzx['parent_id'].",";
                    }
                    $fzx_arr[$rs_fzx['hub_name']]  = $rs_fzx['id'];
                }
                ######水样类型与id
                $water_type_arr = array();
                $sql_water_type = $DB->query("SELECT * FROM `leixing` where (`fzx_id`='$fzx_id' OR `fzx_id`='0') AND `act`='1'");
                while($rs_water_type= $DB->fetch_assoc($sql_water_type)){
                    $water_type_arr[$rs_water_type['lname']]    = $rs_water_type['id'];
                }
                #####垂线 对应的编号
                $site_line_arr = array("左"=>'1',"中"=>'2',"右"=>'3',"I"=>'1',"II"=>'2',"III"=>'3',"Ⅰ"=>'1',"Ⅱ"=>'2',"Ⅲ"=>'3');
                #####层面 对应的编号
                $site_vertical_arr  = array("上"=>'1',"中"=>'2',"下"=>'3',"I"=>'1',"II"=>'2',"III"=>'3',"Ⅰ"=>'1',"Ⅱ"=>'2',"Ⅲ"=>'3');
                ####统计参数的名称及id
                $tjcs_arr   = array();
                $zzx_id     .= $fzx_id;
                $sql_tjcs   = $DB->query("SELECT * FROM `n_set` WHERE fzx_id in($zzx_id) AND module_name='tjcs'");
                while ($rs_tjcs= $DB->fetch_assoc($sql_tjcs)) {
                    $tjcs_arr[$rs_tjcs['module_value1']]   = $rs_tjcs['id'];
                }
                        $i     = 0;
                        $error_water_type   = '';
                        foreach($sheetData as $kk => $vv){
	                        if($vv['A'] !='所属分中心'){
                                        $site_arr   = array();
                                        $site_arr['fzx_id'] = $fzx_id;
                                        $site_arr['site_type']      = 0;//先默认为监督任务
                                        $site_arr['river_name']    = $vv['J'];//河名
                                        $site_arr['site_name']      = $vv['B'];//站名
                                        $site_arr['water_system']   = $vv['H'];//水系
                                        $site_arr['site_address']   = $vv['N'];//站址
                                        $site_arr['area']           = $vv['I'];//流域
                                        $site_arr['xz_area']        = $vv['G'];//行政区
                                        $site_arr['create_date']    = date('Y-m-d');//创建时间
                                        $site_arr['sgnq']           = $vv['K'];//水功能区
                                        $site_arr['sgnq_code']      = $vv['L'];//水功能区编号
                                        $site_arr['site_code']      = $vv['C'];//站码
                                        $site_arr['create_man']     = $u['userid'];//创建人
                                        $site_arr['status']         = '1';//1
                                        $site_arr['banjing']        = $vv['Q'];//采样范围
                                        //分配给哪个分中心                                
                                        if(array_key_exists($vv['A'], $fzx_arr)){
                                            //如果不是总中心的人上传站点，只能上传到自己中心
                                            if($u['is_zz']=='1'){
                                                $site_arr['fp_id']  = $fzx_arr[$vv['A']];
                                            }else{
                                                $site_arr['fp_id']  = $fzx_id;
                                            }
                                        }else{
                                            $site_arr['fp_id']  = $fzx_id;
                                        }
                                        //判断水样类型
                                        if(array_key_exists($vv['F'], $water_type_arr)){
                                            $site_arr['water_type'] = $water_type_arr[$vv['F']];
                                        }else{
                                            $site_arr['water_type'] = $water_type_arr['地表水'];//如果识别不出水样类型就插入地表水的id
                                            $error_water_type   .= $vv['F'].":".$vv['B'].",";
                                        }
                                        //判断垂线
                                        if(array_key_exists($vv['D'], $site_line_arr)){
                                            $site_arr['site_line']  = $site_line_arr[$vv['D']];
                                        }else{
                                            $site_arr['site_line']  = '1';//垂线默认为1
                                        }
                                        //判断层面
                                        if(array_key_exists($vv['E'], $site_vertical_arr)){
                                            $site_arr['site_vertical'] = $site_vertical_arr[$vv['E']];
                                        }else{
                                            $site_arr['site_vertical'] = '1';
                                        }
                                        ############根据站码、垂线、层面来判断该站点在数据库中是否已存在
                                        $sql_site_old   = $DB->fetch_one_assoc("SELECT id,site_code FROM `sites` WHERE site_code='{$site_arr['site_code']}' AND `site_line`='{$site_arr['site_line']}' AND `site_vertical`='{$site_arr['site_vertical']}'");
                                        $update = 'no';
                                        if(!empty($sql_site_old['id'])){
                                            $update = $sql_site_old['id'];
                                            //continue;
                                        }
                                        //经度的转换
                                        //$vv['O']    = str_replace(array("度","分","秒"), array("°","′","″"), $vv['O']);
                                        $vv['O']    = str_replace(array("度","分","秒","°","′","″"), '|', $vv['O']);
                                        if(stristr($vv['O'],"|")){
                                            $jingdu_du  = $jingdu_fen = $jingdu_miao   = 0;
                                            $tmp_jingdu = explode("|",$vv['O']);
                                            if(!empty($tmp_jingdu[0])){
                                                $jingdu_du  = $tmp_jingdu[0];
                                            }
                                            if(!empty($tmp_jingdu[1])){
                                                $jingdu_fen = $tmp_jingdu[1];
                                            }    
                                            if(!empty($tmp_jingdu[2])){
                                                $jingdu_miao= $tmp_jingdu[2];
                                            }
                                            $site_arr['jingdu']     = ($jingdu_miao/60+$jingdu_fen)/60+$jingdu_du;
                                        }else{
                                            $site_arr['jingdu'] = $vv['O'];
                                        }
                                        //纬度的转换
                                        $vv['P']    = str_replace(array("度","分","秒","°","′","″"), '|', $vv['P']);
                                        if(stristr($vv['P'],"|")){
                                            $weidu_du  = $weidu_fen = $weidu_miao   = 0;
                                            $tmp_weidu = explode("|",$vv['P']);
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
                                        }else{
                                            $site_arr['weidu'] = $vv['P'];
                                        }
                                        $xm       = $data2Arr[$_POST['paiWuMbxm']];//化验项目
                                        //$xuhao    = $vv['B'];
                                        //统计参数
                                        $tjcs   = '';
                                        $site_tjcs_arr  = array();
                                        $vv['R']    = str_replace(array("，","，","，"), ',', $vv['R']);
                                        if($vv['R']!=''){
                                            $tmp_tjcs   = @explode(",",$vv['R']);
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
                                            $tjcs   .= ",";
                                        }
                                        $site_arr['tjcs']   = $tjcs;
                                        //水功能区类型
                                        $site_arr['sgnq_type']  = $vv['M'];
                                        /*$json   = array();
                                        $json['sgnq_type']  = $vv['M'];
                                        $site_arr['json']   = JSON($json);*/
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
                                        echo $sql_insert_site."<br><br><br>===============>";
                                        $DB->query($sql_insert_site);
                                        $site_new_id    = $DB->insert_id();
                                        if((int)$site_new_id>0){
                                            if(count($site_tjcs_arr)>0){
                                                foreach ($site_tjcs_arr as $value) {
                                                    $sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='$value',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1'");
                                                }
                                            }else{
                                                $sql_insert_group   = $DB->query("INSERT INTO `site_group` SET `fzx_id`='$fzx_id',`site_id`='$site_new_id',`group_name`='',`ctime`='".date('Y-m-d h:i:s')."',`assay_values`='$xm',`cuser`='{$u['userid']}',`act`='1'");
                                            }
                                            echo "<br><br><br>";
                                        }
                                        //echo $sql_insert_site    = "INSERT INTO `sites` SET fzx_id='$fzx_id',fp_id='$fp_id',`site_type`='$site_type',`river_name`='$river_name',site_name='$site_name',`water_type`='$water_type',water_system='$water_system',`site_address`='$site_address',area='$area',`xz_area`='$xz_area',`create_date`='$create_date',`sgnq`='$sgnq',`sgng_code`='$sgng_code',`site_code`='$site_code',`site_line`='$site_line',`site_vertical`='$site_vertical',`create_man`='$create_man',`status`='$status',`jingdu`='$jingdu',`weidu`='$weidu',`banjing`='$banjing',`tjcs`='$tjcs'";

                                        /*mysql_query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
                                        mysql_query("BEGIN");//开始事务定义
                                        if($DB->query($sql)){
                  	                	$count ++;
                                        	$id   = mysql_insert_id();
						$time = date('Y-m-d h:i').':'.$miao;
                                                $sql2 = "insert into `site_group` (`site_id`,`group_name`,`ctime`,`assay_values`,`cuser`,`ord`) values('$id','$group_name','$time','$xm','$user','$xuhao')";
                                                if(!$DB->query($sql2))mysql_query("ROOLBACK");//判断执行失败回滚
                                         }else{
                                         	mysql_query("ROOLBACK");//判断执行失败回滚
                                         }
                                         mysql_query("COMMIT");//执行事务
                                         $i++;
                                         $flag = true;
                                         */
                                  }
                	}
                  }else{
                	echo "<script>alert('上传文件出现错误请联系管理员');location.href='pw_upload.php'</script>";
                        exit;
                  }
		}
		else{
			echo "<script>alert('上传文件未找到,请刷新页面重新上传');location.href='pw_upload.php'</script>";
                        exit;
		}
	}
    exit;
	/*######地下水站点excel导入
	if(!empty($_FILES['diXiaUpfile']['name'])){
		//定义批名
		if(!empty($_POST['diXiaGroupName']))$group_name = $_POST['diXiaGroupName'];
		else $group_name = "地下水".date("Y-m-d H:i:s");
		$xxx     = explode('.',$_FILES['diXiaUpfile']['name']);
                $cnt     = count($xxx);
                $newname = date('ymdhis').".".$xxx[$cnt-1];//上传文件的新名称
//有时间给文件夹 加个判断,如果没有就新建 并赋予权限
                $path    = "./upfile/dixiashui/".$newname;//上传文件的存放路径
                $miao    = date('s');
                if(file_exists($_FILES['diXiaUpfile']['tmp_name'])){//判断上传的文件是否存在
                  if(move_uploaded_file($_FILES['diXiaUpfile']['tmp_name'],$path)){//把上传的文件重命名并移到系统upfile目录下
                        $inputFileName = $path;
                        $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
                        $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                        //排污口项目:氨氮，化学需氧量，挥发酚，二甲苯(总量)，苯，甲苯，乙苯，氯苯 +ph 全盐量
                        //104|109|35|214|89|90|91|94,383
                        $conunt= 0;
                        if($sheetData[1]['A']!='序号'){//第一个字不是“序号”的话 就是她们传错文件了
                                echo "<script>alert('上传文件内容不附');location.href='pw_upload.php'</script>";
                                exit;
                        }
			if(!empty($_POST['diXiaMbxm'])){//还不支持地下水任务和排污口任务 用同一个模板
                                $DB->query("update `set` set `data1`='diXia' where id='".$_POST['diXiaMbxm']."'");
                        }
                        $pat[] = '/(.*)\'(.*)/';
                        $pat[] = '/(.*)\"(.*)/';
                        $pat[] = '/(.*)\º(.*)/';
                        $rep[] = '$1′$2';
                        $rep[] = '$1″$2';
                        $rep[] = '$1°$2';
                        $i     = 0;
		//	$firstLine = @array_flip($sheetData['1']);//得到标题对应的列 array
			$zhongWenFuHao  = array('（','（');//中文左括号 全半角
			$zhongWenFuHao2 = array('）','）');//中文右括号 全半角
			$count = 0;
                        foreach($sheetData as $tr => $td){
				if($tr ==1||$tr=='1')continue;
				if($tr!='1'&&$tr!=1&& $td['B'] !=''&&$td['B'] !='监测站名称'){//最起码站点名称不为空
					$ord       = $td['A'];//站点排序码
					$site_name = $td['B'];//站名
//为空情况
					$td['B']   = str_replace($zhongWenFuHao2,')',str_replace($zhongWenFuHao,'(',$td['B']));//将括号统一英文格式
					$code      = str_replace(')','',substr($td['B'],strrpos($td['B'],'(')+1));//从站名中取出站码
                                        $site_name = $td['B'];//站名
					$river_name= $td['I'].$td['H'];//所属区域
                                        $valley    = $td['E'];//街道名称
					$jingdu    = preg_replace($pat,$rep,$td['F']);//经度
					$weidu     = preg_replace($pat,$rep,$td['G']);//纬度
                                        //$xm        = '104|109|35|214|89|90|91|94';//默认不导入全盐量383号项目
					$xm        = $data2Arr[$_POST['diXiaMbxm']];//地下水的项目 每个站点不一样,就不自动导入了
                                        $note      = '监测站编码:'.$td['C'].
                                                    '\r行政区编码:'.$td['D'];//备注
                                        $user     = $_SESSION['u']['userid'];
                                        $sql      = "insert into `sites` (`code`,`site_name`,`site_type`,`valley`,`jingdu`,`weidu`,`river_name`,`note`,`water_type`,`create_date`) values('".$code."','".$site_name."','5','".$valley."','".$jingdu."','".$weidu."','".$river_name."','".$note."','地下水',curdate())";
                                        mysql_query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
                                        mysql_query("BEGIN");//开始事务定义
                                        if($DB->query($sql)){
                                                $count++;
                                                $id   = mysql_insert_id();
                                                $time = date('Y-m-d h:i').':'.$miao;
                                                $sql2 = "insert into `site_group` (`site_id`,`group_name`,`ctime`,`assay_values`,`cuser`,`ord`) values('".$id."','".$group_name."','".$time."','".$xm."','".$user."','".$ord."')";
                                                if(!$DB->query($sql2))mysql_query("ROOLBACK");//判断执行失败回滚
                                         }else{
                                                mysql_query("ROOLBACK");//判断执行失败回滚
                                         }
                                         mysql_query("COMMIT");//执行事务
                                         $i++;
                                         $flag = true;
                                  }
			}
		  }else{
                        echo "<script>alert('上传文件出现错误请联系管理员');location.href='pw_upload.php'</script>";
                        exit;
                  }
                }
                else{
                        echo "<script>alert('上传文件未找到,请刷新页面重新上传');location.href='pw_upload.php'</script>";
                        exit;
                }
	}*/
	if($flag==true){
                echo "<script>alert('数据导入成功,共导入".$count."个站点');location.href='pw_upload.php';</script>"; //前面几步的都正确 就弹出成功
		exit;
        }
	echo "<script>alert('请先上传excel文件');location.href='pw_upload.php'</script>";
        exit;
}
disp('pw_upload');
?>
