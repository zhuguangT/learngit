<?php
/**
 * 功能：实验室平行样检测结果
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$fzx_id = FZX_ID;
if(intval($_GET['vid']))
	$sqlx = ' AND `ao`.`vid` = '.intval($_GET['vid']);
else
	$sqlx = '';

$sql="SELECT ao.tid,ao.vid,ao.water_type,vd0,bar_code,assay_pay.td2,assay_pay.userid,assay_pay.userid2,xm.value_C,xiang_dui_pian_cha,ping_jia,ping_jun,hy_flag
    FROM assay_order AS ao LEFT JOIN assay_pay ON ao.tid=assay_pay.id 
    LEFT JOIN `assay_value` xm ON ao.vid = xm.id 
    WHERE (hy_flag IN(-20,-40,-60,-6) OR hy_flag>0) $sqlx 
    	AND ao.cyd_id IN ($ids) 
    	AND xm.`is_xcjc`='0'
    	AND ao.sid>0
    ORDER BY ao.id";
$value=$DB->query($sql);
$arr = array();
$hs=26;
$bz1=$bz2=$bz3='';
while($v=$DB->fetch_assoc($value)){
    if($v['hy_flag']>0 || $v['hy_flag']==-6){
        $v['hy_flag']=20;
    }else{
		$v['hy_flag']=-20;
	}
    if(substr($v['bar_code'],-1)=='P'){
        $v['bar_code'] = str_replace('P','',$v['bar_code']);
    }
    $arr[$v['vid']]['head'][0]=$v['value_C'];
    $arr[$v['vid']]['head'][1]=$v['td2'];
    $arr[$v['vid']]['head'][2]= ($v['userid2']=='') ? $v['userid'] : $v['userid']." ".$v['userid2'];

	//$arr[$v['vid']]['data'][$v['bar_code']]['x']  = $v['value_C'];
	$arr[$v['vid']]['data'][$v['bar_code']]['t']  = $v['tid'];
    $arr[$v['vid']]['data'][$v['bar_code']]['bz'] = $v['ping_jia'];
    $arr[$v['vid']]['data'][$v['bar_code']]['pj'] = $v['ping_jun'];
    $arr[$v['vid']]['data'][$v['bar_code']]['xd'] = $v['xiang_dui_pian_cha'];
    $arr[$v['vid']]['data'][$v['bar_code']][$v['hy_flag']]=$v['vd0'];
    //加如两个函数需要用到的参数，水样类型和项目id。
	$arr[$v['vid']]['data'][$v['bar_code']][$v['hy_flag']]['water_type'] = $v['water_type'];
	$arr[$v['vid']]['data'][$v['bar_code']]['vid'] = $v['vid'];
}
//删除没有做平行的站点数据
$tab_id = 0;
foreach ($arr as $vid => $tab_data) {
	$hgs=$n=$i=0;
	foreach ($tab_data['data'] as $bar_code => $value) {
		if(!(key_exists(20,$value) && key_exists(-20,$value))){
			continue;
		}
		$i++;
		$data[$tab_id]['head'] = $tab_data['head'];
        $arr[$vid]['data'][$bar_code]['xd'] = ($value['xd']!='')?abs($value['xd']):'';
        $value['bz'] == '合格' && $hgs++;
        //获取精密度范围
        $zkfw = get_zkfw($value['vid'],$value[20]['water_type'],$value['pj'],'','sn_jmd');
		if($zkfw==''){
			$fw = '-';
		}else{
			$fw = $zkfw;
		}

		$arr[$vid]['data'][$bar_code]['f'] = ($fw =='合格') ? '':$fw;
        $data[$tab_id]['data'][$bar_code] = $arr[$vid]['data'][$bar_code];
		if($i==$hs){
    		$data[$tab_id]['head']['bz1'] = '测试合格率：';
    		$data[$tab_id]['head']['bz2'] = '质控意见：';
    		$data[$tab_id]['head']['bz3'] = "备注：";
    		$data[$tab_id]['head']['bz4'] = "质量负责人：";
			$i=0;
			$n+=$hs;
			$tab_id++;
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
    $data[$tab_id]['head']['bz1'] = '测试合格率：'.$hg;
    $data[$tab_id]['head']['bz2'] = '质控意见：本次监测实验室平行样合格率'.$hg;
    $data[$tab_id]['head']['bz3'] = "备注：浓度低于检出限的，以检出限计";
    $data[$tab_id]['head']['bz4'] = "质量负责人：";
	$tab_id++;
    if($i==0) continue;
	for($i;$i<$hs;$i++)
		$data[$tab_id-1]['data'][] = array();
}
unset($arr);
if(intval($_GET['xz']!=1)){
	//质控表显示
	foreach($data as $kk=>$vv){
		$i=0;
		$valuec = $vv['head'][0];
		$ff = $vv['head'][1];
		$ry = $vv['head'][2];
		$baogaoline = '';
		foreach($vv['data'] as $k=>$v){
			if(empty($v)){
				$baogaoline.='<tr align=center class="hang"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				continue;
			}
			$i++;
			if($v['bz']=='不合格')
				$v['bz']='<span style="color:#FF0000">'.$v['bz'].'</span>';
			if($_GET['print'])
				{$link = "<td>$k</td>";}
			else
				{$link = "<td><a href=\"$rooturl/huayan/assay_form.php?tid=$v[t]\" target=\"_blank\">$k</a></td>";}
			$baogaoline.="<tr align=center class=\"hang\"><td>$i</td>$link<td>$v[20]</td><td>".$v['-20']."</td><td>$v[pj]</td><td>$v[xd]</td><td>$v[f]</td><td>$v[bz]</td></tr>";
		}
		$bz1 = $vv['head']['bz1'];
		$bz2 = $vv['head']['bz2'];
		$bz3 = $vv['head']['bz3'];
		$bz4 = $vv['head']['bz4'];
		echo temp('zkb/danxiang_zk/baogao_zk_syspx1');
	}
	exit();
}
//质控表下载
$cols = 8;//总列数
$title = "$year 年 $month 月 $_GET[xun]实验室平行样检测结果";
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
	//标题 合并单元格
	$rows++;
	$objPHPExcel->getActiveSheet()->setTitle($tab_data['head']['0']);
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rows)->getFont()->setName('宋体')->setSize(15)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	//第二行
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"项目:");
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$tab_data['head'][0]);
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"分析方法:");
	$objPHPExcel->getActiveSheet()->mergeCells('D'.$rows.':E'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$tab_data['head'][1]);
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"分析人员:");
	$objPHPExcel->getActiveSheet()->mergeCells('G'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$tab_data['head'][2]);
	//第三行
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"序号:");
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"平行样编号");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"第一次测试(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"第二次测试(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"平行样均值");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"精密度(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"精密度允许偏差(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,"结果评定");
	$i=1;
	foreach ($tab_data['data'] as $key => $value) {
		$rows++;
		if(empty($value)) continue;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$i++);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$key);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['20'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['-20'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['pj'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value['xd'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$value['f'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$value['bz'].' ');
	}
	$last_cell = 'H'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'H'.($rows+4))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
	
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz2']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz3']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz4']);

	$objPHPExcel->createSheet();
}