<?php
/**
 * 功能：
 * 作者：Mr Zhou
 * 日期：2016-05-03
 * 描述：化验单默认配置
 */
return array(
	'hyd' =>array(
		'v'=> '2.0.3' //在hyd.js和hyd.css更新时增加版本号防止缓存
		,'danwei'=> (empty($dw_biaozhi)?'lnsw':$dw_biaozhi)
		,'wendu' => ''//化验单表头默认温度
		,'shidu' => ''//化验单表头默认湿度
		,'plan_file_path'=>'hyd/plan/lnsw/'//化验单模板文件路径
		,'sign_can_same' => '1'//签字是否允许相同
		,'sh_set'=> array(
			'02'=>array('jh','校核','v1'),
			'03'=>array('fh','复核','v2')
		)//审核设置
		,'sh_config'=> array(
			'jh'=>array('v1','校核','已完成','02'),
			'fh'=>array('v2','复核','已校核','03'),
			'sh'=>array('v3','审核','已复核','04')
		)
		,'hide_sign_date'=>false
		,'code_jiema'=> array(
			'is_jiema' => '1',  //站点是否解码  0不解码|1解码
			'sign' => 'sign_01' //哪一级解码 sign_01|sign_02|sign_03|sign_04
		)
		,'tuihui'=>array(
		'clear_sign_date' => false
		)
		,'qx'=>array(
			'type'=>'1'   //1|2   1代表y=bx+a，2代表x=by+a
		)
	),
	'zk'  => array(
		 'P'  => array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
		,'X'  => array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
		,'J'  => array('data'=>'_vd0','XYjcx'=>'_vd0','xy'=>'_round','ws'=>'')
		,'zk_set'=>array(
				array('nd'=>'0～0.0001', 'sn_jmd'=>'≤50',  'jbhs'=>'70～130'),
				array('nd'=>'0.0001～0.001','sn_jmd'=>'≤30', 'jbhs'=>'70～130'),
				array('nd'=>'0.001～0.01', 'sn_jmd'=>'≤20',  'jbhs'=>'70～130'),
				array('nd'=>'0.01～0.1', 'sn_jmd'=>'≤10',  'jbhs'=>'80～120'),
				array('nd'=>'0.1～1',    'sn_jmd'=>'≤5',   'jbhs'=>'90～110'),
				array('nd'=>'1～10',   'sn_jmd'=>'≤2.5', 'jbhs'=>'95～105'),
				array('nd'=>'10～100',   'sn_jmd'=>'≤1',   'jbhs'=>'95～105')
			)
		,'zhikong'=>array('zky_name'=>'自控样','sc_need_zky'=>false,'has_zk7'=>true)//针对zhikong.js做的配置
		)//质控修约配置 P 平均值 X相对偏差 J加标回收率 array('data'=>[vd0|_vd0],'XYjcx'=>[jcx|_vd0|0|1(检出限的一半)],'xy'=>[round|_round],'ws'=>[])
);