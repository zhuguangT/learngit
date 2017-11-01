<?php
/**
 * 功能：标准溶液配置标定
 * 作者：Mr Zhou
 * 日期：2015-10-03
 * 描述：标准溶液配置标定
 * */
class BiaodingApp extends LIMS_Base {
	public	$bd_id;
	public	$hyd_id;
	public	$bd_type;
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	/** 
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：
	*/
	public function index(){}
	/** 
	 * 功能：曲线选择列表
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：化验单曲线切换时选择列表
	*/
	public function sel_bd(){
		global $rooturl,$u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$vid	= intval($_GET['vid']);		//项目id
		$bd_id	= intval($_GET['bd_id']);	//标液id
		$hyd_id	= intval($_GET['hyd_id']);	//化验单id
		$hyd_vid= intval($_GET['hyd_vid']);	//化验单vid
		//查询出标液关联的化验项目
		if(!$vid){
			if(!$bd_id){
				$vid = $hyd_vid;	//没有关联标液的时候使用化验单的化验项目
			}else{
				$jzry_bd = $DB->fetch_one_assoc("SELECT `vid` FROM `jzry_bd` WHERE `id`='{$bd_id}' AND `fzx_id`='{$fzx_id}'");
				$vid = $jzry_bd['vid'];
			}
		}
		//查询出化验项目，提供项目选择列表
		$xm_list = '<option value="0">请选择项目</option>';
		$sql = "SELECT `vid`,`value_C` FROM `jzry_bd` AS bd LEFT JOIN `assay_value` AS av ON bd.`vid`=av.`id` WHERE bd.`fzx_id`='{$fzx_id}' GROUP BY CONVERT( `value_C` USING gbk )";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			$selected = ($vid==$row['vid']) ? 'selected="selected"' : '';
			$xm_list .= '<option '.$selected.' value="'.$row['vid'].'">'.$row['value_C'].'</option>';
		}
		$bd_lines = '';
		$date = date('Y-m-01', strtotime('-3 month'));
		$sql="SELECT `id`,`bzry_name`,`bzry_nongdu`,`bzry_pzrq`,`bzry_bdrq`,`fx_user` FROM `jzry_bd` WHERE `fzx_id`='{$fzx_id}' AND `vid`='{$vid}' AND `bzry_bdrq` >= '{$date}' ORDER BY `bzry_bdrq` DESC";
		$query=$DB->query($sql);
		while($row=$DB->fetch_assoc($query)){
			$checked= ($bd_id==$row['id']) ? 'checked="checked"':'';
			$bd_lines.= '<tr>
				<td><label style="width:100%;cursor:pointer;">'.$row['id'].'
					<input style="cursor:pointer;" type="radio" name="sc_bd" '.$checked.' value="'.$row['id'].'|'.$row['bzry_name'].'|'.$row['bzry_nongdu'].'"  />
				</label></td>
				<td>'.$row['bzry_name'].'</td>
				<td>'.$row['bzry_nongdu'].'</td>
				<td>'.$row['bzry_pzrq'].'</td>
				<td>'.$row['bzry_bdrq'].'</td>
				<td>'.$row['fx_user'].'</td>
			</tr>';
		}
		$warning = '';
		if($vid != $hyd_vid){
			$valueC = $_SESSION['assayvalueC'];
			$warning = '<div class="alert alert-danger">注意：您选择的化验项目【<strong>'.$valueC[$vid].'</strong>】与化验单的【<strong>'.$valueC[$hyd_vid].'</strong>】不一致！</div>';
		}
		echo '<!-- 选择标液 -->
		<div style="width:800px;margin:0 auto;overflow:auto;>
		  <form action="'.$rooturl.'/huayan/qx_data.php" method="get" name="form_select" class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>选择 <select name="vid" id="xm_select" style="font-size:16px">'.$xm_list.'</select> 公式参数</h3>
			</div>
			<div class="modal-body">
			  '.$warning.'
			  <table  class="table table-bordered center" style="width:100%" align=center>
				<tr><td>记录号</td><td>标准溶液名称</td><td>浓度</td><td>配置日期</td><td>标定日期</td><td>标定人</td></tr>
				'.$bd_lines.'
			  </table>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="hyd_id" value="'.$hyd_id.'" />
				<input type="hidden" name="app" value="'.$_GET['app'].'" />
				<a href="#" class="btn btn-primary btn-sm" id="sel_qx_ok">确定</a>
				<a href="#" class="btn btn-sm" data-dismiss="modal">取消</a>
			</div>
		  </form>
		</div><!-- 选择标液 end -->';
	}
	/** 
	 * 功能：将标准溶液关联到化验单
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：将标准溶液关联到化验单
	*/
	public function related_to_hyd(){
		global $rooturl;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$hyd_id	= intval($_GET['hyd_id']);	//化验单id
		$sc_bd = explode('|', $_GET['sc_bd']);
		if(intval($sc_bd[0])){
			$query = $DB->query("UPDATE `assay_pay` SET `bdid`='{$sc_bd[0]}',`scid`=0,`CA`='{$sc_bd[1]}',`CB`='{$sc_bd[2]}' WHERE `id`='{$hyd_id}' AND `fp_id`='{$fzx_id}'");
		}
		if($query){
			die(json_encode(array('error'=>'0','content'=>'')));
		}else{
			die(json_encode(array('error'=>'1','content'=>'关联失败！')));
		}
	}
}