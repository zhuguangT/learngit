<?php
/**
 * 功能：原始记录表
 * 作者：Mr Zhou
 * 日期：2015-10-29
 * 描述：
 */
class PrintApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		include_once './assay_form_func.php';
		//模板文件路径
		$this->file_path = $this->_global['hyd']['plan_file_path'];
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：
	*/
	public function index(){
		$u = $this->_u;
		$DB=$this->_db;
		$fzx_id = $this->fzx_id;
		global $global,$trade_global,$rooturl,$current_url,$zong_biao,$heng_biao,$arow;
		if(!isset($_GET['tid']) || empty($_GET['tid'])){
			header('location:'.$rooturl.'/huayan/ahlims.php?ajax=1&app=public&act=reto&content=化验单ID参数不合法！&class=danger');
			die;
		}
		$print_data = $hyd_pdf = array();
		$tids = explode(',' ,trim($_GET['tid']));
		foreach ($tids as $key => $tid) {
			$arow = get_hyd_data( $tid );
			// 若复核人员已签字, 则将已打印字段置 1
			if ( $arow['sign_03'] ) {
				$DB->query( "UPDATE `assay_pay` SET `printed`='1' WHERE `id`={$arow['id']} AND ( `fp_id` = '{$fzx_id}' OR `fzx_id` = '{$fzx_id}' ) " );
			}
			// 表格纵横板式
			$zongheng	= $arow['zongheng'].'_biao';
			// 表格纵横板式的宽度
			$zongheng	= $$zongheng;
			// 使用模板的名称
			$table_name = $arow['table_name'];
			// 获取行数据
			$lines_data	= get_assay_hyd_line($tid,$table_name,1);
			// 站点解码
			$zhanming	= ($global['hyd']['code_jiema']['is_jiema']&&$arow[$global['hyd']['code_jiema']['sign']]) ? '站 名' : '样品编号';
            $pdf_sql = "SELECT `hydpdf`.*,`pdf`.`pdf_detail`,`pdf_type` 
                  FROM `hydpdf`LEFT JOIN `pdf` ON `hydpdf`.`pid` =`pdf`.`id` 
                  WHERE `tid` = '{$tid}' 
                  ORDER BY  `pdf_type` ASC ,
                            RIGHT(LEFT(REPLACE(REPLACE(`pdf_detail`,'{\"bar_code\":\"',''),'<br/>',''),13),4)";
			$query = $DB->query($pdf_sql);
			while ($row = $DB->fetch_assoc($query)) {
				if( isset($hyd_pdf[$row['pid']]) ){
                    $hyd_pdf[$row['pid']][] = $row['tid'];
				}else{
                    $hyd_pdf[$row['pid']] = array($row['tid']);
                }
			}
			// 化验单模板文件地址
			$plan_file_path = $global['hyd']['plan_file_path'];
			// 这里添加  环境条件 的表格头部
			eval('$hjtj_bt="'.gettemplate($this->file_path.'hjtj_bt').'";');
			// 获取化验单签字表单
			$assay_sign_form = get_assay_form_sign($arow);
			// 其他数据
			@eval('$plan="'.gettemplate($this->file_path.'plan_'.$table_name).'";');
			@eval('$line_tpl="'.gettemplate($this->file_path.'line_'.$table_name).'";');
			@eval('$plan_tpl="'.gettemplate('hyd/assay_form_hyd').'";');
			// 提取extraxjs的内容
			preg_match("/<script.*extrajs.*>(.*)<\/script>/isU", $plan_tpl, $extrajs);
			$print_data[$arow['id']] = array(
				'print_html'=> '',
				'tid'		=> $arow['id'],
				'data'		=> json_encode($arow),
				'extrajs'	=> $extrajs[1],
				'line_tpl'	=> $line_tpl,
				'lines_data'=> json_encode($lines_data),
				'plan_tpl'	=> preg_replace('/<script.*>(.*)<\/script>/isU','',$plan_tpl)
			);
		}
		//定义pdf所属tid的数组 $pdfs
        //定义共用pdf的化验单数组 $tids
		$pdfs = $tids = array();
        //通过循环$hyd_pdf将pdf放在所属化验单中单号最大的那张化验单下面,并且记录所有放在该化验单(单号最大的化验单)下的pdf关联的化验单ID
		foreach ($hyd_pdf as $pid => $row){
		    $pdfs[] = array(
		        'pid' => $pid,
		        'tid' => max($row)
            );
            if( !isset($tids[$pdfs[$pid]])){
                $tids[$pdfs[$pid]] = $row;
            }else{
                $tids[$pdfs[$pid]] = array_unique(array_merge($tids[$pdfs[$pid]],$row));
            }
            //将单号倒序排,这样pdf就会附在最后一张化验单后面(化验单号最大的那张化验单)
            sort($tids[$pdfs[$pid]]);
        }
        //将共用pdf的化验单放在一起
        foreach ($tids as $max_tid => $row){
            foreach ($row as $key => $tid){
                $a = $print_data[$tid];
                unset($print_data[$tid]);
                $print_data[$tid] = $a;
            }
        }
		$hyd_pdf = json_encode($pdfs);
		echo $this->temp('hyd/print_all',get_defined_vars());
	}
	public function print_hyd(){
		global $rooturl;
		$this->index();
	}
	public function set_with(){
		$u = $this->_u;
		$DB=$this->_db;
		$fzx_id = $this->fzx_id;
		$A4_type = array(
			'A4_Vertical' => 'zong',
			'A4_Horizontal' => 'heng'
		);
		if(!array_key_exists($_GET['A4_type'],$A4_type)){
			die('A4纸类型参数不合法！');
		}
		$zongheng = $A4_type[$_GET['A4_type']];
		$tid = explode(',',str_replace("'", '', trim($_GET['tid'])));
		if(empty($tid)){
			die('化验单ID参数不合法！');
		}
		$tid = implode("','", $tid);
		$user_where = $u['admin'] ? '1' : "( `uid`='{$u['id']}' OR `uid2`='{$u['id']}' )";
		$fids = array();
		$sql = "SELECT DISTINCT `fid` FROM `assay_pay` WHERE `id` IN ('{$tid}') AND `fzx_id`='{$fzx_id}' AND {$user_where}";
		$query = $DB->query($sql);
		while ($row=$DB->fetch_assoc($query)) {
			$fids[] = $row['fid'];
		}
		$fids = implode("','", $fids);
		$sql = "UPDATE `bt` SET `zongheng`='{$zongheng}' WHERE `fid` IN ('{$fids}')";
		$query = $DB->query($sql);
		echo $DB->affected_rows() ? '纵横版修改成功！' : '纵横版修改失败！';
	}
}