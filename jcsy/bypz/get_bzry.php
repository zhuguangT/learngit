<?php
/**
 * 功能：获取标准溶液列表
 * 作者: Mr Zhou
 * 日期: 2014-10-15
 * 描述: 
*/
include ('../../temp/config.php');
$fzx_id = FZX_ID;
//获取与某化验项目关联的标注溶液
$vid = $_GET['vid'];
//标准溶液
if(1==$_GET['wz_type']){
	$sql = "SELECT `bzwz`.`wz_bh`,`bzwz`.`wz_name` sjmc,`bzwz`.`wz_name` wz_mc,`bzwz`.`wz_type`,`bzwz`.`amount`,`bzwz`.`unit`,`bzwz`.`time_limit`,bd.`consistence` wz_nd,bd.`id`,bd.`id` wz_id,'' AS sj_id,`vid`,'' AS pzrq FROM `bzwz_detail` AS bd LEFT JOIN `bzwz` ON `bzwz`.`id`=bd.`wz_id` WHERE `bzwz`.fzx_id='$fzx_id' AND bd.`vid`='$vid' AND `bzwz`.`time_limit` > curdate() ORDER BY `bzwz`.`wz_bh`, `bzwz`.`time_limit`";
	$query = $DB->query($sql);
	$bzry_lines = '';
	while($row=$DB->fetch_assoc($query)){
		$wz_mc = $row['wz_mc'];
		$disabled = ($row['amount']>0) ? '':'disabled=""';

		$bzry_vid_checkbox	= '<label><input class="ace" type="radio" name="wz_data" class="bzry_radio" '.$disabled.' value=\''.json_encode($row).'\' />&nbsp;<span class="lbl">'.$row['wz_mc'].'</span></label>';
		$bzry_lines	.= '<tr><td align="left">'.$bzry_vid_checkbox.'</td><td>'.$row['wz_bh'].'</td><td>'.$row['wz_nd'].'</td><td>'.$row['time_limit'].'</td><td>'.$row['amount'].'('.$row['unit'].')</td></tr>';
	}
	$theade = '
		<td width="30%">标准溶液名称</td>
		<td width="20%">标准溶液编号</td>
		<td width="15%">标液浓度</td>
		<td width="15%">有效期</td>
		<td width="20%">余量(单位)</td>';
}else if(2==$_GET['wz_type']){
	//自配溶液
	$sql = "SELECT *,`id` sj_id,'' AS wz_id,`wz_mc` sjmc,`sjmc` wz_mc,`sj_nd` wz_nd,`wz_nd` sj_nd FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `vid`='$vid' AND `sj_yxrq` > curdate()";
	$query = $DB->query($sql);
	$bzry_lines = '';
	while($row=$DB->fetch_assoc($query)){
		$row['amount']=$row['time_limit']=$amount='-';

		$bzry_vid_checkbox	= '<label><input class="ace" type="radio" name="sj_data" class="bzry_radio" value=\''.json_encode($row).'\' />&nbsp;<span class="lbl">'.$row['wz_mc'].'</span></label>';
		$bzry_lines	.= '<tr><td align="left">'.$bzry_vid_checkbox.'</td><td>'.$row['start_date'].'</td><td>'.$row['pz_user'].'</td><td>'.$row['wz_nd'].'</td><td>'.$row['sj_yxrq'].'</td></tr>';
	}
	$theade = '
		<td width="20%">自配溶液名称</td>
		<td width="30%">配制日期</td>
		<td width="15%">配制人</td>
		<td width="15%">自配液浓度</td>
		<td width="20%">有效日期</td>';
}else if(3==$_GET['bzye_type']){
	die;
}else if(4==$_GET['bzye_type']){
	die;
}
?>
<div class="widget-box no-border">
  <div class="widget-body">
	<table class="table table-striped table-bordered table-hover center" style='table-layout:fixed'>
	  <tr align="center">
	  <?php echo $theade; ?>
	  </tr>
	  <?php echo $bzry_lines; ?>
	</table>
  </div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#select_bzry_div label").click(function(){
			var data = $.parseJSON($(this).find("input").val());
	        form_select_by.data.value = $(this).find("input").val();
	        form_select_by.wz_id.value = data.wz_id;
	        form_select_by.sj_id.value = data.sj_id;
	    })
	});
</script>