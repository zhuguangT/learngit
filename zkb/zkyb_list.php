<?php
/**
 * 功能：质控月报列表
 * 作者：Mr Zhou
 * 日期：2014-06-20
 * 描述：质控月报列表
*/
include('../temp/config.php');
include('../huayan/assay_form_func.php');
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'质控审核','href'=>$current_url),
    array('icon'=>'','html'=>'质量控制月报表','href'=>$current_url)
);

$fzx_id     = FZX_ID;
$_GET['year']      = intval($_GET['year']);
$site_type  = $global['site_type'];
if($_GET['site_type']=='全部'||empty($_GET['site_type']))
{
    $selected="selected";
}
//获得任务类型
$site_op="<option value='全部' $selected >全部</option>";
$site_op.= disp_options($site_type);
$site_type_flip    = array_flip($site_type);
$_GET['site_type'] = $site_type_flip[trim($_GET['site_type'])];
$_GET['site_type'] = empty($_GET['site_type']) ? '全部' : $_GET['site_type'];
//年份
$_GET['year']   = empty($_GET['year'])? date('Y'):$_GET['year'];
//SQL条件
$sql_where = '';
$sql_where .= ' AND `cy`.`fzx_id` = '.$fzx_id;
$sql_where .= ($_GET['site_type']=='全部')?' AND 1':' AND cy.`site_type` = '.$_GET['site_type'];
$sql_where .= " AND YEAR(`cy`.`cy_date`)='{$_GET['year']}'";
    
$year_list      = '';
for($i=date('Y');$i>=2013;$i--){
    $year_list.="<option value='$i'>$i</option>";
}

//现场平行站点数
$sql    = "SELECT MONTH(`cy`.`cy_date`) AS `m`,COUNT(`cy_rec`.`id`) AS `c` 
            FROM `cy` 
            LEFT JOIN `cy_rec` ON  `cy`.`id`=`cy_rec`.`cyd_id` 
            WHERE `cy_rec`.`zk_flag` ='-6' $sql_where 
            GROUP BY `m` ORDER BY `m` desc";
$xcpx_data  = array();
$xcpx_query = $DB->query($sql);
while($row=$DB->fetch_assoc($xcpx_query)){
    $xcpx_data[$row['m']] = $row['c'];
}
//全程空白站点数
$sql    = "SELECT month(`cy`.`cy_date`) AS `m`,COUNT(`cy_rec`.`id`) AS `c` 
            FROM `cy` 
            LEFT JOIN `cy_rec` ON  `cy`.`id`=`cy_rec`.`cyd_id` 
            WHERE `cy_rec`.`sid`='0' AND `cy_rec`.`zk_flag` >0 $sql_where 
            GROUP BY `m` ORDER BY `m` desc";
$qckb_data  = array();
$qckb_query = $DB->query($sql);
while($row=$DB->fetch_assoc($qckb_query)){
    $qckb_data[$row['m']] = $row['c'];
}
//每月室内平行样品数
//AND `assay_order`.`assay_over` = 1
$sql    = "SELECT MONTH(`cy`.`cy_date`) AS `m`,COUNT(`assay_order`.`id`) AS `c` 
            FROM `cy` 
            LEFT JOIN `assay_order` ON  `cy`.`id`=`assay_order`.`cyd_id` 
            WHERE `assay_order`.`hy_flag` IN (-20,-26,-60,-66)
            GROUP BY `m` ORDER BY `m` DESC";
$snpx_query=$DB->query($sql);
$snpx_data=array();
while($row=$DB->fetch_assoc($snpx_query)){
    $snpx_data[$row['m']]=$row['c'];
}
//每月采集的全部站网站点个数
$sql    = "SELECT month(`cy`.`cy_date`) AS `m`,count(`cy_rec`.`id`) AS `c` 
            FROM `cy` 
            LEFT JOIN `cy_rec` ON `cy`.`id`=`cy_rec`.`cyd_id` 
            WHERE 1 $sql_where 
            GROUP BY `m` ORDER BY `m` DESC";
$total_sites    = array();
$t_sites_query  = $DB->query($sql);
while($row=$DB->fetch_assoc($t_sites_query)){
    $total_sites[$row['m']]=$row['c'];
}
//每月样品总瓶数
//AND `assay_order`.`assay_over`=1
$sql    = "SELECT month(`cy`.`cy_date`) AS `m`,count(`assay_order`.`id`) AS `c` 
            FROM `cy` 
            LEFT JOIN `assay_order` ON  `cy`.`id`=`assay_order`.`cyd_id` 
            WHERE `assay_order`.`hy_flag` >=0  AND `assay_order`.`sid` >= 0  $sql_where
            GROUP BY `m` ORDER BY `m` DESC";
$R=$DB->query($sql);
$total_yp=array();
while($r=$DB->fetch_assoc($R)){
    $total_yp[$r['m']]=$r['c'];
}
//每月盲样检测次数
$sql    = "SELECT month(`cy`.`cy_date`) AS `m`,count(`cy_rec`.`id`) AS `c` 
            FROM `cy_rec` 
            LEFT JOIN `cy` ON `cy_rec`.`cyd_id`=`cy`.`id`
            WHERE `cy_rec`.`sid`='-3' 
            AND `cy_rec`.`zk_flag` IN(3,23,43,63) $sql_where 
            GROUP BY `m` ORDER BY `m` DESC";
$R=$DB->query($sql);
$total_my=array();
while($r=$DB->fetch_assoc($R)){
    $total_my[$r['m']]=$r['c'];
}
//print_rr($total_yp);
//print_rr($total_sites);
$maxmonth=($_GET['year']==date('Y')) ? intval(date('n')) : 12;
for($i=1;$i<=$maxmonth;$i++){
    if(!$total_sites[$i] && !$total_yp[$i]) continue;
    //现场
    $xcpx   = ($xcpx_data[$i])?$xcpx_data[$i]:'-';
    $qckb   = ($qckb_data[$i])?$qckb_data[$i]:'-';
    $t_cy   = ($total_yp[$i]) ?$total_sites[$i] :'-';
    //室内
    $snpx       = ($snpx_data[$i])?$snpx_data[$i]:'-';
    $total_sy   = ($total_yp[$i])?$total_yp[$i]:'-';
    //盲样
    $bzyp   = ($total_my[$i])?$total_my[$i]:'-';
    //月份
    $month  = ($i<10) ? '0'.$i : $i;
    //现场
    if($xcpx!='-' || $qckb!='-'){
        $url_data   = "type=xczk&t_cy_rec=$t_cy&xcpx_cy_recs=$xcpx&qckb_cy_recs=$qckb&date={$_GET['year']}-{$month}&site_type={$_GET['site_type']}";
        $xcjl_op    = "<a href='zkyb_disp.php?$url_data' target='_blank'>查看</a>";
    }else{
        $xc_op      = $xcjl_op = '-';
    }
    //室内
    if($snpx!='-'){
        $url_data   = "type=snzk&date={$_GET['year']}-{$month}&t_cy_rec=$t_cy&site_type={$_GET['site_type']}";
        $sn_op      = "<a href='zkyb_disp.php?$url_data' target='_blank'>查看</a>";
        $url_data2   = "type=snzk2&date={$_GET['year']}-{$month}&t_cy_rec=$t_cy&site_type={$_GET['site_type']}";
        $sn_op2      = "<a href='zkyb_disp.php?$url_data2' target='_blank'>查看</a>";
    }else{
        $sn_op      = '-';
    }
    //盲样
    if($bzyp!='-'){
        $url_data   = "type=mykh&date={$_GET['year']}-{$month}&site_type={$_GET['site_type']}";
        $by_op      = "<a href='zkyb_disp.php?$url_data' target='_blank'>查看</a>";
    }else{
        $by_op      = '-';
    }
    
    $lines  .="<tr align='center'>
                <td>{$month}月</td>
                <td>$xcpx/$qckb/$t_cy</td>
                <td>$xcjl_op</td>

                <td>$snpx/$total_sy</td>
                <td>$sn_op</td>
                <td>$snpx/$total_sy</td>
                <td>$sn_op2</td>
                <td>$bzyp</td>
                <td>$by_op</td>
            </tr>";
}
disp('zkb/zkyb/zkyb_list');

?>
