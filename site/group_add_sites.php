<?php
/**
 * 功能：添加站点
 * 作者：zhangdengsheng
 * 日期：2014-07-09
 * 描述：添加新站点（可添加多条），并录入一些站点的常规信息及关联项目
*/
include '../temp/config.php';
require_once "$rootdir/inc/site_func.php";
if($_GET['action']=='fzxgl'){
	$daohang = array(
        		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
                array('icon'=>'','html'=>'分中心管理','href'=>"site/group_add_sites.php?site_type=0&action=fzxgl")
	);
	$trade_global['daohang'] = $daohang;
}

$lxing   = $_GET['q'];//得到水样类型
$fzx_id	 = FZX_ID;//中心
$leix=get_syleixing('','123');//获取水样类型
#######################统计参数
$tjcs = $tjcs2 = "";
$queryTjlx   = $DB->query("select id,module_value1 from `n_set` where `fzx_id`=$fzx_id AND `module_name`='tjcs' AND `module_value3`='1'");
while($rsTjlx=$DB->fetch_assoc($queryTjlx)){
	$tjcslx[]=$rsTjlx['id'];//各中心自己的统计参数
	$tjcs .= "<label id='".$rsTjlx['id']."'><input type=\"checkbox\" name=\"tjcs_name[]\"  value=\"".$rsTjlx['id']."\" />".$rsTjlx['module_value1']."</label>&nbsp;&nbsp;";
	$tjcs2 .= "<span><input type=\"text\" onkeyup=\"value=value.replace(/[(\ )(\~)(\`)(\!)(\@)(\#)(\$)(\￥)(\%)(\^)(\&)(\……)(\*)(\()(\))(\-)(\——)(\_)(\+)(\=)(\[)(\])(\【)(\】)(\{)(\})(\|)(\\)(\;)(\；)(\：)(\:)(\')(\‘)(\’)(\“)(\”)(\,)(\，)(\.)(\。)(\/)(\<)(\>)(\《)(\》)(\?)(\？)(\、)(\)]+/g,'')\" onbeforepaste=\"clipboardData.setData('text',clipboardData.getData('text').replace(/[(\ )(\~)(\`)(\!)(\@)(\#)(\$)(\￥)(\%)(\^)(\&)(\……)(\*)(\()(\))(\-)(\——)(\_)(\+)(\=)(\[)(\])(\【)(\】)(\{)(\})(\|)(\\)(\;)(\；)(\：)(\:)(\')(\‘)(\’)(\“)(\”)(\,)(\，)(\.)(\。)(\/)(\<)(\>)(\《)(\》)(\?)(\？)(\、)(\)]+/g,''))\" lxid='".$rsTjlx['id']."' huifu='".$rsTjlx['module_value1']."' value=\"".$rsTjlx['module_value1']."\" onblur=\"uplx(this)\" /></span>&nbsp;&nbsp;";
}
##################获取分中心
if(!isset($_GET['site_type'])){
  $_GET['site_type']='0';
}
if(empty($global['firm_type'])){
	$global['firm_type']	= 'zls';
}
//自来水的站点添加信息
if($global['firm_type'] == 'zls'){
	$fzx="<tr id=\"dzd\">
				<td align=\"center\" id=\"wuyongde\">站名:</td>
				<td id=\"wuyongde\"><input type=\"text\" onblur='site_nameyz(this)' name=\"site_name[]\"  placeholder=\"不能为空\" required=\"required\"  value=\"\"><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td align=\"center\" id=\"wuyongde\">水样类型:</td>
				<td id=\"sylx\" id=\"wuyongde\"><select name=\"customers[]\" onchange=\"showCustomer()\" id='sl'>$leix</select></td>
			</tr>
			<tr>
				<td align=\"center\" id=\"wuyongde\">行政区:</td>
				<td id=\"wuyongde\"><input type=\"text\" name=\"xz_area[]\"><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	            <td align=\"center\" id=\"wuyongde\">站址:</td>
	            <td id=\"wuyongde\"><input type=\"text\" name=\"site_address[]\"size=\"30\"></td>
	        </tr>";
}else{//水文站点添加信息
	if($u['is_zz']=='1'&&$_GET['site_type']=='0'){
		$sql_fenzx = $DB->query("SELECT id,hub_name FROM `hub_info` WHERE is_zz!='1' ORDER BY `id` ASC");
		while($zx  = $DB->fetch_assoc($sql_fenzx))
		{
			$fenzx.="<option value='$zx[id]'>$zx[hub_name]</option>";
		}
		$fzx="<tr id=\"dzd\"><td align=\"center\" id=\"wuyongde\">站名:</td>
				<td id=\"wuyongde\"><input type=\"hidden\" name=\"actions\" value=\"tjjdrw\" /><input type=\"text\" name=\"site_name[]\"onblur='site_nameyz(this)'  placeholder=\"不能为空\" required=\"required\"  value=\"\"><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td align=\"center\" id=\"wuyongde\">水样类型:</td>
				<td id=\"sylx\" id=\"wuyongde\"><select name=\"customers[]\" onchange=\"showCustomer()\" id='sl'>$leix</select></td>
				<td align=\"center\" id=\"wuyongde\">分中心:</td>
				<td id=\"wuyongde\"><select name='fenz[]'>$fenzx</select></td></tr>
				<tr>
				<td align=\"center\" id=\"wuyongde\">站点编码:</td>
				<td id=\"wuyongde\"><input type=\"text\" name=\"code[]\"onblur='zhanmayz(this)' value=\"\" ><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td align=\"center\" id=\"wuyongde\">河(库)名:</td>
				<td id=\"wuyongde\"><input type=\"text\" name=\"river_name[]\"></td>
	            <td align=\"center\" id=\"wuyongde\">站址:</td>
	            <td id=\"wuyongde\"><input type=\"text\" name=\"site_address[]\"size=\"30\"></td>
			   </tr>";
	}else{
		$fzx="<tr id=\"dzd\"><td align=\"center\" id=\"wuyongde\">站名:</td>
				<td id=\"wuyongde\"><input type=\"text\" onblur='site_nameyz(this)' name=\"site_name[]\"  placeholder=\"不能为空\" required=\"required\"  value=\"\"><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td align=\"center\" id=\"wuyongde\">水样类型:</td>
				<td id=\"sylx\" id=\"wuyongde\"><select name=\"customers[]\" onchange=\"showCustomer()\" id='sl'>$leix</select></td>
				<td align=\"center\" id=\"wuyongde\">站点编码:</td>
				<td id=\"wuyongde\"><input type=\"text\" name=\"code[]\" onblur='zhanmayz(this)' value=\"\" ><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td></tr>
				<tr>
				<td align=\"center\" id=\"wuyongde\">河(库)名:</td>
				<td id=\"wuyongde\"><input type=\"text\" name=\"river_name[]\"><span style='color:#ff3300'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	            <td align=\"center\" id=\"wuyongde\">站址:</td>
	            <td id=\"wuyongde\"><input type=\"text\" name=\"site_address[]\"size=\"30\"></td><td id=\"wuyongde\"></td><td id=\"wuyongde\"></td></tr>";
	}
}
####################获取模板
 $S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id' " );
 while( $row = $DB->fetch_assoc( $S ) ) {
	$mbxm.="<option value='$row[module_value1]'>$row[module_value2]</option> ";
}
if(!isset($lxing)){$lxing='1';$lxbs='1';}
if(isset($lxing)){
	$strs     = array_unique(explode(',', $lxing));//去除重复字符
	$leixing  = implode(',',$strs);//组成字符串
	$leixings = explode(',', $leixing);//组成数组
	//print_rr($leixings); 
	if($leixing==''){$leixing ="''";}//解决当水样类型为空时sql语句报错，给他加上引号
	//$strs     = array_unique($sit);//数组去除重复
	//$sits     = array_intersect($sit);取交集
	$sit   = array();
	$ssit  = array();
	$lxnum =count($leixings);
	##################获取公测项目
	for($i=0;$i<$lxnum;$i++){
		$sgc ="SELECT * FROM `leixing` WHERE id='{$leixings[$i]}'";
		$sgcc=$DB->fetch_one_assoc($sgc);
		if($sgcc[parent_id]!=0){
			$sql= $DB->query("SELECT xmfa.*,av.id as vid,av.value_C FROM `xmfa` inner join `assay_value` as av on xmfa.xmid=av.id where xmfa.fzx_id='$fzx_id' and xmfa.lxid in ('{$leixings[$i]}','{$sgcc['parent_id']}') and xmfa.act='1' and xmfa.mr='1' group by xmfa.xmid");
			while($sqll =$DB->fetch_assoc($sql)){
			$sit[$i][]=$sqll[vid];}
		}else{
			$sql= $DB->query("SELECT xmfa.*,av.id as vid,av.value_C FROM `xmfa` inner join `assay_value` as av on xmfa.xmid=av.id where xmfa.fzx_id='$fzx_id' and xmfa.lxid in ('{$leixings[$i]}') and xmfa.act='1' and xmfa.mr='1' group by xmfa.xmid");
			while($sqll =$DB->fetch_assoc($sql)){
			$sit[$i][]=$sqll[vid];}
		}
		if(empty($sit[$i])){//判断项目为空
			$kk='1';
		}else{
			if($i!=0&&count($sit[$i])!=0&&count($sit[$i-1])!=0 ){
				$sit[$i] = array_intersect($sit[$i],$sit[$i-1]);//多水样类型取交集(公测项目)
				$sit[$i] = array_intersect($sit[$i-1],$sit[$i]);//多水样类型取交集(公测项目)
			}
		}
	}//print_rr($sit[$lxnum-1]);
	if($kk!=1){
		foreach ($sit[$lxnum-1] as $key => $cxm){//公测项目
				$RR=$DB->query("SELECT id,value_C FROM `assay_value` WHERE id=$cxm");
				while($rxm=$DB->fetch_assoc($RR))
				{
					$site[]=$rxm[id];
					if($u['admin']=='1'){ //查询是否管理员显示id 
						$id=$rxm[id];
					}
					$xm.="<label class=\"show\" style=\"float: left; margin-bottom: 1px; margin-left: 1px; height: 43px; width: 132px; border: 1px solid rgb(215, 215, 215); background-color: rgb(255, 255, 255); cursor: pointer;text-align: left;\"><input name=\"xid[]\"  value=\"$rxm[id]\" type=\"checkbox\" onclick=\"return tjxm();\">$rxm[value_C]$id</label>";
				}
		}
	}else{}
	if(strlen($leixing)!='1'){$fl_line.="<tr><td align='center' colspan='2'>单测项目</td></tr>";}
##################获取单测项目
	for($i=0;$i<$lxnum;$i++){
		$ssgc="SELECT * FROM `leixing` WHERE id='$leixings[$i]'";
		$ssgcc=$DB->fetch_one_assoc($ssgc);

		$ZZ= $DB->query("SELECT id as vid,value_C FROM `assay_value` where 1");
		while($sql_xm =$DB->fetch_assoc($ZZ)){
			$ssit[]   =$sql_xm[vid]; 
		}

		if('1'==$lxnum){
			//1个站点
			if(count($ssit)!=0){
				if(count($site)!=0){
					//说明：公测=单测有方法项目
					$sttt[$i]	= array_diff($ssit,$site);//全部项目和公测项目的取差集(单测项目)
				}else{
					$sttt[$i]	= $ssit;
				}
			}
		}else{
			//多个站点
			$fl_line.= '<tr align=center class="xm" ><td style="vertical-align:middle;text-align:center;" >'.$ssgcc[lname].'</td><td  >';
			if(count($site)==0){ //没有公测项目
				if(count($sit[$i])!=0&&count($ssit)!=0){
					$stt[$i]=$sit[$i];//单水样类型有方法项目(单测有方法项目)
					$sttt[$i]	= array_diff($ssit,$stt[$i]);//全部项目和单测有方法项目取差集(单测无方法项目)
				}
			}else{
				$st[$i]		= array_diff($ssit,$site);//全部项目和公测项目的取差集(单测全部项目)
				if(count($st[$i])!=0){
				$stt[$i]	= array_intersect($sit[$i],$st[$i]);//单水样类型有方法项目和单测全部项目取交集(单测有方法项目)
				$sttt[$i]	= array_diff($st[$i],$stt[$i]);//单测全部项目和单测有方法项目取差集(单测无方法项目)
				}
			}
			if(count($stt[$i])!=0){
				foreach ($stt[$i] as $key => $a) { 
					$cxmm=$DB->fetch_one_assoc("SELECT id as vid,value_C FROM `assay_value` where id='$a' GROUP BY `vid`");
					if($u['admin']=='1'){ //查询是否管理员显示id 
						$wid=$cxmm[vid];
					}
					$fl_line.= "<label class=\"show\" style=\"float: left; margin-bottom: 1px; margin-left: 1px; height: 43px; width: 132px; border: 1px solid rgb(215, 215, 215); background-color: rgb(255, 255, 255); cursor: pointer;text-align: left;\"><input name=\"xid[]\"  value=\"$cxmm[vid]\" type=\"checkbox\" onclick=\"return tjxm();\">$cxmm[value_C]$wid</label>";
				}
			}
		}
		/*if(count($sttt[$i])!=0){
		foreach ($sttt[$i] as $key => $a) {
			$ccxmm=$DB->fetch_one_assoc("SELECT id as vid,value_C FROM `assay_value` where id='$a' GROUP BY `vid`");
			$fl_line.="<li class=\"lizf\"><label><a class=\"\" target=\"_blank\" onclick=\"pzfangfa($ccxmm[vid])\">$ccxmm[value_C]</a></label></li>";
			}
		}			*/
		$fl_line.= '</td></tr>';
		
	}
	$fl_line.= "</td></tr><tr align=center class='xm' ><td colspan='2'>*说明：没有显示的项目需要去检验方法配置中配置方法</td></tr>";
	if($lxbs!='1'){echo($xm.'@'.$fl_line);  }
	
}
if($_GET['bs']=='fzx'){
	echo temp('group_add_sites.html');
}else{
	disp('group_add_sites');}
?>
