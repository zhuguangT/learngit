<?php
/**
* 功能：全局变量定义文件
* 作者：Mr Zhou
* 日期：2014-04-16
* 描述：将系统中使用的全局变量定义在$global数组中
*/
//是否开始验收模块
$yanshou_peizhi = "有验收";//为了防止后续变量覆盖后出错，判断值给定为中文：有验收，没有验收；
$yp_status_xinxi = array("清澈透明","浑浊","沉淀","气泡","其他","包装完好","包装破损");
$yp_status_xinxi1 = array("清澈透明","浑浊","沉淀","气泡","其他");//采样记录表里的
//页面初始化 在变量$trade_global中声明导航和需要引入的css和js文件等其他信息
$fzx_id=$u['fzx_id'];
$gx_jingdu='120.387522';
$gx_weidu='36.106301';
$global	= $trade_global		= array();
$trade_global['daohang']	= array(
	array(	'icon'	=> 'icon-home home-icon',
			'html'	=> '首页',
			'href'	=> 'main.php')
);
$trade_global['u']	= $u;
$trade_global['rooturl'] = $rooturl;
global $global,$trade_global;
//将系统中使用的全局变量定义在$global数组中
$global = array(
	'version'	=> '2.0',//系统版本号,
	'firm_type'	=> 'zls',//企业类型（自来水"zls"，水文"sw"）
	'pdf_file_way'=>'/home/files/',
	'load_way'	=> array(1=>'pdf',2=>'excel',3=>'txt'),//载入方式
	'rq_size'	=> array('50mL','100mL','200mL','250mL','300mL','400mL','500mL','1L','2L','2.5L','5L'),//容器规格
	'unit'		=> array('mg/L','℃','CFU/mL','CFU/L','CFU/100mL','MPN/mL','MPN/L','MPN/100mL','万个/L','个/L','μg/L','个/10L','NTU','级','μS/cm','Bq/L','%','g/cm³','mg/m³','mg/g','度','cm','m','m³','m³/秒','无量纲'),//项目单位
	'site_type'	=> array(1=>'常规任务',2=>'内部任务',3=>'委托任务'),//站点类别//总中心的 站点类别在下面
	'cy_flag_site_type'=>array('3'),//采样单位需要默认为委托单位的 任务类型
	//'tjcs'		=> array(0=>'省界',1=>'重点',2=>'水源地',3=>'排污口',4=>'地下水'),//统计参数
	'pzzd'		=> array(0=>'',1=>'',2=>'',3=>'',4=>''),//配置站点显示参数
	'bar_code'	=> array(
				'site_type'	=> array(0=>'J',1=>'C',2=>'L',3=>'W'),//任务类型对应的样品编号标识
				'water_type'	=> array(1=>'B',3=>'X',5=>'S',7=>'F',55=>'W',70=>'T',73=>'K'),//水样类型对应的样品编号标识
				'water_type_barcode'	=> '0'//不同水样类型（大类） 是否分开编号1是0否
				),
	'hyd'		=>array(
				 'v'=> '2.1.4' //在hyd.js和hyd.css更新时增加版本号防止缓存
				,'danwei'=> 'lzzls'
				,'wendu' => '20'//化验单表头默认温度
				,'shidu' => '50'//化验单表头默认湿度
				,'plan_file_path'=>'hyd/plan/lzzls/'//化验单模板文件路径
				,'sign_can_same' => true//签字是否允许相同
				,'hide_sign_date'=>true
				,'sh_set'=> array(
						'02'=>array('jh','校核','v1'),
						'03'=>array('fh','复核','v2')
					)//审核设置
				,'sh_config'=> array(
						'jh'=>array('v1','校核','已完成','02'),
						'fh'=>array('v2','复核','已校核','03'),
						'sh'=>array('v3','审核','已复核','04')
					)
				,'code_jiema'=> array(
						'is_jiema' => '0',	//站点是否解码  0不解码|1解码
						'sign' => 'sign_02'	//哪一级解码	sign_01|sign_02|sign_03|sign_04
					)
				,'qx'=>array(
						'type'=>'1'		//1|2   1代表y=bx+a，2代表x=by+a
					)
				,'jcxm_set_mr_lx' => '5'
				),
	'zk'		=> array(
				 'P'	=> array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
				,'X'	=> array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
				,'J'	=> array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
				,'zk_set'=>array(
								array('nd'=>'0～0.0001',		'sn_jmd'=>'≤50',	'jbhs'=>'70～130'),
								array('nd'=>'0.0001～0.01',	'sn_jmd'=>'≤30',	'jbhs'=>'80～120'),
								array('nd'=>'0.01～0.1',		'sn_jmd'=>'≤20',	'jbhs'=>'90～110'),
								array('nd'=>'0.1～1',		'sn_jmd'=>'≤10',	'jbhs'=>'90～110'),
								array('nd'=>'1～10',			'sn_jmd'=>'≤5',		'jbhs'=>'90～110'),
								array('nd'=>'10～100',		'sn_jmd'=>'≤2.5',	'jbhs'=>'95～105'),
								array('nd'=>'≥100',			'sn_jmd'=>'≤1',		'jbhs'=>'95～105')
					)
				,'jb_js'=>array('jsgs'=>'1',	'tjxs'=>'2',	'jcx_jg'=>'1','sj_jg'=>'1','blws'=>'1')//针对zhikong.js做的配置
				,'zhikong'=>array('zky_name'=>'自控样','sc_need_zky'=>false,'has_zk7'=>true,'02C08C'=>true)//针对zhikong.js做的配置
				),//质控修约配置 P 平均值 X相对偏差 J加标回收率 array('data'=>[vd0|_vd0],'XYjcx'=>[jcx|_vd0|0|1(检出限的一半)],'xy'=>[round|_round],'ws'=>[])
	'zk_js'     => array(
				'jbhs' => array(
					'jsgs'=>array(1=>'P =[(c2-c1)×V1+C2×V0]/(c0 ×V0)×100% ',2=>'P =[m1-m2]/(m0)×100%'),
					'tjxs'=>array(1=>'需要',2=>'不需要')
					,'jcx_jg'=>array(1=>'原始结果',2=>'检出限一半',3=>'0')
					,'sj_jg'=>array(1=>'原始结果',2=>'修约后的结果')
					,'blws'=>array(1=>'3位数字',2=>'小数后1位'))
				),
	'status'	=> array(
				'0'	=> '采样任务未确认',
                '1'	=> '采样任务已下达',
                '2'	=> '采样任务已接受',
                '3'	=> '已采样',
                '4'	=> '样品已审核',
                '5'	=> '样品已接收',
                '6'	=> '检测任务已下达',
                '7'	=> '已完成化验',
                '8'	=> '报告已签发',
				),
	'duijie'	=> '1',//是否需要与其他系统对接 1为需要，0为不需要
	'site_line'	=> array('1'=>'左','2'=>'中','3'=>'右'),
	'site_vertical'	=> array('1'=>'上','2'=>'中','3'=>'下'),
	'cy_record_bt'=>array(
					'moren'=>array(
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						),
					'1bak'=>array(
						'采样<br/>方式'=>'cy_way',
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						'水位<br/>(m)'=>'water_height',
						'流量/蓄水量<br/>(m³/s/亿m³)'=>'liu_l',
						'流速<br/>(m/s)'=>'liu_s',
						'感官指标'=>'gg_zb'
						),
					'3bak'=>array(
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						
						),
					'5bak'=>array(
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						),
					'7bak'=>array(
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						)
					),
	'cy_record_bt_order'=>array(
						'埋深<br/>(m)'=>'cy_ms',
						'采样<br/>方式'=>'cy_way',
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						'水位<br/>(m)'=>'water_height',
						'流量/蓄水量<br/>(m³/s/亿m³)'=>'liu_l',
						'流速<br/>(m/s)'=>'liu_s',
						'感官指标'=>'gg_zb'
					),
	'cy_cx'            =>array('左','中','右'),
	'cy_way'           =>array('桥采','船采','涉水','缆道','冰上'),
	'tian_qi'		   =>array('晴','多云','雨','雪'),
					//不同水样类型对应采样记录表中的不同表头设置(1:地表水,3:地下水,5:生活饮用水,7:废污水)
	'not_need_zk'      =>array('38','39','49','50','56','58','73'),
	'snpx_flag'        =>array('20','21','23','60','61','63','25','65'),
	'jbhs_flag'        =>array('40','41','43','60','61','63','45','65'),
	'xcpx_flag'        =>array('5','25','45','65'),
	'qckb_flag'		   =>array('1','21','41','61'),
	'cgb_bt_cs'        =>array('水功能区号'=>'water_area_nums','水功能区名称'=>'water_area_name','站点名称'=>'site_name','采样日期'=>'cy_date','采样时间'=>'cy_time','水位'=>'water_height','流量/蓄水量'=>'liu_l','气温'=>'qi_wen'),
	//定义的项目如果检测值是0则显示为未检出//在这里将69 70这两个项目删掉如果需要可再添加上
	'modi_data_vids'=>array(1,2,3,6,569),
	'bg_pingjun'=>'1',
	'related_value'=>'|&121&187&186&198&|&173&174&103&|&120&119&118&114&|&100&117&|&100&103&|',//相关项目,在报告上关联显示//配置方式 “|&关联项目1&关联项目2&关联项目3&|另外一组关联项目|”
);
if($u['is_zz']){
	//0永远是监督任务，没有监督任务请空着
	$global['site_type']=array(1=>'常规任务',2=>'内部任务',3=>'委托任务');
}
