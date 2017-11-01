<?php
/**
* 功能：多合一化验单配置表
* 作者：Mr Zhou
* 日期：2014-12-03
* 描述：配置多合一项目
*/
/*
	多合一化验单分主项目和副项目 主项目可以使虚拟项目也可以是真实项目
	$dhy_arr['str1'] 含有所有的多合一项目vid的变量
	$dhy_arr['str2'] 含有所有副项vid的变量
	$dhy_arr['xm'][主项目] = array(vid[,vid]);
	$dhy_arr['vd'][主项目][vid] = array('vid'=>'vd26','_vd0'=>'vd7');
	$dhy_arr['ad'][主项目][vid] = array('vd0'=>'需要比设置的保留位数多保留几位');
*/
$dhy_arr['str2']=$dhy_arr['str1']='';
//119五日生化需氧量
/*
$dhy_arr['xm'][119] = array(119);
$dhy_arr['vd'][119][119] = array('vd0'=>'vd27','_vd0'=>'_vd0');
$dhy_arr['ad'][119][119] = array('vd0'=>'1');*/

//125总碱度188碳酸盐189重碳酸盐575氢氧化物
// $dhy_arr['xm'][125] = array(125,188,189,575);
// $dhy_arr['vd'][125][125] = array('vd0'=>'vd8','_vd0'=>'vd16','xiang_dui_pian_cha'=>'vd17');
// $dhy_arr['vd'][125][575] = array('vd0'=>'vd9','_vd0'=>'vd18','xiang_dui_pian_cha'=>'vd19');
// $dhy_arr['vd'][125][188] = array('vd0'=>'vd10','_vd0'=>'vd20','xiang_dui_pian_cha'=>'vd21');
// $dhy_arr['vd'][125][189] = array('vd0'=>'vd11','_vd0'=>'vd22','xiang_dui_pian_cha'=>'vd23');
// $dhy_arr['vd'][125][125] = array('_vd0'=>'vd16','vd0'=>'vd17');
// $dhy_arr['vd'][125][575] = array('_vd0'=>'vd8' ,'vd0'=>'vd9' );
// $dhy_arr['vd'][125][189] = array('_vd0'=>'vd10','vd0'=>'vd11','vd27'=>'vd12');
// $dhy_arr['vd'][125][188] = array('_vd0'=>'vd13','vd0'=>'vd14','vd27'=>'vd15');

//69贾第鞭毛虫70隐孢子虫
// $dhy_arr['xm'][69] = array(69,70);
// $dhy_arr['vd'][69][69] = array('vd0'=>'vd11','_vd0'=>'vd12');
// $dhy_arr['vd'][69][70] = array('vd0'=>'vd9','_vd0'=>'vd10');
//95嗅和味96肉眼可见物
//$dhy_arr['xm'][95] = array(95,96);
//$dhy_arr['vd'][95][95] = array('vd0'=>'vd7','_vd0'=>'vd8');
//$dhy_arr['vd'][95][96] = array('vd0'=>'vd5','_vd0'=>'vd6');
// //592永久硬度598负硬度
// $dhy_arr['xm'][592] = array(592,598);
// $dhy_arr['vd'][592][592] = array('vd0'=>'vd5','_vd0'=>'vd7');
// $dhy_arr['vd'][592][598] = array('vd0'=>'vd6','_vd0'=>'vd8');
//699破碎率701磨损率739破碎率与磨损率之和
$dhy_arr['xm'][699] = array(699,701,739);
$dhy_arr['vd'][699][699] = array('vd0'=>'vd7','_vd0'=>'vd10');
$dhy_arr['vd'][699][701] = array('vd0'=>'vd8','_vd0'=>'vd11');
$dhy_arr['vd'][699][739] = array('vd0'=>'vd9','_vd0'=>'vd12');
$str1=$str2=array();
//根据配置自动生成一些参数
foreach($dhy_arr['xm'] as $z_vid => $vid_arr){
	$f_vid = array();
	foreach ($vid_arr as $key => $vid) {
		$dhy_arr[$vid]=$z_vid;
		if($z_vid!=$vid){
			$f_vid[] = $vid;
		}
	}
	$str1 = array_merge($str1,$vid_arr);
	$str2 = array_merge($str2,$f_vid);
}
$dhy_arr['str1'] = implode(',',$str1);
$dhy_arr['str2'] = implode(',',$str2);