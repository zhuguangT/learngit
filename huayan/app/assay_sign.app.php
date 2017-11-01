<?php
/**
 * 功能：表单退回
 * 作者：Mr Zhou
 * 日期：2016-03-14
 * 描述：
 */
class Assay_signApp extends LIMS_Base {
	//再退回任务单时默认清空签字日期
	private  $clear_sign_date = true;
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		global $global;
		//获取是否清空签字日期的配置信息
		if( isset($global['hyd']['tuihui']['clear_sign_date']) ){
			$this->clear_sign_date = $global['hyd']['tuihui']['clear_sign_date'];
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-14
	 * 参数：
	 * 返回值：
	 * 功能描述：
	*/
	public function index(){}
	/**
	 * 功能：获取退回信息
	 * 作者：Mr Zhou
	 * 日期：2016-03-14
	 * 参数：$arow[Array] 数据表之前的退回信息，在json字段中
	 * 返回值：
	 * 	$arow_json['退回']= array(
	 *		1 => array(
	 *			'tuiHuiUid'		=> $u['id'],
	 *			'tuiHuiUser'	=> $u['userid'],
	 *			'tuiHuiReason'	=> $_POST['yuanYin'],
	 *			'tuiHuiTime'	=> date('Y-m-d H:m:s')
	 *		)[,
	 *		2 => array()
	 *		]
	 *		……
	 *	);
	 * 功能描述：
	*/
	private function get_tuihui_data($arow){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//情况之前的图片签名信息
		$arow_json['userid_img'] = array();
		//判断之前是否有退回记录
		$arow_json = empty($arow['json']) ? array() : JSON_addslashes(json_decode($arow['json'],true));
		//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",trim($_POST['yuanYin']));
		$arow_json['退回'][]= array(
			'tuiHuiUid'		=> $u['id'],
			'tuiHuiUser'	=> $u['userid'],
			'tuiHuiReason'	=> JSON_addslashes($_POST['yuanYin'],true),
			'tuiHuiTime'	=> date('Y-m-d H:m:s')
		);
		return JSON($arow_json,true);
	}
	/**
	 * 功能：表单退回后再签字需要填写修改理由
	 * 作者：Mr Zhou
	 * 日期：2016-03-14
	 * 参数：$arow[Array] 数据表之前的退回信息，在json字段中
	 * 返回值：$arow_json['退回'][$end_json]['xiuGaiLiYou'] = $_POST['yuanYin'];
	 * 功能描述：
	*/
	private function get_sign_data($arow){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//判断之前是否有退回记录
		$arow_json = empty($arow['json']) ? array('退回'=>array()) : JSON_addslashes(json_decode($arow['json'],true));
		if( '' == $_POST['yuanYin'] ){
			return json_decode(JSON($arow_json['退回'],true),true);
		}
		//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",trim($_POST['yuanYin']));
		//已经有几次退回记录
		$end_json = count($arow_json['退回'])-1;
		//修改人uid
		$arow_json['退回'][$end_json]['xiuGaiUid']		= $u['id'];
		//修改人姓名
		$arow_json['退回'][$end_json]['xiuGaiUserid']	= $u['userid'];
		//修改理由
		$arow_json['退回'][$end_json]['xiuGaiLiYou']	= JSON_addslashes($_POST['yuanYin'],true);
		//只返回退回信息
		return json_decode(JSON($arow_json['退回'],true),true);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-14
	 * 参数：$_POST['assay_type']	[String]	原始记录类型
	 * 参数：$_POST['sign_type']	[String]	签字类型
	 * 返回值：
	 * 功能描述：原始记录表单签字
	*/
	public function assay_sign(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$admin	= $u['admin'];
		$fzx_id	= $this->fzx_id;
		global $dhy_arr,$global;
		//允许签字的数据表
		$allow_sign_tables = array(
			'py'	=> array('称量配药记录表','jzry'),
			'bd'	=> array('标准溶液标定记录表','jzry_bd'),
			'hyd'	=> array('化验单表原始记录表','assay_pay'),
			'qx'	=> array('标准曲线原始记录表','standard_curve')
		);
		//获取请求签字的数据表单类型
		$assay_type = trim($_POST['assay_type']);
		//数据表必须在$can_tables允许范围内
		if( in_array($assay_type, array_keys($allow_sign_tables) ) ){
			$table_name = $allow_sign_tables[$assay_type][1];
		}else{
			die(json_encode(array('error'=>'1','content'=>"【{$assay_type}】是不支持的签字表单类型，请检查重试！")));
		}
		//数据ID必须有效
		$id = intval($_POST['id']);
		( !$id ) && die(json_encode(array('error'=>'1','content'=>"{$allow_sign_tables[0]}数据ID请求错误，请检查重试！")));
		$status = 'status';
		if( 'hyd' == $assay_type ){
			$status = 'over';
			//获取表单信息
			$arow=$DB->fetch_one_assoc("SELECT * FROM `{$table_name}` WHERE `id`={$id} AND `fp_id`='$fzx_id'");
		}else{
			//获取表单信息
			$arow=$DB->fetch_one_assoc("SELECT * FROM `{$table_name}` WHERE `id`={$id} AND `fzx_id`='$fzx_id'");
		}
		if( !intval($arow['id']) ){
			die(json_encode(array('error'=>'1','content'=>"无法查到{$allow_sign_tables[0]}ID为{$id}的数据，请检查重试！")));
		}
		//SQL语句
		$sql_sign = '';
		//取出json字段中数据并转换为数组格式
		$json_data = json_decode($arow['json'],true);
		//签名图谱
		$json_data['userid_img'] = ( !is_array($json_data['userid_img']) ) ? array() : $json_data['userid_img'];
		//定义默认提示信息
		$msg_error = 0;
		$msg_content = '';
		//定义于需要提示的人名（带样式）
		$userid  = '<span class="green">'.$arow['userid'].'</span>';
		$sign_01 = '<span class="green">'.$arow['sign_01'].'</span>';
		$sign_02 = '<span class="green">'.$arow['sign_02'].'</span>';
		$sign_03 = '<span class="green">'.$arow['sign_03'].'</span>';
		$sign_04 = '<span class="green">'.$arow['sign_04'].'</span>';
		$userid2 = '、<span class="green">'.$arow['userid2'].'</span>';
		$sign_012 = '<span class="green">'.$arow['sign_012'].'</span>';
		$sign_type = trim($_POST['sign_type']);
		switch ($sign_type) {
			case 'fx_qz':
				//第一签字人
				if( !empty($arow['sign_01']) ){
					$msg_error = 1;
					$msg_content = '该化验单已签字，签字人为（'.$sign_01.'）,你可以刷新页面来查看签字信息。';
				}else if( $admin || in_array($u['userid'], array($arow['userid'], $arow['userid2']) ) ){
					//如果化验单未签字并且当前用户为第一或第二化验员
					$json_data['userid_img']['sign_01'] = $u['userid_img'];//签名图片
					$date_time = empty($arow['sign_date_01']) ? date('Y-m-d H:i:s') : $arow['sign_date_01'];//签字时间
					//如果接收到签字原因，则说明这是退回后的单子再签字
					if( !empty($_POST['yuanYin']) ){
						//获取退回签字信息
						$json_data['退回'] = $this->get_sign_data($arow);
					}
					$sql_sign = "UPDATE `{$table_name}` SET `sign_01`='{$u['userid']}',`sign_date_01`='{$date_time}',`{$status}`='已完成'";
				}else{
					$msg_error = 1;
					$msg_content = '对不起，你不是该化验单分析人员（'.$userid.$userid2.'），无法进行签字！';
				}
				break;
			case 'fx2_qz':
				//第二签字人
				if( !empty($arow['sign_012']) ){
					$msg_content = '该化验单已校核签字，签字人为（'.$sign_01.$sign_012.'）,你可以刷新页面来查看签字信息。';
				}else if( !empty($arow['sign_01']) && empty($arow['sign_012']) && $u['id'] == $arow['uid2'] ){
					//第一化验员已签字，并且当前用户为第二化验员
					$json_data['userid_img']['sign_012'] = $u['userid_img'];//签名图片
					$date_time = empty($arow['sign_date_012']) ? date('Y-m-d H:i:s') : $arow['sign_date_012'];//签字时间
					$sql_sign = "UPDATE `{$table_name}` SET `sign_012`='{$u['userid']}',`sign_date_012`='{$date_time}',`{$status}`='已完成'";
				}else if( $u['id'] == $arow['uid'] && !empty($arow['sign_01']) ){
					#因为谁先签字谁就会签在第一化验员的位置上，所以当第二化验员已经签字而第一化验员再签字时
					#需要更改第一第二签字人顺序为：第一化验员为第一签字人，第二化验员为第二签字人
					$json_data['userid_img']['sign_012']= $json_data['userid_img']['sign_01'];
					$json_data['userid_img']['sign_01']	= $u['userid_img'];
					$sign_date_01 = empty($arow['sign_date_01']) ? date('Y-m-d H:i:s') : $arow['sign_date_01'];
					$sign_date_012 = empty($arow['sign_date_012']) ? $arow['sign_date_01'] : $arow['sign_date_012'];
					$sql_sign = "UPDATE `{$table_name}` SET `sign_01`='{$u['userid']}', `sign_012`='{$arow['sign_01']}', `sign_date_01`='{$sign_date_01}', `sign_date_012`='{$sign_date_012}'";
				}else{
					$msg_error = 1;
					$msg_content = '对不起，你不是该化验单分析人员（'.$userid.$userid2.'），无法进行签字！';
				}
				break;
			case 'jh_qz':
				//校核
				if( !$u['jh'] ){
					$msg_error = 1;
					$msg_content = '你没有校核权限！';
				}/*else if( !$admin && in_array($u['userid'], array($arow['sign_01'], $arow['sign_012'] ) ) ){
					$msg_error = 1;
					$msg_content = '你不能校核自己的原始记录！';
				}*/else if( !empty($arow['sign_02']) ){
					$msg_error = 1;
					$msg_content = '该原始记录已被（'.$sign_02.'）校核，你可以刷新页面来查看签字信息。';
				}else{
					$json_data['userid_img']['sign_02'] = $u['userid_img'];//签名图片
					$date_time = empty($arow['sign_date_02']) ? date('Y-m-d H:i:s') : $arow['sign_date_02'];
					$sql_sign = "UPDATE `{$table_name}` SET `sign_02`='{$u['userid']}',`sign_date_02`='{$date_time}',`{$status}`='已校核'";
				}
				break;
			case 'fh_qz':
				//复核
				if( !$u['fh'] ){
					$msg_error = 1;
					$msg_content = '你没有复核权限！';
				}/*else if( !$admin && $u['userid'] == $arow['sign_02'] ){
					$msg_error = 1;
					$msg_content = '你不能复核自己校核的原始记录！';
				}*/else if( !empty($arow['sign_03']) ){
					$msg_error = 1;
					$msg_content = '该原始记录已被（'.$sign_03.'）复核，你可以刷新页面来查看签字信息。';
				}else{
					$json_data['userid_img']['sign_03'] = $u['userid_img'];
					$date_time = empty($arow['sign_date_03']) ? date('Y-m-d H:i:s') : $arow['sign_date_03'];
					$sql_sign = "UPDATE `{$table_name}` SET `sign_03`='{$u['userid']}',`sign_date_03`='{$date_time}',`{$status}`='已复核'";
				}
				break;
			case 'sh_qz':
				//审核
				if( !$u['sh'] && !$u['admin'] ){
					$msg_error = 1;
					$msg_content = '你没有审核权限！';
				}/*else if( !$admin && $u['userid'] == $arow['sign_03'] ){
					$msg_error = 1;
					$msg_content = '你不能审核自己复核的原始记录！';
				}*/else if( !empty($arow['sign_04']) ){
					$msg_error = 1;
					$msg_content = '该原始记录已被（'.$sign_04.'）审核，你可以刷新页面来查看签字信息。';
				}else{
					$json_data['userid_img']['sign_04'] = $u['userid_img'];
					$date_time = empty($arow['sign_date_04']) ? date('Y-m-d H:i:s') : $arow['sign_date_04'];
					$sql_sign = "UPDATE `{$table_name}` SET `sign_04`='{$u['userid']}',`sign_date_04`='{$date_time}',`{$status}`='已审核'";
				}
				break;
			default:
				$msg_error = '1';
				$msg_content = '对不起，数据请求错误！';
				$msg_status = array('error'=>'1','content'=>'');
				break;
		}
		if( !empty($msg_content) ){
			die(json_encode(array('error'=>$msg_error,'content'=>$msg_content)));
		}
		//不是多合一项目或者不是化验单签字时只有一个ID号
		if( !intval($dhy_arr[$arow['vid']]) || 'assay_pay' != $table_name ){
			$id_str = $arow['id'];
		}else{
			//获取多合一化验单列表
			$id_arr = array();
			$vid_str = implode(',',$dhy_arr['xm'][$dhy_arr[$arow['vid']]]) ;
			$query = $DB->query("SELECT `id` FROM `assay_pay` WHERE `cyd_id` = {$arow['cyd_id']} AND `vid` IN ({$vid_str})");
			while ( $row = $DB->fetch_assoc($query) ) {
				$id_arr[] = $row['id'];
			}
			$id_str = implode(',', $id_arr);
		}
		$json_data = JSON($json_data);
		// echo $sql_sign . ",`json`='{$json_data}' WHERE `id` IN({$id_str})";
		$query_hyd = $DB->query($sql_sign . ",`json`='{$json_data}' WHERE `id` IN({$id_str})");
		if( $query_hyd ){
			//如果是化验单分析签字则需要更新下面的信息
			if( 'hyd' == $assay_type && 'fx_qz' == $sign_type ){
				//更新order表数据状态
				$query_order = $DB->query(	"UPDATE `assay_order` SET `assay_over`='1' WHERE `tid` IN( {$id_str} ) AND `vd0` != ''");
				//更新化验单数据和化验单完成数目,查询改cyd_id下的化验单数据，查询已完成的化验数目
				$hyd_count_sql = "SELECT COUNT(`id`) AS `count` FROM `assay_pay` WHERE `cyd_id`='{$arow['cyd_id']}'";
				$hyd_count = $DB->fetch_one_assoc( $hyd_count_sql );
				$wch_count = $DB->fetch_one_assoc( $hyd_count_sql." AND `{$status}` NOT IN ('未开始','已开始')" );
				$up_count_sql = "UPDATE `cy` SET `hyd_count`='{$hyd_count['count']}' , `hyd_wc_count`='{$wch_count['count']}' WHERE `id`='{$arow['cyd_id']}'";
				$DB->query( $up_count_sql );
				//更新批次任务状态为7（已完成化验）,状态信息在temp/definition.php中定义
				$sql_up_cy_status = "UPDATE `cy` SET `status` = '7', `sj_wc_date` = CURDATE() WHERE `id`='{$arow['cyd_id']}' AND `status`='6' AND `hyd_count`=`hyd_wc_count` LIMIT 1";
				$DB->query( $sql_up_cy_status );
			}
			die(json_encode(array('error'=>'0','content'=>'')));
		}else{
			die(json_encode(array('error'=>'1','content'=>'签字失败，请刷新页面重试！')));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-14
	 * 功能描述：原始记录退回
	*/
	public function assay_return_back(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $dhy_arr,$global;
		//允许签字的数据表
		$can_tables = array(
			'cy'	=> 'cy',//采样单记录
			'py'	=> 'jzry',	//称量配药记录
			'bd'	=> 'jzry_bd',//标准溶液标定记录
			'hyd'	=> 'assay_pay',//化验单原始记录
			'qx'	=> 'standard_curve'//标准曲线原始记录
		);
		//数据表必须在$can_tables允许范围内
		$assay_type = trim($_POST['assay_type']);
		//化验单表的状态字段和其他表的不一样
		$status = ( 'hyd' == $assay_type ) ? 'over' : 'status';
		if( in_array($assay_type, array_keys($can_tables) ) ){
			$table_name = $can_tables[$assay_type];
		}else{
			die(json_encode(array('error'=>'1','content'=>'数据表请求有误！')));
		}
		//数据ID必须有效
		$id = intval($_POST['id']);
		( !$id ) && die(json_encode(array('error'=>'1','content'=>'数据ID请求错误！')));
		$status = 'status';
		if( 'hyd' == $assay_type ){
			$status = 'over';
			//获取表单信息
			$arow=$DB->fetch_one_assoc("SELECT * FROM `{$table_name}` WHERE `id`={$id} AND `fp_id`='$fzx_id'");
		}else{
			//获取表单信息
			$arow=$DB->fetch_one_assoc("SELECT * FROM `{$table_name}` WHERE `id`={$id} AND `fzx_id`='$fzx_id'");
		}
		if( !intval($arow['id']) ){
			die(json_encode(array('error'=>'1','content'=>'无法查到指定数据，请重试！')));
		}
		//获取退回信息
		$jsonStr = $this->get_tuihui_data($arow);
		//清空签字名称和签字日期的SQL组合语句
		$clear_sign_name_sql = $clear_sign_date_sql = '';
		//清空签字名称
		$clear_sign_name_sql = "`sign_01`='',`sign_012`='',`sign_02`='',`sign_03`='',`sign_04`='',";
		if( true == $this->clear_sign_date ){
			//清空签字日期
			$clear_sign_date_sql = "`sign_date_01`=NULL,`sign_date_012`=NULL,`sign_date_02`=NULL,`sign_date_03`=NULL,`sign_date_04`=NULL,";
		}
		//将化验单退回到已开始状态
		$hyd_query = $DB->query("UPDATE `{$table_name}` SET {$clear_sign_name_sql} {$clear_sign_date_sql} `{$status}`='已开始',`json`='{$jsonStr}' WHERE `id`='{$id}'");
		if( 'hyd' == $assay_type && 1 == intval($DB->affected_rows()) ){
			//将化验已完成状态(7)改成已生成化验(6)，并将已完成的化验单数减一
			$cy_query = $DB->query("UPDATE `cy` SET `status`=6,`hyd_wc_count`=`hyd_wc_count`-1 WHERE `id`='{$arow['cyd_id']}'");
			//检测报告打印状态修改
			$order_query = $DB->query("SELECT DISTINCT `cid` FROM `assay_order` WHERE `tid` = '{$id}'");
			while ($row = $DB->fetch_assoc($order_query)) {
				$order_cid[] = $row['cid'];
			}
			$cids = empty($order_cid) ? '0' : implode(',', $order_cid);
			$re_sql = "UPDATE `report` SET `print_status`='-1' WHERE `cyd_id`='{$arow['cyd_id']}' AND `print_status`='1' AND `cy_rec_id` IN ({$cids}) ";
			$re_query = $DB->query($re_sql);
		}
		if( $hyd_query ){
			echo json_encode(array('error'=>'0','content'=>''));
		}else{
			echo json_encode(array('error'=>'1','content'=>'原始记录退回失败，请重试！'));
		}
	}
}