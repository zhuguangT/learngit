<?php
// 与采样有关的function

/*
采样单编号规则
前缀 A站网 B委托 C临时
年月 200704
序号 001-999(按月小排队每月从001开始计)

样品编号规则:
前缀 站网/临时 A
     委托 B
年月 200704
序号 0001-9999(按年大排队)

*/
//取采样单信息


//$site_type_num 是0-3 对应的是 AABZ   $ypxs是样品编号 开头形式 ABC  如果等于0  是ABA  如果1 是ACB
function get_last_bar_code( $site_type_num, $year,$ypxs='0' ) {
    global $DB;
    $bar_code_pre = get_bar_code_pre( $site_type_num,$ypxs );
    $init_date = $year . '-01-01';
    $sql = "
SELECT RIGHT(bar_code, 4) AS bar_code
FROM cy_rec LEFT JOIN cy ON cy.id = cy_rec.cyd_id
WHERE cy.cy_date >= '{$init_date}' ";
    $sql .= ($site_type_num <= 1)
    	? ' AND cy.site_type IN (0,1) '
    	: " AND cy.site_type = '$site_type_num' ";
if($ypxs)
    $sql .= " AND cy_rec.bar_code  LIKE '$bar_code_pre%'  ORDER BY bar_code DESC LIMIT 1";
else
 $sql .= " AND cy_rec.bar_code != '' ORDER BY bar_code DESC LIMIT 1";
    
$last_bar_code = $DB->fetch_one_assoc( $sql );
    if ( $last_bar_code )
        return  $bar_code_pre . $last_bar_code['bar_code'];
    else
        return $bar_code_pre . '0000';
}
//如果样品编号 中的 临时任务要 选用 C 做为开头 只需 把 下面数组中  1=>'A'  改为  1=>'C'
//除北京外 其他 中心都说  临时任务的 样品编号都是 以C 开头的

function get_bar_code_pre( $site_type_num ,$lb='0') {
   if($lb>0)
   $pre_data = array(
    0=>'A',
    1=>'C',
    2=>'B',
    3=>'Z'
    );
   else
    $pre_data = array(
    0=>'A',
    1=>'A',
    2=>'B',
    3=>'Z'
    );
    return $pre_data[$site_type_num];
}

function get_cyd_bh_pre( $site_type_num, $cy_date ) {
	$pre_data = array(
	0=>'A',
	1=>'C',
	2=>'B',
	3=>'Z',
	);
    return $pre_data[$site_type_num] . date( 'Ym', strtotime( $cy_date ) );
}

function new_bar_code( $last_bar_code, $cy_date ) {
    $prefix = date('Ym', strtotime($cy_date));
    $bar_code_pre = $last_bar_code{0} . $prefix;
    $new_num = (int)substr( $last_bar_code, -4) + 1;
    $t = '';
    for( $k = 0; $k < 4 - strlen( $new_num ); $k++ )
        $t .= '0';
    return $bar_code_pre . $t . $new_num;
}

function new_cyd_bh( $site_type_num, $cy_date ) {
    global $DB;
    $cyd_bh_pre = get_cyd_bh_pre( $site_type_num, $cy_date);
    $sql = "
SELECT * FROM cy
WHERE cyd_bh LIKE '$cyd_bh_pre%' AND site_type = {$site_type_num}
ORDER BY cyd_bh DESC LIMIT 1";
    $cyd = $DB->fetch_one_assoc( $sql );
    if( $cyd['cyd_bh'] ) {
        $cyd_bh = (int)substr( $cyd['cyd_bh'], 7 );
        $cyd_bh++;
        if ( $cyd_bh < 10 )
            return $cyd_bh_pre . '00' . $cyd_bh;
        if ( $cyd_bh < 100 )
            return $cyd_bh_pre . '0' . $cyd_bh;
        return $cyd_bh_pre . $cyd_bh;
    }
    else
        return $cyd_bh_pre . '001';
}


function get_error( $site_id, $group_name ) {
    global $DB;
    $msg = "";
    while( list( , $sid ) = each( $site_id ) ){
        if( $sid != -1 ) {
            $sql = "
    SELECT s.site_name, sg.assay_values FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id
    WHERE sg.site_id = $sid AND sg.group_name = '{$group_name}'";
            $A = $DB->fetch_one_assoc( $sql );
            if( !elementsToArray($A['assay_values']) )
                $msg .= "$A[site_name]站点未设置化验项目，<br />";
        }
        else {
            if( !elementsToArray( get_para( "全程空白项目" ) ) )
                $msg .= "你选择了全程序空白质控, 却没有设置全程序空白检验项目.<br />";
        }
    }
    return $msg;
}

function get_xcpx_error( $xcpx_sites, $group_name ) {
    global $DB;
    $msg = "";
    $xcpx_vids = elementsToArray( get_para( "现场平行项目" ) );
    if( !$xcpx_vids )
        $msg .= "你选择了现场平行质控, 却没有设置任何现场平行检验项目.<br />";
    for( $i = 0; $i < count( $xcpx_sites ); $i++ ) {
        $sql = "
SELECT s.site_name, sg.assay_values FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id
WHERE sg.site_id = {$xcpx_sites[$i]} AND sg.group_name = '{$group_name}'";
        $xcpx = $DB->fetch_one_assoc( $sql );
        $xcpx_site_vids = elementsToArray( $xcpx['assay_values'] );
        $spare_xcpx_vids = array_diff( $xcpx_vids, $xcpx_site_vids );
        if ( $spare_xcpx_vids )
            $msg .= "{$group_name} 的 {$xcpx['site_name']} 站点本身不需要做<b>".get_c_items( $spare_xcpx_vids ) . "</b>检测项目,该站点无法就此项目进行<b> 现场平行 </b>质控!<br />";
    }
    return $msg;
}

function date_arrage( $date_str ){
    if( !$date_str )
        return array(date('Y-m-d'));
    else {
        $cy_dates = $date_str;
        $cy_dates = str_replace(",",' ',$cy_dates);  //半角逗号换为半角空格
        $cy_dates = str_replace("，",' ',$cy_dates); //全角逗号换为半角空格
        $cy_dates = str_replace("　",' ',$cy_dates); //全角空格替换为半角空格

        $cy_dates = str_replace('/','-',$cy_dates); //日期分割符
        $cy_dates = str_replace('－','-',$cy_dates); //日期分割符
        $cy_dates = str_replace('.','-',$cy_dates); //日期分割符
        $cy_dates = str_replace('。','-',$cy_dates); //日期分割符
    }
    $cy_riqi_list = elementsToArray( $cy_dates, ' ' );
    $cy_date_list = array();
    while( list( , $cy_date ) = each( $cy_riqi_list ) ) {
        if( strlen( $cy_date ) < 6 )
            $cy_date = date('Y-') . $cy_date;
        $aDate = strtotime( $cy_date );
        if( '-1' == $aDate || $cy_date < date('Y-m-d') ) {
            continue;
        }
        else
            $cy_date_list[] = date( 'Y-m-d', $aDate );
    }
    return $cy_date_list;
}

function get_filt_vids_flag( $cyd_id ) {
    $cyd = get_cyd( $cyd_id );
    return $cyd['cy_flag'] && $cyd['xc_exam_flag'];
}

#创建不存在的关联元素, 并将其值置为 0
function my_yp_filter( $data ) {
    global $e2c;
    while( list( $e, $c ) = each( $e2c ) )
        if( !isset( $data[$e] ) )
            $data[$e] = '0';
    reset( $data );
    reset( $e2c );
    return $data;
}

function isAllowedRWJS($cyd_id) {
    global $u, $DB;
    $isAllowed = false;
    $username = trim($u['userid']);
    $rwjs_username = trim(get_para('任务接受人'));
    if($username == $rwjs_username) {
        $isAllowed = true;
    } else {
        $sql = "SELECT userid FROM assay_pay WHERE cyd_id = $cyd_id GROUP BY userid";
        $res = $DB->query($sql);
        if($DB->rows <= 2) {
            while($row = mysql_fetch_assoc($res)) {
                if($row['userid'] == $username) {
                    $isAllowed = true;
                    break;
                }
            }
        }
    }
    return $isAllowed;
}
