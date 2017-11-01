<?php
include '../temp/config.php';
require_once '../inc/cy_func.php';
require_once '../huayan/assay_form_func.php';
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'任务接收单','href'=>'./xd_csrw/rwjs.php'),
);
if(!$u['userid']){
	nologin();
}
$fzx_id=$u['fzx_id'];
//签字处理
if(($_GET['action']=='jsall')&&$_GET['wjs']){
	$wjsarr = explode(',',$_GET['wjs']);
	foreach($wjsarr as $vv){
		$psql = $DB->fetch_one_assoc("select * from assay_pay where id='$vv'");
		up_new_hyd_bt($psql);
	}
}

//签字处理结束
if($_GET['cyd_id']&&$u['userid']){
	$cyd = get_cyd( $_GET['cyd_id'] );
	$xiala ='';//按批显示时不显示下拉菜单
	$pici = "<table width='100%'><tr>
		<th width='40%'>批次名称：{$cyd['group_name']}</th>
		<th width='40%'>采样日期：{$cyd['cy_date']}</th>
		<th width='20%'></th>
	</tr></table>";
	$line = '';
	$i ='1';
    $sql = $DB->query("select * from assay_pay where cyd_id='".$_GET['cyd_id']."'");
    while($re = $DB->fetch_assoc($sql)){
    	//json处理
    	$json = json_decode($re['json'],true);
    	$js_json = $json['rwjs'];
   		 if($js_json['zt']=='已接受'){
           $zt = '已接受';
        }else{
           $zt = '未接受';
        }
        //人员处理
        if($re['userid2']){
            $jsr = $re['userid'].'、'.$re['userid2'];
        }else{
            $jsr = $re['userid'];
        }
        //现场项目处理
        if($re['is_xcjc']){
            $beizhu = '现场项目';
            $zt = '';
        }else{
            $beizhu = '';
        }
        $js_time	= $re['start_time'];//任务接收日期
        if(($zt != '已接受')&&$re['start_time']){//如果任务没有接收，但是已经打开过化验单了，就当做已经接收了，直接读取打开化验单时间
            $zt = '已接受';
        }else if(($zt != '已接受')&&!$re['start_time']){
        	$jsr = '';
        	$js_time = '';
        }
        $line .= "<tr align='center'><td>$i</td><td>".$re['assay_element']."</td><td>".$re['create_date']."</td><td>$zt</td><td>$jsr</td><td>".$js_time."</td><td>$beizhu</td></tr>";
        $i++;
    }
}else if(($_GET['cyd_id']=='')&&$u['userid']){
	//处理参数
	if( !$_GET['year'] ){
	    $_GET["year"] = date( "Y" );
	}
	if( !$_GET['month'] ){
	    $_GET["month"] = date( "m" );
	}
	if( !$_GET['js'] ){
	    $_GET["js"] = '未接受';
	}
	
	$pici = "";
	//所有年
	$year_data[] = $_GET["year"];
	for( $i = date('Y'); $i >= 2005; $i-- )
	    if( $i != $_GET['year'] ) 
	        $year_data[] = $i;

	$month_data[] = $_GET["month"];

	$year_list = disp_options( $year_data );
	//所有月
	$rs_month = $DB->fetch_one_assoc("SELECT month(cy_date) as m FROM `cy` WHERE `fzx_id`='$fzx_id' AND year(cy_date)='{$_GET['year']}' AND month(cy_date)>'".date('m')."' GROUP BY month(cy_date) ORDER BY month(cy_date) DESC LIMIT 1");
	if($rs_month['m']){
		$month_max	= $rs_month['m'];
	}else{
		$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
	}
	$month_data = array( $_GET["month"]);
	for( $i = $month_max; $i >= 1; $i-- ) {
	    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
	    if( $month_text != $_GET['month'] )
	        $month_data[] = $month_text;
	}
	$month_list = disp_options( array_unique($month_data) );
	//处理任务接受的下拉菜单
	$zt_list = '';
	//处理个人数据
	if($_GET['js']=='未接受'){
		$where_sql = " and ((start_time='' or  start_time is null) and (json is null OR json not like '%rwjs%')) ";	
		$js_list = "<option value='全部'>全部</option><option value='已接受'>已接受</option><option value='未接受' selected>未接受</option>";
	}else if($_GET['js']=='全部'){
		$where_sql = "  ";
		$js_list = "<option value='全部' selected>全部</option><option value='已接受'>已接受</option><option value='未接受'>未接受</option>";
	}else if($_GET['js']=='已接受'){
		$where_sql = " and ( start_time<>'' or json like '%rwjs%' ) ";
		$js_list = "<option value='全部' >全部</option><option value='已接受' selected>已接受</option><option value='未接受'>未接受</option>";
	}else{
		$where_sql = " and (json is null OR json not like '%rwjs%') ";	
		$js_list = "<option value='全部'>全部</option><option value='已接受'>已接受</option><option value='未接受' selected>未接受</option>";
	}
	//当前时间
	$js_sql = $DB->query("select * from assay_pay where (userid='".$u['userid']."' or userid2='".$u['userid']."') AND year(create_date)='{$_GET['year']}' AND month(create_date)='{$_GET['month']}' $where_sql ORDER BY month(create_date) DESC");
	$i = 1;
	while($re = $DB->fetch_assoc($js_sql)){
		//json处理
    	$json = json_decode($re['json'],true);
    	$js_json = $json['rwjs'];
   		 if($js_json['zt']=='已接受'){
           $zt = '已接受';
        }else{
           $zt = '未接受';
        }
        if($js_json['js_date']){
        	$js_time = $js_json['js_date'];
        }
        //人员处理
        if($re['userid2']){
            $jsr = $re['userid'].'、'.$re['userid2'];
        }else{
            $jsr = $re['userid'];
        }
        //现场项目处理
        if($re['is_xcjc']){
            $beizhu = '现场项目';
            $zt		= '已接受';//到生成化验单时， 现场检测项目肯定已经接受了不应再确定一次
        }else{
            $beizhu = '';
        }
        if(($zt != '已接受')&&$re['start_time']){//如果任务没有接收，但是已经打开过化验单了，就当做已经接收了，直接读取打开化验单时间
            $js_time = $re['start_time'];
            $zt = '已接受';
            if($_GET['js']=='未接受'){
            	continue;
            }
        }else if(($zt != '已接受')&&!$re['start_time']){
        	$jsr = '';
        	$js_time = '';
        }
        $line .= "<tr align='center'><td>$i</td><td>".$re['assay_element']."</td><td>".$re['create_date']."</td><td zt='$zt' pid='$re[id]'>$zt</td><td>$jsr</td><td>".$js_time."</td><td>$beizhu</td></tr>";
        $i++;
	}

	$xiala ='<div class="widget-header header-color-blue4 center"><div class="widget-toolbar center">
		年份选择:<select id="year"      onchange="redirect()">'.$year_list.'</select>
		月份选择:<select id="month"     onchange="redirect()">'.$month_list.'</select>
		任务状态:<select id="zt"     onchange="redirect()">'.$js_list.'</select> 
		<input type="button" value="接受所有任务" onclick="jsall()"> 
	</div>';
}


disp("rwjs.html");
?>
