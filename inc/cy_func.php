<?php
/**
 * 功能：与下达采样任务有关的function
 * 作者：hanfeng
 * 日期：2014-06-11
 * 描述：
*/
/**
 * 功能：根据传入的采样日期和目前系统中本月最后一个样品编号来生成新的编号
 * 作者：hanfeng
 * 日期：2014-06-11
 * 参数1：[int] [site_type] [任务类型]
 * 参数2：[int] [water_type] [水样类型]
 * 参数3：[日期] [cy_date] [采样日期]
 * 返回值：最新的样品编号
 * 描述： 编号格式为 **-年月-***(任务类型+样品类型+“-”+年月+“-”+每月样品流水号)
*/
function new_bar_code( $site_type,$water_type, $cy_date ) {
	global $global,$DB,$u;
	#########先到n_set表中获取一下，样品编号的组成部分有哪些
	$bar_code_make_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_make' LIMIT 1");
	$bar_code_make_arr	= array();
	if(!empty($bar_code_make_old['module_value1'])){
		$bar_code_make_arr	= explode("+",$bar_code_make_old['module_value1']);
	}else{
		//默认全部包括
		$bar_code_make_arr	= array("site_type","water_type","year","month","-","serial_num");
	}
	#########取出样品编号中任务类型所代表的字母
	if(in_array('site_type',$bar_code_make_arr)){
		$bar_code_site_mark	= array();
		$sql_bar_code_site_mark	= $DB->query("SELECT * FROM `n_set` WHERE `module_name`='bar_code_mark_site'");
		while ($rs_bar_code_site_mark=$DB->fetch_assoc($sql_bar_code_site_mark)) {
				$bar_code_site_mark[$rs_bar_code_site_mark['module_value2']]	= $rs_bar_code_site_mark['module_value1'];
		}
	}
	#########取出所有水样类型
	if(in_array('water_type',$bar_code_make_arr)){
	    $water_type_all = $water_type_all2      = $bar_code_water_mark	= array();
	    $sql_water_type = $DB->query("SELECT * FROM `leixing` WHERE 1");
	    while($rs_water_type=$DB->fetch_assoc($sql_water_type)){
	        if($rs_water_type['parent_id']=='0'){
	                $rs_water_type['parent_id']     = $rs_water_type['id'];
	        }
	        $bar_code_water_mark[$rs_water_type['id']]	= $rs_water_type['bar_code_mark'];
			$water_type_all[$rs_water_type['id']]   = $rs_water_type['parent_id'];
	        $water_type_all2[$rs_water_type['parent_id']][]   = $rs_water_type['id'];
	    }
	}
	$fzx_id 	 = $u['fzx_id'];
	$bar_code	 = $where_water_type	= '';
	if(in_array('site_type',$bar_code_make_arr)){
		//根据任务类型确定 样品编号的第一位
		if(empty($bar_code_site_mark[$site_type])){
			$bar_code	= 'A';//没有配置就默认为A
		}else{
			$bar_code	 = $bar_code_site_mark[$site_type];//$global['bar_code']['site_type'][$site_type];
		}
	}
	if(in_array('water_type',$bar_code_make_arr)){
		//根据水样类型确定 样品编号的第二位
		if(empty($bar_code_water_mark[$water_type])){
			$bar_code	.= 'A';//没有配置就默认为A
	    }else{
			$bar_code	.= $bar_code_water_mark[$water_type];
		}
	}
	/*if(!empty($global['bar_code']['water_type'][$water_type])){
		$bar_code	.= $bar_code_water_mark[$water_type];//$global['bar_code']['water_type'][$water_type];
	}else{
		$bar_code	.= $global['bar_code']['water_type'][$water_type_all[$water_type]];
	}*/
	$bar_code	.= date('Ym', strtotime($cy_date));//根据采样日期确定 样品编号的中间部分
	if(in_array('-',$bar_code_make_arr)){
		$bar_code	.= '-';;
	}
	//根据n_set表里的配置，决定样品编号是按年生成还是按月生成
	$bar_code_create= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_create' limit 1");
	$where_month	= '';
	if(!empty($bar_code_create['module_value1']) && $bar_code_create['module_value1'] == 'year'){
		//按年编号
		$where_date	= date('Y-01-01',strtotime($cy_date));
	}else{
		//按月编号
		$where_date	= date('Y-m-01',strtotime($cy_date));
		$where_month= " AND  cy.cy_date<='".date("Y-m-d",strtotime("$where_date +1 month -1 day"))."'";//date('Y-m-01',strtotime("+1months",strtotime($cy_date)))."' "; 
	}
	//根据global中的配置，判断是否需要将不同水样类型(大类)分开编号
	if($global['bar_code']['water_type_barcode']=='1'){
		$water_type      = $water_type_all[$water_type];
		$where_water_type= " AND rec.water_type in('',".implode(",",$water_type_all2[$water_type]).") ";
	}
	//SUBSTR(bar_code,-4,4)  这一句的意思是，返回样品编号的后4位。（为了能按照样品编号的流水号进行排序而做，如果编号格式改变就不适用了）
 	$rs_bar_code	 = $DB->fetch_one_assoc("SELECT cy.site_type,rec.id,rec.bar_code FROM `cy_rec` AS rec INNER JOIN `cy` ON rec.cyd_id=cy.id WHERE cy.fzx_id='$fzx_id' AND cy.site_type='$site_type' $where_water_type AND rec.bar_code!='' AND cy.cy_date>='$where_date' $where_month ORDER BY SUBSTR(bar_code,-4 ,4) DESC LIMIT 0,1");
	if(!empty($rs_bar_code['bar_code'])){
		$new_num	= (int)substr( $rs_bar_code['bar_code'], -4) + 1;
	}else{
		$new_num	= 1;
	}
	$t	= '';
	//在流水号前面加上相应的几个“0”，一共3位数
	if ( $new_num < 10 ){
        	$t	= '000';
        }else if( $new_num>=10 && $new_num<100 ){
		$t	= '00';
	}elseif($new_num>=100 && $new_num<1000){
		$t      = '0';
	}
	return $bar_code . $t . $new_num;
}
/**
 * 功能：根据传入的采样日期和目前系统中本月最后一个采样单编号来生成新的编号
 * 作者：hanfeng
 * 日期：2014-06-11
 * 参数1：[int] [site_type] [任务类型]
 * 参数2：[日期] [cy_date] [采样日期]
 * 返回值：最新的采样单号
 * 描述： 采样单号格式为 *年月***(任务类型+年月+每月采样单流水号)
*/
function new_cyd_bh($site_type,$cy_date){
	global $global,$DB,$u;
	#########取出样品编号中任务类型所代表的字母
	$bar_code_site_mark	= array();
	$sql_bar_code_site_mark	= $DB->query("SELECT * FROM `n_set` WHERE `module_name`='bar_code_mark_site'");
	while ($rs_bar_code_site_mark=$DB->fetch_assoc($sql_bar_code_site_mark)) {
			$bar_code_site_mark[$rs_bar_code_site_mark['module_value2']]	= $rs_bar_code_site_mark['module_value1'];
	}
	$fzx_id	 = $u['fzx_id'];
	$cyd_bh	 = $where_month	= '';
	//根据任务类型确定 采样单号的第一位
	if(empty($bar_code_site_mark[$site_type])){
		$cyd_bh	= 'A';//没有配置就默认为A
	}else{
		$cyd_bh	 = $bar_code_site_mark[$site_type];//$global['bar_code']['site_type'][$site_type];
	}
	$cyd_bh	.= date('Ym', strtotime($cy_date));//根据采样日期确定 采样单号的中间部分
	$where_date     = date('Y-m-01',strtotime($cy_date));
	$where_month= " AND  cy.cy_date<='".date("Y-m-d",strtotime("$where_date +1 month -1 day"))."'";//date('Y-m-01',strtotime("+1months",strtotime($cy_date)))."' ";  
	$rs_cyd_bh	= $DB->fetch_one_assoc("SELECT cyd_bh FROM `cy` WHERE fzx_id='$fzx_id' AND site_type='$site_type' AND cyd_bh!='' AND cy_date>='$where_date' $where_month ORDER BY SUBSTR(cyd_bh,-4 ,4) DESC LIMIT 0,1");
	if(!empty($rs_cyd_bh['cyd_bh'])){
		$new_num= (int)substr( $rs_cyd_bh['cyd_bh'], -3) + 1;
	}else{
		$new_num        = 1;
	}
	$t      = '';
        //在流水号前面加上相应的几个“0”，一共3位数
        if($new_num<10){
                $t      = '00';
        }else if($new_num>=10 && $new_num<100){
                $t      = '0';
        }
	return $cyd_bh.$t.$new_num;
}
/* 
 * 功能：查询出水样类型的最大类
 * 作者: zhengsen
 * 时间：2014-6-27
 */
function get_water_type_max($water_type,$fzx_id){
	global $DB;
	$lx=$DB->fetch_one_assoc("SELECT id,parent_id FROM	`leixing` WHERE id='".$water_type."' AND (fzx_id='".$fzx_id."'||fzx_id='0')");
	if(!$lx){
		return false;
	}else{
		if($lx['parent_id']=='0'){
			return $water_type;
		}else{
			return get_water_type_max($lx['parent_id'],$fzx_id);
		}
	}
 }
function cy_shuyuan($cyd_id,$userid,$html,$liyou) {
        global $DB,$pdf_files;
        if(empty($pdf_files)){//个性config里面配置的路径
                $pdf_files      = '/home/files/';
        }
        $md5     = md5($html);
        //$html  = addslashes($html);
        $cs      = $DB->fetch_one_assoc("select COUNT( id ) as ci from hy_shuyuan where cyd_id='$cyd_id'");
        $ci      = $cs['ci'];
        $row     = $DB->fetch_one_assoc("select id,md5,liyou from hy_shuyuan where cyd_id='$cyd_id' ORDER BY `id` DESC LIMIT 1");
        $md51    = substr($md5,0,2);//获取到前两位作为文件夹名称
        $md52    = substr($md5,2,2);//获取到2到4位作为二级文件夹名称
        if($row['md5']!=$md5 && $row['liyou']!=$liyou){//以前插入过数据
                $DB->query("INSERT INTO `hy_shuyuan` set `cyd_id`='$cyd_id',`userid`='$userid',`cishu`='$ci',`rdate`= now(),`liyou`='$liyou',`md5`='$md5',`html`='1'");
                //创建一级目录
                $path   = $pdf_files.'shuyuan/'.$md51;
                if(!file_exists($path)){
                        mkdir($path,0777);
                }
                //创建二级目录
                $path   = $path.'/'.$md52;
                if(!file_exists($path)){
                        mkdir($path,0777);
                }
                //创建文件并写入内容
                $path   = $path.'/'.$md5;
                file_put_contents($path,$html);
                //$command      = "mkdir ".$pdf_files."shuyuan/".$md51;
                //exec($command,$out,$status);//$out 返回结果  $command 执行外部命令  、$status 返回状态  为0为成功
                //$command1     = "mkdir ".$pdf_files."shuyuan/".$md51."/".$md52;
                //exec($command1,$out1,$status1);
                //$filename     = "".$pdf_files."shuyuan/".$md51."/".$md52."/".$md5;
                //$command2     = "touch $filename";
                //exec($command2,$out2,$status2);
                //file_put_contents($filename,$html);
                //压缩文件
                $command3       = "gzip ".$pdf_files."shuyuan/".$md51."/".$md52."/".$md5;    //添加压缩文件  不保留原文件
                exec($command3,$out3,$status3);
        }
        //$comm1 = "unlink  /home/files/shuyuan/".$md51."/".$md52."/".$md5.".text";  //删除已经创建的文件
        //exec($comm1,$outa1,$statusa1);
}
/**
 * 功能：根据传入的采样日期和目前系统中当月最后一个空白的编号生成下一个空白编号
 * 作者：zhengsen
 * 日期：2015-03-20
 * 描述： 返回一个新的空白编号（格式：KB+年+月+三位流水号）
*/
function kb_bar_code($cy_date){
	global $DB;
	$fzx_id = FZX_ID;
	//查询采样日期当月的最后一个空白的样品编号
	if(!empty($cy_date)){
		$now_cy_date=date('Y-m-01',strtotime($cy_date));
		$next_cy_date=date('Y-m-01',strtotime("+1months",strtotime($cy_date)));
		$kb_rs=$DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE `fzx_id`='$fzx_id' AND hy_flag='-2' AND `bar_code` REGEXP '^KB[0-9]{9}$' AND create_date<'$next_cy_date' AND create_date>='$now_cy_date' order by id DESC");
		if(!empty($kb_rs)&&preg_match("/^KB\d{9}$/",$kb_rs['bar_code'])){
			$kb_bh_nums=substr($kb_rs['bar_code'],2)+1;
			$kb_bar_code='KB'.$kb_bh_nums;
			return $kb_bar_code;
		}else{
			$kb_bar_code='KB'.date('Ym',strtotime($cy_date)).'001';
			return $kb_bar_code;
		}
	}else{
		return false;
	}
}
/* 
 * 功能：把样品编号做成连续的
 * 作者: zhengsen
 * 时间：2014-6-27
 */
function get_short_barcode($b,$color=1){ 
	sort($b);

	$bar_codex=array();      
	foreach($b as $kx =>$vx){ 
		$bar_code=preg_replace("/[^0-9]/",'',$vx);
		$bar_code_next=preg_replace("/[^0-9]/",'',$b[$kx+1]);//下一个编号	
		if(($bar_code+1==$bar_code_next)){
			if(!isset($start_key)){
				$start_key=$kx;
			}else{
				$end_key=$kx;
			}
		}else{
			if(isset($start_key)){
				$bar_codex[]=$b[$start_key].'～'.$b[$kx];
				unset($start_key);
				$end_key='';
			}else{
				if(!in_array($vx,$bar_codex) && $vx != ''){
					$bar_codex[] = $vx;
				}
			}
		}
		
	}
	if($color){
		$newbar ='<font color="green"><b>'.implode( ' 、 ', $bar_codex ) . '</b></font>';
	}else{
		$newbar =implode( ' 、 ', $bar_codex );
	}
	return $newbar;
}
/* 
 * 功能：获取检测参数
 * 作者: zhengsen
 * 时间：2014-6-27
 */
 function get_jccs($water_type,$vids,$fzx_id)
 {
	 global $DB;
	 $vid_arr=array();
	$water_type=get_water_type_max($water_type,$fzx_id);
	if(!empty($vids)){
		$valueC_sql = "SELECT value_C FROM assay_value WHERE id in (".$vids.")";
		$valueC_query = $DB->query($valueC_sql);
		$i =0;
		while($valueC_rs = $DB->fetch_assoc($valueC_query))
		{
			$vid_arr[]=$valueC_rs['value_C'];
		}
		return implode('、',$vid_arr)."，";
	}else{
		return false;
	}
 }
?>
