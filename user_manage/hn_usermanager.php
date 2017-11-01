<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
$print	= $_GET['print'];
if($print==1){
	//这里可以根据config.php配置 关联不同打印文件
	gotourl("usermanager_print_default.php?zz={$_GET['zz']}&fzx={$_GET['fzx']}&sex={$_GET['sex']}&gw={$_GET['gw']}&zc={$_GET['zc']}&xl={$_GET['xl']}");
	exit;
}else if($print==2){
	//这里可以根据config.php配置 关联不同打印文件
	gotourl("usermanager_print_default.php?zz={$_GET['zz']}&fzx={$_GET['fzx']}&sex={$_GET['sex']}&gw={$_GET['gw']}&zc={$_GET['zc']}&xl={$_GET['xl']}&handle=download");
	exit;
}
//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'人员档案管理','href'=>$_SESSION['url_stack'][0]),//'user_manage/hn_usermanager.php'),
);$trade_global['daohang'] = $daohang;

$zz='';
if(!empty($_GET['zz'])){//判断是否有传递过来的在职状态的查看（本html页面）
	if($_GET['zz']=='离职'){
		$zz=" and b.group ='离职' ";
		$lz_sel="selected='selected'";
		$zz_sel='';
	}elseif($_GET['zz']=='在职'){
		$zz=" and b.group !='离职' ";
		$zz_sel="selected='selected'";
		$lz_sel='';
	}else{
		$zz='';
		$lz_sel='';
		$zz_sel='';
	}
}
//学历
if(!empty($_GET['xl'])){
	if($_GET['xl']=='全部'){
		$education_sql = "";
	}else{
		$education_selected = "<option value='{$_GET['xl']}'>{$_GET['xl']}</option>";
		$education_sql = " AND `whcd` LIKE '%{$_GET['xl']}%'";
	}
}
//性别
if(!empty($_GET['sex'])){
	if($_GET['sex'] == '全部'){
		$sex_sql = '';
	}else{	
		$sex_selected = "<option selected>{$_GET['sex']}</option>";
		$sex_sql = " AND b.`sex` = '{$_GET['sex']}'";
	}		
}

if(!empty($_GET['zc']) && $_GET['zc'] != '全部'){
	$zcc_sql = " AND a.zc LIKE '%$_GET[zc]%'";
}else{
	$zcc_sql = '';
}
if(!empty($_GET['gw']) && $_GET['gw'] != '全部'){
	$gww_sql = " AND a.gw LIKE '%$_GET[gw]%'";
}else{
	$gww_sql = '';
}
//职称筛选
$zc_select = '';
$zc_sql = "SELECT DISTINCT(`zc`) FROM `hn_users`";
$zc_re = $DB->query($zc_sql);
$zc_select = " 职称：<select id='zc' onchange='find_zc(this);'><option value='全部'>全部</option>";
if(!empty($_GET['zc'])){
	$zc_select .="<option selected>{$_GET['zc']}</option>";
}
while($zc_data = $DB->fetch_assoc($zc_re)){
	if(!empty($zc_data['zc'])){
		$zc_select .="<option value='$zc_data[zc]'>$zc_data[zc]</option>";
	}	
}
$zc_select .="</select> ";

//岗位筛选
$gw_select = '';
$gw_sql = "SELECT DISTINCT(`gw`) FROM `hn_users` ORDER BY `px_id`";
$gw_re = $DB->query($gw_sql);
$gw_select = " 岗位：<select id='gw' onchange='find_gw(this);'><option value='全部'>全部</option>";
if(!empty($_GET['gw'])){
	$gw_select .="<option selected>{$_GET['gw']}</option>";
}
while($gw_data = $DB->fetch_assoc($gw_re)){
	if(strpos($gw_data['gw'] , '/')){
		$arr[]=explode('/' , $gw_data['gw']);
	}else{
		$arr[]=$gw_data['gw'];
	}	
}
// print_rr($arr);
foreach($arr as $key=>$value){
	if(is_array($value)){
		foreach($value as $k=>$v){
			$arr[]=$v;
		}
		unset($arr[$key]);
	}	
	if(empty($value)){
		unset($arr[$key]);
	}
}
foreach(array_unique($arr) as $key=>$value){
	if(!empty($value)){
		$gw_select .="<option value='$value'>$value</option>";
	}	
}
$gw_select .="</select> ";
//工龄筛选
if(!empty($_GET['gl'])){
	$gl = date('Y')-$_GET['gl'];
	$gl_sql = " AND `gzny` LIKE '{$gl}%'";
}else{
	$gl_sql = '';
}
if($_GET['gzny_select']=='sele'){
	$gzny_select = "<option selected>{$_GET['gl']}</option>";
}else{
	$gzny_select = '';
}
//分中心列表
$fzx_list       = '';
$fzx_arr	= array();
if($u['is_zz']=='1'){
	$fzx_list       .= "分中心列表:<select id='fzx' name='fzx' style='max-width:200px;'><option value='全部'>全部</option>";
	$hub_list_sql   = $DB->query("SELECT * FROM `hub_info` WHERE 1");
	$hub_num	= $DB->num_rows($hub_list_sql);
	while($hub_list_rs = $DB->fetch_assoc($hub_list_sql)){
		$fzx_arr[$hub_list_rs['id']]	= $hub_list_rs['hub_name'];
		if($_GET['fzx'] == $hub_list_rs['id'] || (empty($_GET['fzx']) && $fzx_id == $hub_list_rs['id'])){
		        $fzx_list       .= "<option value='{$hub_list_rs['id']}' selected>{$hub_list_rs['hub_name']}</option>";
		}else{
		        $fzx_list       .= "<option value='{$hub_list_rs['id']}'>{$hub_list_rs['hub_name']}</option>";
		}
	}
    $fzx_list       .= "</select>";
	if($hub_num<=1){
    		$fzx_list	= '';
    	}
}
$sql_where	= '';
if(!empty($_GET['fzx'])){
	if($_GET['fzx'] != '全部'){
		$sql_where	.= " AND b.fzx_id=".$_GET['fzx']." ";
	}
}else{
	$sql_where	.= " AND b.fzx_id={$fzx_id} ";
}
//想办法将 新插入的数据放到最后面，可以将排序号
$sql="SELECT a.*,b.* , (select count(b.id) from hn_users as a right join users as b on a.uid=b.id where b.group!='0' AND b.group!='测试组' $sql_where and b.sex!='' $zz and b.userid != 'admin' ) as total  from hn_users as a right join users as b on a.uid=b.id where b.group!='0' AND b.group!='测试组' $sql_where and b.sex!='' $gl_sql $zz and b.userid != 'admin' AND b.`user_status` = '0' $zcc_sql $gww_sql $sex_sql $education_sql ORDER BY  b.fzx_id,a.px_id,b.id";
$R=$DB->query($sql);
$table_header	= " <table class='table table-striped table-bordered table-hover center' style='width:80%;'>
        <tr align=center>
	<th>序号</th>
	<th>姓名</th>
	<th>性别</th>
	<th>年龄</th>
	<th>学历</th>
	<th>职称</th>
	<th>所学专业</th>
	<th>工龄</th>
	<th>从事水质工作年限</th>
	<th>所在部门<br>岗位</th>
    <!-- <th>本岗位<br>年限</th> -->
	<th>备注</th>
	<th>操作</th>
        </tr>";
$table_str	= '';
$old_fzx_id	= $r_fzx_id	= $laxt_px_zt	= $laxt_px_line	= '';
while($r=$DB->fetch_assoc($R)){
	if(!empty($r['total'])){
		$total = '共('.$r['total'].')人';
	}else{
		$total = '';
	}
	$r_fzx_id	= $r['fzx_id'];
	//hn_user表没有数据时，就插入一条
	$laxt_px_zt	= '';
	if($r['jid']==''){
		$max_px_sql	= $DB->fetch_one_assoc("SELECT max(`px_id`) as max_px_id FROM `hn_users` AS hn LEFT JOIN `users` AS u ON hn.uid=u.id WHERE u.fzx_id='{$r['fzx_id']}' LIMIT 1");
		$max_px_id	= ++$max_px_sql['max_px_id'];
		$insert_sql	= $DB->query("INSERT INTO `hn_users` SET `px_id`='{$max_px_id}',`uid`='{$r['id']}'");
		$jid		= $DB->insert_id();
		if(!empty($jid)){
			$r['jid']	= $jid;
			$r['px_id']	= $max_px_id;
			$r['uid']	= $r['id'];
			$laxt_px_zt	= "yes";
		}

	}
	if($old_fzx_id=='' || $old_fzx_id!=$r_fzx_id){
		//查询出各个分中心有多少人
		if($_GET['fzx']=='全部'){
			$sql = "SELECT COUNT(`id`) as fzx_num_total FROM `users` WHERE `fzx_id` = '{$r_fzx_id}' AND `group`!='0' AND `group`!='测试组' AND `sex`!='' AND `userid`!='admin' GROUP BY `fzx_id`";
			$data = $DB->fetch_one_assoc($sql);
			$fzx_num_total = " &nbsp;&nbsp;&nbsp;&nbsp;共（".$data['fzx_num_total']."）人";
		}else{
			$fzx_num_total = '';
		}
		//这里将分中心的名称显示出来
		if(empty($gww_sql) && empty($zcc_sql) && empty($gl_sql) && empty($sex_sql) && empty($education_sql)){
			$fzx_name	= "<p style='margin:0 auto;width:80%;'>&nbsp;&nbsp;&nbsp;".$fzx_arr[$r_fzx_id].$fzx_num_total."</p>";
		}else{
			$fzx_name	= "<p name='man_total' style='margin:0 auto;width:80%;'>&nbsp;&nbsp;&nbsp;></p>";
		}
			
			$old_fzx_id	= $r_fzx_id;
		
		if($table_str	== ''){
			$table_str	.= $fzx_name.$table_header;
		}else if(empty($gww_sql) && empty($zcc_sql) && empty($gl_sql) && empty($sex_sql) && empty($education_sql)){
			$table_str	.= $lines.$laxt_px_line."</table>".$fzx_name.$table_header;
			$lines	= $laxt_px_line	= '';
		}
	}
	//计算年龄
	if(!empty($r['csrq'])){
		$cs_year	= substr($r['csrq'],0,4);
		$now_year	= date('Y');
		$age	= $now_year-$cs_year;
		$r['csrq']	= $age;

	}
	//计算工龄
	if(!empty($r['gzny'])){
		$gzny_years = date('Y-m-d') - $r['gzny'] .'年';
	}else{
		$gzny_years = '';
	}
	if(empty($_GET['fzx']) || $fzx_id == $r_fzx_id || $u['admin']=='1'){
		$px_modify	= ' onclick="px(this,'.$r[px_id].')"';
    	$op = "<a class='green icon-edit bigger-130' title='修改' href='./hn_usermanager_mod.php?uid=$r[id]&r=$r[userid]&sex=$r[sex]'></a>|
		<a class='red icon-remove bigger-140' title='删除' href=javascript:s_confirm("."'hn_usermanager_del.php?uid=$r[id]&userid=$r[userid]')"."></a></td>";
	}else{
		$op	= '<font color="#A29D9D">无权限</font>';
		$px_modify	= '';
	}
	//从事本岗位年限
	if(!empty($r['jsnx'])){
		$jsnx_years = date('Y-m-d') - $r['jsnx'] .'年';
	}else{
		$jsnx_years = '';
	}
	


	if($r['group']=='离职'){
		$zaizhi="离职";
	}else{$zaizhi="在职";}
	if($laxt_px_zt == 'yes'){
		$laxt_px_line	.= temp('user_manager/user_manager_line');
	}else{
    	$lines.=temp('user_manager/user_manager_line');
	}
}
if(empty($table_str)){
	$table_str	.= $table_header."<tr><td colspan='12'>没有查询到{$_GET['zz']}人员</td></tr></table>";
}else{
	$table_str	.= $lines.$laxt_px_line."</table>";
}

  //disp('user_manager_line');
disp('user_manager/user_manager');

/**
* 用于筛选
* 前台传过来筛选项，将筛选项变为 sql 语句输出
* 
*/
function add_find_sql($name , $val){
	$sql = " AND `$name` = '{$val}'";
	return $sql;
}
?>
