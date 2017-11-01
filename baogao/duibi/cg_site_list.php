<?php
/**
 * 功能：
 * 作者：Mr Zhou
 * 日期：2014-11-10
 * 描述：
*/
include '../../temp/config.php';
include INC_DIR . "cy_func.php";
$divline="";
$fzx_id=$u['fzx_id'];
//查询所有模板
$bgmb_list = array();
$cyd_id=$_GET['cyd_id'];	
$C = $DB->query("SELECT  c.*,s.`water_type` FROM cy_rec c LEFT JOIN `sites` s ON c.sid = s.id WHERE c.cyd_id='".$cyd_id."' AND sid>'0' AND zk_flag>'-1'");
while($c=$DB->fetch_assoc($C)){
	if(empty($c['water_type'])){
		$water_type_bh=substr($c['bar_code'],1,1);
		$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
		$water_type_max=$c['water_type']=get_water_type_max($water_type,$fzx_id);
	}else{
		$water_type_max=get_water_type_max($c['water_type'],$fzx_id);
	}

// 循环所有模板  设置站点的模板	 
	$url    =   'cid='.$c['id'].'&cyd_id='.$cyd_id.'&sid='.$c['sid'];
	$mblxid=@$DB->fetch_one_assoc("SELECT  te_id FROM `report2` WHERE  cy_rec_id={$c['id']} ");
	
	$sql ="SELECT  * FROM `report_template` WHERE state = '1' ";

	$rows = $DB->query($sql);
	$bgmb_list  = '';  
	while($row=$DB->fetch_assoc($rows)){
		if( $row['id'] ==  $mblxid['te_id'] ||(empty($mblxid) && $row['water_type'] == $water_type_max) ){
			$bgmb_list  .= ' <option value ='.$row['id'].'  selected="selected">'.$row['te_name'].'</option>';  
		 }else{
			$bgmb_list  .= ' <option value ='.$row['id'].'>'.$row['te_name'].'</option>';  
		 }
	}

	//计算出每个站点的项目数
	$items_arr=explode(',',$c['assay_values']);
	foreach($items_arr as $key=>$value)
	{
		if(empty($value))
		{
			unset($items_arr[$key]);
		}
	}
			$z_nums=count($items_arr);//一个站点中所有的项目
	$sql_order="SELECT  vd0,hy_flag,ping_jun  FROM `assay_order` where cid='".$c['id']."' and cyd_id='".$cyd_id."' and (hy_flag>=0 || hy_flag=-3)";
	$query_order=mysql_query($sql_order);
	$y_nums=0;
	while($rs_order=mysql_fetch_array($query_order))
	{
//  差数2 为空报错              if(in_array($rs_order['hy_flag'],$have_Snpx))
		{
			if(!empty($rs_order['ping_jun'])||$rs_order['ping_jun']=="0")
			{
				$y_nums++;
			}
		}
//跟上为同一判断	else
		{
			if(!empty($rs_order['vd0'])||$rs_order['vd0']=="0")
				$y_nums++;
		}
	}
	if($c[water_type] !=''){
		$bzlx=@$DB->fetch_one_assoc("SELECT  `lname` FROM `leixing` WHERE `id`={$c['water_type'] }");
		$szlx= $bzlx['lname'];
	}
	$divline.=  '<tr align=center>
					<td>'.$c['id'].'</td>
					<td width="120">
						'.$c['bar_code'].'
					</td>
					<td>'.$szlx.'</td>
					<td style="text-align:left;">'.$c['site_name'].'</td>
					<td>'.$y_nums.'/'.$z_nums.'</td>
					<td id="state'.$c['id'].'" align="center">
						<a href="'.$rooturl.'/baogao/duibi/cg_duibi.php?'.$url.'" target="_blank">查看报告</a>
					</td>
				</tr>';
}
	$divline.= "<tr> <td colspan='4'>汇总报告</td><td colspan = '3'><a align=center  href='".$rooturl."/baogao/duibi/cg_duibi.php?
				cyd_id=$cyd_id' target='_blank'>查看汇总报告</a></td></tr>";
	disp("dbsy/cg_site_list");
?>