<?php
/**
 * 功能：报告查看和下载
 * 作者：zhengsen
 * 日期：2015-08-25
 * 描述：兰州
*/
include '../temp/config.php';
include('../baogao/bg_func.php');
include INC_DIR . "cy_func.php";
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];                   //分中心的id
$rec_id= get_int( $_GET['cid'] );    	//cy_rec中的id
$cydid = get_int( $_GET['cyd_id']); 	//cy表中的id
$ssid  = get_int( $_GET['sid']);		//站点ID
$lx    = get_int( $_GET['lx']);			//1为显示预览报告  2为下载报告

//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//生活饮用水106项模板/除了106项外，其他项目不显示标准也不判断
$yys_jcbz_mb	= array();
$jcbz_yys	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='mr_mb' AND module_value4='5'");
$yys_jcbz_mb['5']= explode(',',$jcbz_yys['module_value1']);//生活饮用水标准
if($lx == ''){
	$lx =1;//默认为预览
}
//定义检测报告的高度
$table_height='min-height:26cm';

if($lx==2){
	$fenye="<br  style=\"page-break-before:always\">";
}else{
	$fenye="<div style=\"page-break-before:always\"></div>";
}
$jc_date=array();

//echo count($arr[$sid]);
//获取报告编号、日期、模板
$detail_rs=$DB->fetch_one_assoc("SELECT * FROM report WHERE cyd_id='".$cydid."' AND cy_rec_id='".$rec_id."'");
//获取项目排序的id
$xm_px = $detail_rs['xm_px'];
$beizhu = $detail_rs['beizhu'];//备注

//设置的报告项目
$bg_xm_arr=array();
if(!empty($detail_rs['bg_xm'])){
	$bg_xm_arr=explode(",",$detail_rs['bg_xm']);
}
$xm_name_arr= array();
$ywc = $wwc = 0;
$sql   = "SELECT ao.site_name,ao.bar_code,ao.hy_flag,ao.ping_jun,ao.create_date,ao.sid,ao.vid,ao.tid,ao.vd0,ao.vd26,ap.assay_element,ap.td2,ap.td3,ap.td4,ap.td5,ap.td32,ap.td31,ap.td33,ap.td34,ap.over,ap.create_date as ap_create_date,ap.unit,ap.sign_01,ap.sign_date_03,ap.sign_date_04,ap.jc_xz,ap.is_xcjc,ao.cyd_id FROM assay_order as ao JOIN assay_pay as ap ON  ao.tid=ap.id WHERE ao.cyd_id='".$cydid."' AND ao.cid='".$rec_id."' AND ao.hy_flag>='0' AND ao.sid > '0'";//现场平行样的报告也需要显示
$vd=$DB->query($sql);//查询assay_order和assay_pay表，得到报告上面需要的数据
while($v=$DB->fetch_assoc($vd)){
	//通过该项目的 tid 和 hy_flag 来查看该项目的空白值
	$xm_kb = $DB->fetch_one_assoc("SELECT vd0 FROM assay_order WHERE tid=".$v['tid']." AND hy_flag='-2'");
	$xm_kbarr[$v['vid']] = $xm_kb['vd0'];//取得该项目的空白值

	$arr[$v['vid']]  = $v;
	//并不是用户选定的项目，不显示到检测报告中，也不统计其信息。
	if(!empty($bg_xm_arr) && !in_array($v['vid'],$bg_xm_arr)){
		unset($arr[$v['vid']]);
		continue;
	}
	$xm_name_arr[]	= $v['assay_element'];//项目名称
	if(!empty($v['td32'])){
			$wd[] 	     = $v['td32'];		  //温度	
	}
	if(!empty($v['td33'])){
			$sd[] 	     = $v['td33'];		  //湿度	
	}
	if(!empty($v['td31'])&&!in_array($v['td31'],$jc_date)){

		$jc_date[] = strstr($v['td31'],'.')?str_replace('.', '-',$v['td31']):$v['td31'];//结束日期
	}
	if(!empty($v['td34'])&&!in_array($v['td34'],$jc_date)){

		$jc_date[] = strstr($v['td34'],'.')?str_replace('.', '-',$v['td34']):$v['td34'];//结束日期
	}
	$pay['over'] == $qzjb ? $ywc++ : $wwc++;			            //统计站点完成数量
	
}
//获取每份报告的报告编号
$bgbh = $detail_rs['bg_lx'].$detail_rs['year'].$detail_rs['xuhao']."-".$detail_rs['bg_bh'];//&mdash;

$nian = $detail_rs['year'];
if(empty($nian)){
	$nian="<span style=\"padding-left:40px\"></span>";
}
if(!empty($detail_rs['bg_bh'])&&!empty($detail_rs['bg_lx'])){
	$bh=$detail_rs['bg_lx'].get_bgbh($detail_rs['bg_bh']);
} 
if(empty($bh)){
	$bh="<span style=\"padding-left:40px\"></span>";
}
$mbrows =$DB->fetch_one_assoc("SELECT * FROM `report_template` WHERE  id= '".$detail_rs['te_id']."'");
//1代表英文模板，0代表中文模板
$is_eglish=$mbrows['is_eglish'];
//模板信息
//print_rr($mbrows);
$sy_lx = $mbrows['water_type'];   // 水样类型
$fm_mb = $mbrows['fm_mb'];        //  封面模板
$sm_mb = $mbrows['sm_mb'];       //  说明模板
$bt_mb = $mbrows['bt_mb'];       // 表头模板
$one_page_mb = $mbrows['one_page_mb']; //  报告检测数据模版第一页
$two_page_mb=$mbrows['two_page_mb']; //  报告检测数据模版第二页
$sj_mb = $mbrows['sj_line_mb'];     //  数据页模板
$qm_mb = $mbrows['qm_mb'];     //  签名模板
$hang1 = $mbrows['hang1'];     // 带表头分页行数
$hang2 = $mbrows['hang2'];	   // 不带表头分页行数

//报告打印日期
if($detail_rs['bg_dy_date']!=''&&$detail_rs['bg_dy_date']!='0000-00-00'){
	if($is_eglish){
		$bgrq=date('jS F Y',strtotime($detail_rs['bg_dy_date']));
	}else{
		$bgrq=date('Y年m月d日',strtotime($detail_rs['bg_dy_date']));
	}
}else{
	if($is_eglish){
		$bgrq=date("jS F Y");
	}else{
		$bgrq=date("Y年m月d日");
	}
}

//送、采样日期
if($detail_rs['sj_date']=='0000-00-00'){
	$detail_rs['sj_date']='';
}else{
	if($is_eglish){
		$detail_rs['sj_date']=date('jS F Y',strtotime($detail_rs['sj_date']));
	}
}
if(empty($detail_rs['date_lx'])){
	if($is_eglish){
		$date_lx='Sample collection date';
	}else{
		$date_lx='收样日期';
	}
}else{
	$date_lx=$detail_rs['date_lx'];
}

$jcwd='';
$jcrq='';
$jcsd='';
if(!empty($wd)){
	if(min($wd)==max($wd)){
		$jcwd = "温度（".min($wd).")°C";   //报告显示温度具体值 
	}else{
		$jcwd = "温度（".min($wd)."~".max($wd).")°C";   //报告显示温度区间  
	}
}
//对日期进行处理，处理成0000-00-00形式
foreach ($jc_date as $key => $value) {
	$v_num = strlen($value);
	if($v_num < 10 && !empty($value)){
		$jcdate = explode('-',$value);
		if(strlen($jcdate[1])<2){
			$jcdate[1] = '0'.$jcdate[1];
		}
		if(strlen($jcdate[2])<2){
			$jcdate[2] = '0'.$jcdate[2];
		}
		$value = $jcdate[0].'-'.$jcdate[1].'-'.$jcdate[2];
		$jc_date[$key] = $value;
	}
}
if(!empty($jc_date)){
	if(count($jc_date)==1){
		$jcrq=date("Y年m月d日",strtotime($jc_date[0]));    //检测日期
	}else{
		$min_date=min($jc_date);
		$max_date=max($jc_date);
		if($min_date == $max_date){
			$jcrq=date("Y年m月d日",strtotime($min_date));  // 检测日期区间
		}else{
			$jcrq=date("Y年m月d日",strtotime($min_date))."～".date("Y年m月d日",strtotime($max_date));  // 检测日期区间
		}
		
	}
}
if(!empty($sd)){
	if(min($sd)==max($sd)){
		$jcsd = "湿度（".min($sd).")";    //湿度值	
	}else{
		$jcsd = "湿度（".min($sd)."~".max($sd).")";    //湿度区间
	}
}


// 获得表头的数据
  $xc=$DB->fetch_one_assoc("SELECT sites.site_address,cy.sh_user_qz_date,cy.cy_flag,cy.yp_count,cy.fzx_id,cy.ys_date,cy.cy_dept,cy.jc_dept,cy.cy_date,cy.site_type,cy.cy_rwjs_qz_date,cy_rec.ys_zt,cy_rec.site_name,cy_rec.cy_note,cy_rec.water_type,cy_rec.bar_code as rec_bar_code,leixing.lname as syname FROM cy,cy_rec,sites,leixing  WHERE cy.id=cy_rec.cyd_id and leixing.id = cy_rec.water_type and cy.id='".$cydid."' and cy_rec.sid='".$_GET['sid']."' and cy_rec.id='".$rec_id."' ");

	if(empty($xc['water_type'])){
		$water_type_bh=substr($xc['jj'],1,1);
		$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
		$xc['water_type']=get_water_type_max($water_type,$fzx_id);
	}
	if(!empty($detail_rs['wtdw'])){
		$wtdw  = str_replace("\n","<br/>",$detail_rs['wtdw']); //委托单位
	}else{
		$wtdw  = str_replace("\n","<br/>",$xc['cy_dept']); //委托单位
	}

	
	$ypzt  = $detail_rs['yp_zt'];//样品状态
	if(empty($ypzt)){//如果为空则从report表中直接读取样品状态
		$ypzt = $xc['ys_zt'];
	}
	$cydd  = $detail_rs['cy_place']?$detail_rs['cy_place']:$xc['site_address']; //采样地点
	$cydw  = $xc['jc_dept'];            //采样单位
	$ypbh  = $xc['rec_bar_code'];                 //样品编号
	$syrq  = $xc['ys_date'];             //收样日期
	$jssj  = $xc['sh_user_qz_date'];    //样品接收时间
	$ypmc  = $xc['site_name'];          //站点名称
	$sylx  = $xc['syname'];				//水样类型
	$cysj  = $xc['cy_date']; 			//采样时间
	$yply  = $xc['cy_flag'];            //样品来源
	if(!empty($detail_rs['date_lx'])){
		$syrq = $detail_rs['sj_date'];
	}
	if(!empty($syrq)){
		$syrq	= date("Y年m月d日",strtotime($syrq));
	}
	if(!empty($detail_rs['jy_lb'])){
		$yply	= $detail_rs['jy_lb'];
	}else{
		if($yply == '0'){
			$yply = '送样';
		}else{
			$yply = '采样';
		}
	}
//print_rr($xc);

//查询检测单位信息
$jcdw =$DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id` = '".$fzx_id."'");

  
// 查询中水质名称
if($detail_rs['water_type']){
	$lx_id	= $detail_rs['water_type'];
}else{
	$lx_id	= $xc['water_type'];
	$sylx	= '';//report表中没有存储water_type时，就不显示水样类型
}
$bzlx=$DB->fetch_one_assoc("SELECT lname,e_lname FROM leixing WHERE id='".$lx_id."'");
if($is_eglish){//真为英文模板 假为中文模板
	$szlx=$yplb=$bzlx['e_lname'];
}else{
	$szlx=$yplb= $bzlx['lname'];
}

$water_type = $xc['water_type'];//获取水样类型，这个变量用于result_chuli_sj.php中

//判断此模板所对应的水样类型
if(!empty($sy_lx)){
	$xc['water_type'] = $sy_lx;//水样类型
}
if($detail_rs['water_type']){
	$sylx	= $bzlx['lname'];
}else{
	$sylx	= '';//report表中没有存储water_type时，就不显示水样类型
}
//查出水样类型的父级id
$water_type_max=get_water_type_max($xc['water_type'],$fzx_id);
if(empty($detail_rs['jcbz_id'])){
    //先查询当前水样类型的检出限，如果没有再查询父级的检出限
    $jcbz_sql="SELECT n.module_value1,n.module_value2,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' AND module_value2='".$xc['water_type']."'";
    $jcbz_query=$DB->query($jcbz_sql);
    $pd_water_type=$xc['water_type'];
    if(!mysql_affected_rows()){
        $jcbz_sql="SELECT n.module_value1,n.module_value2,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' AND module_value2='".$water_type_max."'";
        $jcbz_query=$DB->query($jcbz_sql);
        $pd_water_type=$water_type_max;
    }
}else{
    //本份检测报告单独配置了判定标准
    $jcbz_sql   = "SELECT n.module_value1,n.module_value2,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE n.`id`='{$detail_rs['jcbz_id']}'";
    $jcbz_query = $DB->query($jcbz_sql);
}
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	//记录各个国标里的项目的项目名称
	if(!empty($jcbz_rs['value_C'])){
		$value_name_arr[$jcbz_rs['vid']]	= $jcbz_rs['value_C'];
	}
	//跳过非国家强制标准
	if(!empty($yys_jcbz_mb[$water_type_max]) && !in_array($jcbz_rs['vid'], $yys_jcbz_mb[$water_type_max])){
		continue;
	}
	//用来判定的标准限值
	if(!empty($jcbz_rs['panduanyiju'])){
		$pd_jcbzarr[$jcbz_rs['vid']]=$jcbz_rs['panduanyiju'];
	}else{
		$pd_jcbzarr[$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
	//用来显示的标准限值
	if($is_eglish&&!empty($jcbz_rs['eglish_xz'])){
		$jcbzarr[$jcbz_rs['vid']]=$jcbz_rs['eglish_xz'];
	}else{
		$jcbzarr[$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
}
//根据不同的模板选择不同的检出限
if($mbrows['te_name'] == '硫酸铝' || $mbrows['te_name'] == '氯化铁' || $mbrows['te_name'] == '活性炭' || $mbrows['te_name'] == '聚氯化铝'){
	include_once 'result_bgxm_jcx.php';//包含返回检出限的PHP文件
}

//如果是英文模板查询项目的英文名称
if($is_eglish){
	$e_item_sql="SELECT av.id,av.eglish_item,aj.eglish_item as aj_eglish_item  FROM assay_value av JOIN assay_jcbz aj ON av.id=aj.vid JOIN n_set n ON aj.jcbz_bh_id=n.id WHERE n.module_name='jcbz_bh' AND module_value3='1' AND module_value2='".$water_type_max."' ";
	$e_item_query=$DB->query($e_item_sql);
	while($e_item_rs=$DB->fetch_assoc($e_item_query)){
		if($e_item_rs['aj_eglish_item']){
			$e_item_arr[$e_item_rs['id']]=$e_item_rs['aj_eglish_item'];
		}else{
			$e_item_arr[$e_item_rs['id']]=$e_item_rs['eglish_item'];
		}
	}
}
//查询执行标准
$bz=array();
$bz=$DB->fetch_one_assoc("SELECT module_value1 FROM n_set WHERE module_value2='".$pd_water_type."' and module_value3 = '1' AND module_name='jcbz_bh'");
$zxbz= $bz['module_value1'];

//print_rr($arr);exit();
$page=0;
$i=0;
$vid_nums=count($arr);
if($vid_nums<=$hang1){
	$z_page=1;
}else{
	$z_page=ceil(($vid_nums-$hang1)/$hang2)+1;	
}
//表头参数处理
if(in_array($mbrows['te_name'],array('石英砂'))){
	$tmp_xm_name	= array_slice($xm_name_arr,0,4);//只取前4个项目，多了显示不开
	if(count($xm_name_arr) == count($tmp_xm_name)){
		$xm_name_str	= implode('、',$tmp_xm_name)."（共{$vid_nums}项）";
	}else{
		$xm_name_str	= implode('、',$tmp_xm_name)."等（共{$vid_nums}项）";//检测项目
	}
	$ypmc	= $ypmc.$sylx;;//样品名称显示
}else{
	$ypmc	= $ypmc.$sylx;
}

//这里开始显示报告
//$conu=count($shu);   #CCFFFF
$bgnrs .="<html><head><style type=\"text/css\">body{margin:0px} .td_border td{border:1px solid}</style></head><body>";//去空白
$bgnrs .="<div style=\"background-color: #000000;padding-top:50px;padding-bottom:50px\">";
if($lx != 3){  //类型为3是为excel  只有数据没有封面和说明
	if(!empty($fm_mb)){
		$bgnrs .= temp($fm_mb);
	}
	if($lx == 2){
		$bgnrs.=$fenye;//报告封面模板
	}
	//$bgnrs .=temp($bg_messge);   //报告信息模板
	if($mbrows['jiego'] =='2'){
		if(!empty($sm_mb)){
			$bgnrs .= temp($sm_mb);     //说明模板
			$bgnrs .= "<div style=\"height:3px;width:19cm\"></div>";
			$bgnrs .= $fenye;
		}
		
	}
}
$bgnrs.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';
if(!empty($bt_mb)){
	$bgnrs.=temp($bt_mb);//表头模板
	$bgnrs.=temp($qm_mb);//签名模板
	$bgnrs.="</div><div style=\"height:3px;width:19cm\"></div>";//用于模板之间的间隙
	$bgnrs.=$fenye;//下载时用
}
//项目排序
if(!empty($xm_px)){
	$rs_px = $DB->fetch_one_assoc("select module_value1 from n_set where id={$xm_px}");
	if(!empty($rs_px)){
		$xm_px_arr=explode(",",$rs_px['module_value1']);
		$arr_temp=array();
		foreach($xm_px_arr as $key=>$value){
			if(!empty($arr[$value])){
				$arr_temp[$value]=$arr[$value];
				unset($arr[$value]);
			}
		}
		$arr=$arr_temp+$arr;
	}
}
$all_value_num	= count($arr);
foreach($arr as $key =>$value){
	$i++;
	$pd='';
	/*assay_pay表字段含义：assay_element(项目名称),td2(检测方法标准号),td3(检出限),td4(仪器名称),td5(仪器编号),td32(温度),td33(湿度),create_date(开始检测	日期)sign_date_04(结束日期),unit(单位),sign_01(检测人员),jc_xz(检测限值)*/	
	$xmid=$key; //项目的id
	$yiju   = $value['td2']; 	 //检测标准 
	$jcname = $value['sign_01']; //项目检测人员名称
	if($is_eglish&&!empty($e_item_arr[$xmid])){
		$xmname=$e_item_arr[$xmid];//英文项目名称
	}else{
		if(!empty($value_name_arr[$xmid])){//如果国标里面有次项目的名称就用国标里的
			$xmname	= $value_name_arr[$xmid];
		}else{
			$xmname = $value['assay_element'];//项目名称
		}
	}
	//$jcbz   = $value['td3'];     //检出限
	$unit   = $value['unit'];    //检测项目单位	
	$jcyq   = $value['td4'];     //检测仪器
	if($value['is_xcjc'] == '1'){
		$tid	= "'cyd_id:{$value['cyd_id']}'";
	}else{
		$tid	= "'{$value['tid']}'";     //化验单id
	}
	$yysz_pjhl	= $value['vd26']; //带入饮用水中的评价*含量
	if($mbrows['te_name'] == '硫酸铝' || $mbrows['te_name'] == '氯化铁' || $mbrows['te_name'] == '活性炭' || $mbrows['te_name'] == '聚氯化铝' ){//选择不同的检出限
		include 'result_sjmb_jcx.php';
	}else{
		$jc_xz  = $jcbzarr[$xmid];//检测限值
	}
	if(!empty($show_shuju_arr) && !in_array($value['over'],$show_shuju_arr)){
		$jie	= '';
	}else{
		//室内平行项目需要取平均值   
		if($value['ping_jun'] != ''&&$global['bg_pingjun']){
			$jie = $value['ping_jun'];//检测结果值
		}else{
			$jie = $value['vd0'];     //检测结果值
		} 
		if(in_array($xmid,$global['modi_data_vids'])&&$jie<='0'&&$jie!=''){
			$jie='未检出';
		}else if($xmid=='689' && !empty($jie)){//粒径项目的特殊处理 vid为689
			$jie_lijing_arr	= json_decode($jie,true);//粒径结果为json格式
			if(is_array($jie_lijing_arr)){
				$jie	= array();
				foreach ($jie_lijing_arr as $value) {
					switch ($value['name']) {
						case (stristr($value['name'],"d")):
							$jie["有效粒径({$value['name']})"]['unit']	= 'mm';
							$jie["有效粒径({$value['name']})"]['vd0']	= $value['value'];
							break;
						case (stristr($value['name'],"k") || stristr($value['name'],"K")):
							$jie["不均匀系数({$value['name']})"]['unit']= '';
							$jie["不均匀系数({$value['name']})"]['vd0']	= $value['value'];
							break;
						default:
							$jie["粒径({$value['name']})"]['unit']	= '%';
							$jie["粒径({$value['name']})"]['vd0']	= $value['value'];
							break;
					}
				}
			}else{
				$jie	= "粒径结果出现问题，请联系管理员";
			}
		}
	}
	if($jc_xz==''||$jc_xz=='-'||$jc_xz=='--'){
		$jc_xz='--';	
	}
	if($jie!='' && !is_array($jie)){
		if($jc_xz=='--'){
			$pd='--';
		}else{
			if($xmid==487 && $water_type_max==5){

			}else{
				$return_data=is_chaobiao($xmid,$pd_water_type,$pd_jcbzarr[$xmid],$jie,$is_eglish);
				if($return_data['status']){
					$jie='<span style="color:red">'.$jie.'</span>';
					$pd='<span style="color:red">'.$return_data['info'].'</span>';
				}else{
					$pd=$return_data['info'];//合格判定
				}
			}
		}
		if($jie=='未检出'&&$is_eglish){
			$jie='Not detected';
		}
		if($jie=='无'&&$is_eglish){
			$jie='No';
		}	
	}
	include 'result_chuli_sj.php';//包含处理不同报告数据的文
	$left	= '';
	if(is_array($jie)){//粒径等多结果项目的报告
		$vd0_arr= $jie;
		$jie	= '';//清空$jie的内容，准备重新赋值
		$all_value_num	+= count($vd0_arr);//总行数
		foreach ($vd0_arr as $key_name => $value_vd0) {
			$left	= "align=left";
			$xmname	= $key_name;//项目名称
			$unit	= $value_vd0['unit'];//计量单位
			$jie	= $value_vd0['vd0'];//结果
			$bgline.= temp($sj_mb);  //数据模版
			$i++;//行数
		}
	}else{//常规报告
		$bgline.= temp($sj_mb);  //数据模版
	}
	if($i==$all_value_num){
		switch ($mbrows['te_name']) {
			case '石英砂':
				$bgjw	.= "<p style=\"width:19cm;margin:0 auto;font-family:宋体;font-size:10pt;text-align:left;padding:10px;\">*注:1、滤料的粒径范围、有效粒径（d10）、均匀系数（K60）或不均匀系数（K80），由用户确定。<br />&nbsp;&nbsp;2、在用户确定的滤料和承托料粒径范围中，小于最小粒径、大于最大粒径的量均应小于5%（按质量计）。<br />&nbsp;&nbsp;3、石英砂（或以含硅物质为主的天然砂）滤料应为坚硬、耐用、密实的颗粒。在加工和过滤、冲洗过程中应能抗蚀。<br />&nbsp;&nbsp;4、石英砂滤料不应含可见的泥土、粉屑、云母或有机杂质。</p>";
				break;
			default:
				if($is_eglish){
					$bgjw.="<p style=\"font-family:Times New Roman;font-size:12pt;height:1.5cm\" align=\"center\">The following blank</p>";
				}else{
					$bgjw.="<p style=\"font-family:宋体;font-size:12pt;height:1.5cm\" align=\"center\">以下空白</p>";
				}
				break;
		}
	}
	if(($i==$all_value_num&&$i<$hang1) || $i==$hang1){
		$page++;
		$bgnrs.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';
		$bgnrs.= temp($one_page_mb);
		if($i<$all_value_num){
			$bgnrs.="</div><div style=\"height:3px;width:19cm\"></div>";
			$bgnrs.=$fenye;
		}
		$bgline = ""; 
	}
	if($i>$hang1&&($i==$all_value_num||($i==$hang1+$hang2*$page))){
		$page++;
		$bgnrs.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';	
		$bgnrs.= temp($two_page_mb);
		if($i<$all_value_num){
			$bgnrs.="</div><div style=\"height:3px;width:19cm\"></div>";
			$bgnrs.=$fenye;
		}
		$bgline = "";

	}
	if($i==$all_value_num){
		if($page>=2){
			$last_page_lines=$i-(($page-2)*$hang2+$hang1);//最后一页的行数
		}else{
			$last_page_lines=$i;
		}
		$end_html="</div>";
		if($last_page_lines>=25){
			$bgnrs.=$end_html.'<div style="height:3px;width:19cm"></div>'.$fenye;
		//	$bgnrs.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';
		//	$bgnrs.=$end_html;
		}else{
			$bgnrs.=$end_html;
		}
	}

}//循环模版结束foreach结束符

// 模板结构为4是说明页在最后
if($lx != 3){ //类型为3是为excel  没有数据只有封面
	if($mbrows['jiego'] == '5'){
		$bgnrs.='<div style="height:3px;width:19cm"></div>';
		$bgnrs.=$fenye;
		$bgnrs.=temp($sm_mb);
	}
}
  $bgnrs .="</div></body></html>"; 
   
//根据$lx判断显示、下载word、下载excel、下载pdf
if($lx == 1){
	echo $bgnrs;   
 }else if($lx == 2){
	header("Content-Type:   application/msword;");// charset=utf-8
	header("Content-Disposition:   attachment;   filename=$ypmc.doc");        
	header("Pragma:   no-cache");        
	header("Expires:   0");
	echo   $bgnrs;        
 }else if($lx == 3){
	header("Content-Type:   application/msexcel");        
	header("Content-Disposition:   attachment;   filename=$ypmc.xls");        
	header("Pragma:   no-cache");        
	header("Expires:   0");        
	echo   $bgnrs; 

 
 }else if($lx ==4 ){
	header("Content-Type:   application/mspdf");        
	header("Content-Disposition:   attachment;   filename=$ypmc.pdf");        
	header("Pragma:   no-cache");        
	header("Expires:   0");        
	echo   $bgnrs; 
 }
	  
  ?>
