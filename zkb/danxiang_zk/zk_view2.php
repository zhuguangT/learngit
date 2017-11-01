<?php
/**
 * 功能：密码平行样检测结果
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$fzx_id = FZX_ID;
if($_GET['vid'])
	$sqlx = "and ao.vid=$_GET[vid]";
else
	$sqlx = '';
$hs = 28;
$sql="SELECT ao.tid,ao.vid,ao.water_type,bar_code,ao.sid,ao.vd0,assay_pay.td2,assay_pay.userid,assay_pay.userid2,xm.value_C,xiang_dui_pian_cha,ping_jia,ping_jun,hy_flag 
	FROM `assay_order` AS ao LEFT JOIN assay_pay ON ao.tid=assay_pay.id 
	LEFT JOIN `assay_value` xm ON ao.vid = xm.id 
	WHERE hy_flag IN (-6,5,25,45,65) $sqlx 
		AND ao.cyd_id IN ($ids) 
		AND ao.sid>0 
		AND xm.`is_xcjc`='0'
		ORDER BY  ao.bar_code";
$value=$DB->query($sql);
$arr = array();
while($v=$DB->fetch_assoc($value)){
	/*
		在系统中如果做加标和室内平行，flag为负数的不会变化，非负数的才会变化
		所以如果做了现场平行的话，该站点会有两个样，一个-6，一个+5
		-6是不会变的，只有+5可能会变，无论怎么变都会是>=0的
		为了便于统计，密码平行样1将flag统一为5
	*/
	if($v['hy_flag'] >= 0){
		$v['hy_flag']=5;
	}

	$arr[$v['vid']]['head'][0]	= $v['td2'];	//方法
	$arr[$v['vid']]['head'][1]	= $v['value_C'];
	$arr[$v['vid']]['head'][2]	= ($v['userid2']!='') ? $v['userid'] : $v['userid'].' '.$v['userid2'];

	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['t']	= $v['tid'];
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['b']	= $v['ping_jia'];
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['c']	= $v['bar_code'];
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['f']	= $v['ping_jia'];
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['v']	= getnumber(getvalue($v['vd0']));
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['p']	= getnumber(getvalue($v['ping_jun']));
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['x']	= abs(getnumber($v['xiang_dui_pian_cha']));
	//加如两个函数需要用到的参数，水样类型和项目id。
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['water_type'] = $v['water_type'];
	$arr[$v['vid']]['data'][$v['sid']][$v['hy_flag']]['vid'] = $v['vid'];

}

//删除没有做密码平行的站点数据
$tab_id = 0;
foreach ($arr as $vid => $tab_data) {
	$hgs = $n =$i=0;
	foreach ($tab_data['data'] as $sid => $value) {
		if(!(key_exists(5,$value) && key_exists(-6,$value))){
			unset($arr[$vid]['data'][$sid]);
			continue;
		}
		$i++;
		$data[$tab_id]['head'] = $tab_data['head'];
		$value['-6']['b'] == '合格' && $hgs++;
		$data[$tab_id]['data'][$sid] = $arr[$vid]['data'][$sid];
		if($i==$hs){
			$data[$tab_id]['head']['bz1'] = "合格率：";
			$data[$tab_id]['head']['bz2'] = "质控意见：";
			$data[$tab_id]['head']['bz3'] = "备注：";
			$i=0;
			$tab_id++;
			$n+=$hs;
		}
	}
	if($i==0 && $n==0){
		continue;	//没有数据的项目直接跳过
	}elseif($n%$hs==0 && $i==0){
		$tab_id--;	//如果满足整页没有多余的数据（即正好是1页，2页，3页……） 将多增加的页数号减1
	}else{
		$n+=$i;		//如果余出的数据不足一页
	}
	$hg = (round($hgs/$n,2)*100).'%';
	$data[$tab_id]['head']['bz1'] = "合格率：".$hg;
	$data[$tab_id]['head']['bz2'] = "质控意见：本次检测现场平行样合格率".$hg;
	$data[$tab_id]['head']['bz3'] = "备注：浓度低于检出限的，以检出限计";
	$tab_id++;
    if($i==0) continue;
	for($i;$i<$hs;$i++)
		$data[$tab_id-1]['data'][] = array();
}
unset($arr);
if(intval($_GET['xz']!=1)){
	//质控表显示
	foreach($data as $kk=>$vv){
		$xx = 1;
		$baogaoline = '';
		$ff = $vv['head'][0];//方法
		$xm = $vv['head'][1];//项目
		$ry = $vv['head'][2];//人员
		
		foreach($vv['data'] as $k =>$v){
			if(empty($v)){
				$baogaoline.='<tr align=center class="hang"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				continue;
			}
			$k1 = $v['5']['c'];		//密码样A编号
			$k2 = $v['-6']['c'];	//密码样B编号
			$v1 = $v['5']['v'];		//密码样A检测值
			$v2 = $v['-6']['v'];	//密码样B检测值
			$pj = $v['-6']['p'];	//平均值
			$b  = $v['-6']['b'];	//评价
			$t1 = $v['5']['t'];		//密码样A化验id
			$t2 = $v['-6']['t'];	//密码样B化验单id
			$pc = $v['-6']['x'] ;	//相对偏差
			//获取范围
			$water_type = $v['-6']['water_type'];
			$gvid = $v['-6']['vid'];
			//get_zkfw($vid,$water_type,$nd,$jieguo='',$leixing='sn_jmd')
			
			$zkfw = get_zkfw($gvid,$water_type,$pj,'','sn_jmd');
			if($zkfw==''){
				$fw = '-';
			}else{
				$fw = $zkfw;
			}
			$b	!= '合格' && $b	= '<font color="red">'.$b.'</font>';
			$url = $rooturl.'/huayan/assay_form.php?tid=';
			if($_GET['print'])
				$link = "<td>$k1</td><td>$v1</td><td>$k2</td>";
			else
				$link = "<td><a href=\"$url$t1\" target=\"_blank\">$k1</a></td><td>$v1</td><td><a href=\"$url$t2\" target=\"_blank\">$k2</a></td>";
			$baogaoline.="<tr align=center class=\"hang\"><td>$xx</td>$link<td>$v2</td><td>$pj</td><td>$pc</td><td>$fw</td><td>$b</td></tr>";
			$xx ++;
		}
		$bz1 = $vv['head']['bz1'];
		$bz2 = $vv['head']['bz2'];
		$bz3 = $vv['head']['bz3'];
		$bz4 = $vv['head']['bz4'];
		echo temp('zkb/danxiang_zk/baogao_zk_mmpx1');
	}
	exit();
}
//质控表下载
$cols = 9;//总列数
$title = "$year 年 $month 月 $_GET[xun]密码平行样检测结果";
$excel_id=0;
foreach ($data as $tab_id => $tab_data) {
	$rows = 0;//行数
	$first_row =1;
	$objPHPExcel->setActiveSheetIndex($excel_id++);
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(21);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(13);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(13);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
	//标题 合并单元格
	$rows++;
	$objPHPExcel->getActiveSheet()->setTitle($data[$tab_id]['head']['1']);
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rows)->getFont()->setName('宋体')->setSize(15)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	//第二行
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"项目:");
	$objPHPExcel->getActiveSheet()->mergeCells('B'.$rows.':C'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$tab_data['head'][1]);
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"检测方法:");
	$objPHPExcel->getActiveSheet()->mergeCells('E'.$rows.':F'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$tab_data['head'][0]);
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"检测人员:");
	$objPHPExcel->getActiveSheet()->mergeCells('H'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$tab_data['head'][2]);
	//第三行
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"序号:");
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"平行样编号");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"测试浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"平行样编号");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"测试浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"平行样均值");
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"精密度(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,"精密度允许偏差(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$rows,"结果评定");
	$i=1;
	foreach ($tab_data['data'] as $key => $value) {
		$rows++;
		if(empty($value)) continue;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$i++);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value['5']['c'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['5']['v'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['-6']['c'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['-6']['v'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value['-6']['p'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$value['-6']['x'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$value['5']['f'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$rows,$value['-6']['b'].' ');
	}
	$last_cell = 'I'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'I'.($rows+4))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
	
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz2']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz3']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':I'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz4']);

	$objPHPExcel->createSheet();
}