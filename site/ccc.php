<?php
//站点上传是 分中心 识别错误的 更正文件（可删除）
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
                                        //分配给哪个分中心                                
                                        if(array_key_exists($vv['A'], $fzx_arr)){
                                            //如果不是总中心的人上传站点，只能上传到自己中心
                                            if($u['is_zz']=='1'){
                                                $site_arr['fp_id']  = $fzx_arr[$vv['A']];
                                            }else{
                                                $site_arr['fp_id']  = $fzx_id;
                                            }
                                        }else{
                                            $vv['A']    = str_replace("监测分中心",'监测中心',$vv['A']);
                                            echo $vv['A']."==>".$site_arr['site_name']."<br>";
                                            $sql_aaa    = $DB->query("SELECT * FROM `sites` WHERE site_name='{$site_arr['site_name']}'");
                                            while ($rs_aaa=$DB->fetch_assoc($sql_aaa)) {
                                                $DB->query("update `sites` set fp_id='{$fzx_arr[$vv['A']]}',site_type='0' where id='{$rs_aaa['id']}'");
                                            }
                                            $site_arr['fp_id']  = $fzx_id;
                                        }
                                        
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
