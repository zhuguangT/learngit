<?php
//供应商评价表 添加及修改
include "../temp/config.php";
$title="供应商信息表";
//导航
$trade_global['daohang'][]	=	array('icon'=>'','html'=>'供方评定记录表','href'=>"$rooturl/gys/pingjia.php");
$_SESSION['daohang']['pingjia']	=	$trade_global['daohang'];

if($u['hub_name']!='国家城市供水水质监测网青岛监测站'){
	$tr='<tr align="center" style="width:2cm;height:1cm;display:none">
				<td>首次列入日期</td>
				<td align="left"><input type="text" class="date_input" name="scdate" id="scdate"   size="10" value="$scdate" /></td>
				<td>评价时间</td>
				<td align="left"><input type="text" class="date_input" name="pjdate" id="pjdate"  size="10" value="$pjdate" /></td>
			</tr>';
}
$fzx_id	= $u['fzx_id'];
$parent_id = get_str($_GET["parent_id"]);
$fujian='';
$fujian_arr=array();
$gy_fwly_arr=array('设备采购类供应商','低值易耗品供应商','标准品供应商','危废处理提供商','服务类供应商','其它');
if(!empty($parent_id) || $_GET['handle']=='del_file'){
	$sql = "SELECT * FROM `gys_gl` WHERE `id` = '$parent_id'";
	$row	= $DB->fetch_one_array($sql);
	$id 	= $row['id'];
	$fujian_arr=json_decode($row["fujian"]);
	if(empty($fujian_arr)){
		$fujian='';
	}else{
		foreach(json_decode($row["fujian"]) as $key=>$value){
		$fj_name = substr($value,12) ;
		$fujian.=<<<ETF
				<ul>
					<li><a style="cursor:pointer;" data-toggle="modal" data-target="#myModal" onclick="show_img(this,$key);">$fj_name</a>&nbsp;&nbsp;&nbsp;&nbsp;<a style="color:red;cursor:pointer;" onclick="del_file($id,$key);">删除</a></li>
				 </ul>
				 <!--图片遮罩层-->
				<div style="display:none;" class="img_$key">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" onclick="close_img(this,$key);" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="myModalLabel">图片:$fj_name</h4>
				      </div>
				      <div class="modal-body">
				        <a href="$rooturl/gys/upfiles/$value" target="_blank;"><img src="$rooturl/gys/upfiles/$value" title="$fj_name"  style="width:50%;"/></a>
				      </div>
				      <div class="modal-footer">
				        <button type="button" class="btn btn-default" onclick="close_img(this,$key);">关闭</button>
				      </div>
				    </div>
				  </div>
				</div>
ETF;
		}
	}
	//供应服务领域
	$gyfwly_select=$row['gy_fwly'];
	$gy_fwly="<option>请选择</option>";
	foreach($gy_fwly_arr as $k=>$v){
		if($v==$gyfwly_select){
			$gy_fwly.="<option value='$v' selected='selected'>$v</option>";
		}else{
			$gy_fwly.="<option value='$v'>$v</option>";
		}
	}
	//联系人
	$lianxiren_arr=json_decode($row['lianxi'],true);
	$lianxi_num=2;
		foreach($lianxiren_arr as $k=>$v){
		$lianxiren.=<<<EOF
		<tr align='center' style='width:2cm;height:1cm' name='lianxiren'>
		<td>联系人$lianxi_num</td>
		<td align='left'><input type='text' class=inputc name='lxr2[]'  size='14' value='$v[lxr]'/></td>
		<td>联系方式</td>
		<td align='left'><input type='text' class=inputc name='lxdh2[]'  size='14' value='$v[lxdh]' /></td>
		</tr>
EOF;
$lianxi_num++;
	}	

	//年度评价
	$pingjia_arr=json_decode($row['year_pingjia'],true);
	$year=date('Y');
	$s_year=$begin_year;
	for($s_year;$s_year<=$year;$s_year++){
		$year_arr[]=$s_year;
	}
	//默认选中数据库存储的年份
	foreach($pingjia_arr as $k=> $v){
		foreach($year_arr as $k2 => $v2){
			if($v2==$v[niandu]){
				$niandu.="<option value='$v2' selected>$v2</option>";
			}else{
				$niandu.="<option value='$v2'>$v2</option>";
			}
		}
		$pingjia.=<<<EOF
		<tr align='center' style='width:2cm;height:1cm' name='year_pingjia'><td><select name="niandu[]">$niandu</select></td><td><input type='text' class=inputc name='zonghe_pingjia[]' value='$v[zonghe_pingjia]'></td><td><input type='text' class=inputc name='pingjia_ren[]' value='$v[pingjia_ren]'></td><td><input type='text' class="date_input"  name='pingjia_time[]' value='$v[pingjia_time]'></td></tr>
EOF;
	}
	if(empty($niandu)){
		foreach($year_arr as $k => $v){
			$niandu.="<option value='$v'>$v</option>";
		}
	}
	
	
	$sname	= $row["sname"];//供应商公司
	$dz		= $row["dz"];//地址
	$lxr	= $row["lxr"];//联系人
	$lxdh	= $row["lxdh"];//电话
	$pjr	= $row["pjr"];//评价人 
	$pjdate	= $row["pjdate"];//评价时间
	$pjbh	= $row["pjbh"];//编号
	$cpzl	= $row["cpzl"];//产品质量
	$fuwu	= $row["fuwu"];//服务
	$xinyu	= $row["xinyu"];//信誉
	$jiage	= $row["jiage"];//价格（青岛  评定结论）
	$fujian	= $fujian;//附件
	$cunfang= $row["cunfang"];//存放位置
	$swdjz=$row['swdjz'];//税务登记证
	$zzjgdm=$row['zzjgdm'];//组织机构代码
	$dqdate=$row['dqdate'];//到期时间
	$yyzz=$row['yyzz'];//营业执照颁发日期
	$gs_xinyong=$row['gs_xinyong'];//公司社会信用代码
	$beizhu=$row['beizhu'];//备注
	$dengji_name=$row['dengji_name'];//登记人
	$first_dengji_time=$row['first_dengji_time'];//初次登记时间
	$dengji_ren=$row['dengji_ren'];
		//青岛新增
	$yingye = $row["yingye"];
	$zhuzhi1 = $row["zhuzhi"];
	if($zhuzhi1){
		$zhuarr = explode('*',$zhuzhi1);
		$zhuzhi = $zhuarr[0];
		$daoqi =  $zhuarr[1];
	}
	if($row['json']){
		$fujian = "<ul>";
		$leixing = array('bmp','jpg','png','gif');
		$json = json_decode($row['json'],true);
		if($json['fujian']){
			$fuarr = explode('||',$json['fujian']);
			foreach($fuarr as $kk){
				$sarr = explode('/',$kk);
				$ge1 = count($sarr);
				$fujian .= "<li><a href='".$kk."'>".$sarr[$ge1-1]."</a></li>";
			}
		}
		$fujian .="</ul>";
	}
}
if($_GET['handle']=='add' || $_GET['parent_id'] == ''){
	$gy_fwly="<option>请选择</option>";
	foreach($gy_fwly_arr as $k=>$v){
		$gy_fwly.="<option value='$v'>$v</option>";
	}
	$year=date('Y');
	$s_year=$begin_year;
	for($s_year;$s_year<=$year;$s_year++){
		$year_arr[]=$s_year;
	}
	foreach($year_arr as $k=>$v){
		$niandu.="<option value='$v'>$v</option>";
	}
	$handle = '<input type="hidden" value="add" name="handle">';
}else{
	$handle = '';
}
disp("gysgl_update");
?>
