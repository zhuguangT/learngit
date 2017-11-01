<?php
/**
 * 功能：加标回收率检测结果
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
if($_GET['vid'])
	$sqlx = "AND ao.vid=$_GET[vid]";
else
	$sqlx = '';
$hs = 30;

$sql="SELECT ao.vid,ao.tid,ao.hy_flag,ao.bar_code,ao.vd0,ao._vd0,ao.vd28,ao.vd29,ao.vd30,ao.water_type,
		assay_pay.td3,assay_pay.td6, xm.value_C,ao.xiang_dui_pian_cha,ao.ping_jia,ao.ping_jun
	FROM assay_order AS ao 
	LEFT JOIN `assay_value` xm ON ao.vid=xm.id
	LEFT JOIN assay_pay ON ao.tid=assay_pay.id
	WHERE (hy_flag IN(-40,-60,-6,-46,-66) OR hy_flag>0) $sqlx
		AND xm.`is_xcjc`='0' 
		AND ao.cyd_id IN ($ids)";

$value = $DB->query($sql);
while($v=$DB->fetch_assoc($value)){
	if( $v['hy_flag']>0 or $v['hy_flag']==-6){
		$v['hy_flag']=40;
	}else{
		$v['hy_flag']=-40;
	}
	if(substr($v['bar_code'],-1)=='J'){
	    $v['bar_code'] = str_replace('J','',$v['bar_code']);
	}
	$row=array();
	$row['v'] = $v['vid'];
	$row['b'] = $v['ping_jia'];
	$row['t'] = $v['tid'];
	$row['w'] = $v['water_type'];
	$row['z'] = $v['vd0'];
	$row['td6'] = $v['td6'];
	$row[0] = $v['vd29'];
	$row[1] = $v['vd30'];
	$row[2] = $v['vd28'];
	$row[3] = $v['vd30']+$v['vd28'];
	$row[4] = $v['ping_jun'];
	$row[5] = $v['_vd0'];//用原始值
	$row[6] = $v['td3']/2;
	$row[7] = $v['td3']/2;
	$row[8] = $v['xiang_dui_pian_cha'];
	$arr[$v['value_C']][$v['bar_code']][$v['hy_flag']] = $row;
}
$tab_id=0;
$data=array();
foreach ($arr as $value_C => $valist) {
	$hgs=$n=$i=0;
	foreach ($valist as $bar_code => $value) {
		if(!(key_exists(-40,$value) && key_exists(40,$value))){
			continue;
		}
		$i++;
		$hg  = $fw = '';
		$b   = $value[-40][b];
		$b  == '合格' && $hgs++;
		$tt  = $value[-40][t];
		$vid = $value[-40][v];

		$va0 = $value[-40][0];
		$va1 = $value[-40][1];
		$va2 = $value[-40][2];
		$va3 = (''!=$value['td6'])?$value['td6']:$value[-40][3];
		$va4 = $value[-40][4];
		$va5 = $value[-40][z];
		$va6 = $value[40][z];
		$va7 = round_value($va6*(($va3-$va1)/$va3),$tt);
		$va8 = $value[-40][8];
		$va8 = ($b=="不合格")?'<font color="red">'.$va8.'</font>':$va8;
		$hg  = get_zkfw($value[40][v],$value[40][w],$va5,'','jbhs');
		$fw  = $b.':'.$hg;

        $data[$tab_id]['data'][$bar_code]=array(
        		'b'	=>$value[-40]['b'],
        		'f'	=>$fw,
        		't'	=>$tt,
        		0	=>$va0,
        		1	=>$va1,
        		2	=>$va2,
        		3	=>$va3,
        		4	=>$va4,
        		5	=>$va5,
        		6	=>$va6,
        		7	=>$va7,
        		8	=>$va8,
        	);
        if($i==$hs){
			$data[$tab_id]['head'] =
				array('xm'=>$value_C,'bz1'=>'测试合格率：','bz2'=>'质量负责人：');
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
	$data[$tab_id]['head'] =
				array('xm'=>$value_C,'bz1'=>'测试合格率：'.(round($hgs/$n,2)*100).'%。','bz2'=>'质量负责人：');
    $tab_id++;
    if($i==0) continue;
	for($i;$i<$hs;$i++)
		$data[$tab_id-1]['data'][] = array();
}
if(intval($_GET['xz']!=1)){
	//质控表显示
	foreach($data as $tab_id=>$valist){
		$baogaoline='';
		foreach($valist['data'] as $k=>$value){
			if(empty($value)){
				$baogaoline .= '<tr class="hang"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				continue;
			}
			if($_GET['print'])
				$link = "<td>$k</td>";
			else
				$link = '<td><a href="'.$rooturl.'/huayan/assay_form.php?tid='.$value['t'].'" target="_blank">'.$k.'</a></td>';
			$baogaoline.='<tr align=center class="hang">
					'.$link.'
					<td nowrap="nowrap">'.$valist['head']['xm'].'</td>
					<td>'.$value[0].'</td>
					<td>'.$value[1].'</td>
					<td>'.$value[2].'</td>
					<td>'.$value[3].'</td>
					<td>'.$value[4].'</td>
					<td>'.$value[5].'</td>
					<td>'.$value[6].'</td>
					<td>'.$value[7].'</td>
					<td title="'.$value['f'].'">'.$value[8].'</td>
				</tr>';
		}
		$bz1 = $valist['head']['bz1'];
		$bz2 = $valist['head']['bz2'];
		echo temp('zkb/danxiang_zk/baogao_zk_jbhs2');
	}
	exit();
}
//质控表下载
$cols = 11;//总列数
$title = "$year 年 $month 月$_GET[xun]加标回收率检测结果";
$excel_id=0;
//print_rr($data);die;
foreach ($data as $tab_id => $tab_data) {
	$rows = 0;//行数
	$first_row =1;
	$objPHPExcel->setActiveSheetIndex($excel_id++);
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
	//标题 合并单元格
	$rows++;
	$objPHPExcel->getActiveSheet()->setTitle($tab_data['head']['xm']);
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':K'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rows)->getFont()->setName('宋体')->setSize(15)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	//第二行
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"样品编号");
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"检测项目");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"标准样品浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"标准样品加入体积(ml)");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"原水样体积(ml)");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"合并水样体积(ml)");
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"标准加入浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,"加标后测定值(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$rows,"原水样浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('J'.$rows,"原水样折算后浓度(mg/L)");
	$objPHPExcel->getActiveSheet()->setCellValue('K'.$rows,"回收率(%)");
	foreach ($tab_data['data'] as $key => $value) {
		$rows++;
		if(empty($value)) continue;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$key);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$tab_data['head']['xm']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value[0].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value[1].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value[2].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value[3].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$value[4].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$value[5].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$rows,$value[6].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$rows,$value[7].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$rows,$value[8].' ');
	}
	$last_cell = 'K'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'K'.($rows+2))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':K'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':K'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id]['head']['bz2']);

	$objPHPExcel->createSheet();
}