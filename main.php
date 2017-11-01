<?php
/**
 * 文件名：main.php
 * 功能：系统首页（个人任务）
 * 作者: Mr Zhou
 * 日期: 2014-03-31
 * 描述:测试git
*/
include ('./temp/config.php');
$fzx_id = FZX_ID;
//$can_click_vid  = array(1,2,3,6,58,86,93,94,95,96,97,99,100,103,104,105,107,108,114,118,119,120,121,122,133,135,137,138,141,152,154,157,159,161,166,179,181,182,185,186,190,198,280,309,315,316,317,323,386,484,494,496,497,498,499,503,544,545,569,592,595,598);//这个数组之外的项目是不允许进入化验单的
$can_click_vid	= array();
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'));

//只检索最近两个月内数据
$last_date = date('Y-m-01',strtotime('-2 months'));

####################未完成的采样任务
/*
'status'    => array(
    '0' => '采样任务未确认',
    '1' => '采样任务已下达',
    '2' => '采样任务已接受',
    '3' => '已采样',
    '4' => '样品已审核',
    '5' => '样品已接收',
    '6' => '测试任务已下达',
    '7' => '已完成化验',
    '8' => '报告已签发',
    )
*/
$cy_tuihui_num  = 0;
$cy_last_date	= $last_date;
if(date('Y-m-d')<='2015-11-14'){
	$cy_last_date	= '2015-10-14';//采样模块刚开始使用，隐藏之前的任务。等到这个日期 ，后就可以恢复原来的状态了
}
$sql = "SELECT * FROM `cy` WHERE `cy`.`cy_date` > '$cy_last_date' AND `fzx_id` = '$fzx_id' AND ((`cy_user` = '{$u['userid']}' AND (`cy_user_qz`='' OR `cy_user_qz` is null)) OR (`cy_user2` = '{$u['userid']}' AND (`cy_user_qz2`='' OR `cy_user_qz2` is null))) and cy.`status`<'7' ORDER BY cy.status,cy.cy_date";
$res = $DB->query($sql);
$result_cy = $result_hy = array();
while( $row = $DB->fetch_assoc($res)){
    if($row['json']!=''){
        $cy_json   = json_decode($row['json'],true);
    }else{
        $cy_json   = array();
    }
    $row['site_total']  = count(elementsToArray( $row['sites']));
    $row['status_text'] = get_cyd_status_text( $row['status']);
    if(!empty($cy_json['退回'])){//这里是被退回的采样单
        $cy_tuihui_num++;
        $row['status_text']     = '<font color=red>采样记录被退回</font>';
    }
    $m['js_cyrw'] = $row['cy_flag']
        ? '<a href="'.$rooturl.'/cy/cy_tzd.php?cyd_id='.$row['id'].'&action=cy" target="_blank">接受任务</a>'
        : '<a href="'.$rooturl.'/cy_task.php?action=手动下载&cyd_id='.$row['id'].'">接受任务</a>';
        #采样通知单
        $m['cy_tzd'] = $row['cy_flag']
            ? '<a href="'.$rooturl.'/cy/cy_tzd.php?cyd_id='.$row['id'].'&action=cy" >采样通知单</a>'
            : '<a href="'.$rooturl.'/cy/cy_tzd.php?cyd_id='.$row['id'].'" >采样通知单</a>';
        #采样记录
        $m['cy_rec'] = '<a href="'.$rooturl.'/cy/cy_record.php?cyd_id='.$row['id'].'&cy_date='.$row['cy_date'].'">采样记录</a>';
    $op = array();
    if($row['status']<2){
        $op[] = $m["js_cyrw"];
    }
    if($row['status']>1){
        $op[] = $m['cy_tzd'];
        $op[] = $m['cy_rec'];
    }
    $row['operation'] = implode( '|', $op );
    $result_cy[] = $row;
}
if($cy_tuihui_num>0){
    $cyrw_nums  = "共:".count($result_cy)."张&nbsp;<font color='red' style='font-weight:bold;'>被退回:".$cy_tuihui_num."张</font>";//采样任务数量
}else{
    $cyrw_nums  = count($result_cy);
}

$cy_rw_arr= array();
foreach ($result_cy as $key => $data) {
    //采样员一
    $cy_user = $data['cy_user'];
    //采样员二
    if(!empty($data['cy_user2'])){
        $cy_user2 = empty($cy_user) ? $data['cy_user2'] : ','.$data['cy_user2'];
    }
    $cy_rw_arr[$data['status']]    .= '<tr>
        <td>'.($key+1).'</td>
        <td>'.$data['cy_date'].'</td>
        <td>'.$data['cyd_bh'].'</td>
        <td>'.$data['group_name'].'</td>
        <td>'.$cy_user.$cy_user2.'</td>
        <td>'.$data['status_text'].'</td>
        <td>'.$data['operation'].'</td>
    </tr>';
    $cy_rw .= '<tr>
        <td>'.($key+1).'</td>
        <td>'.$data['cy_date'].'</td>
        <td>'.$data['cyd_bh'].'</td>
        <td>'.$data['group_name'].'</td>
        <td>'.$cy_user.$cy_user2.'</td>
        <td>'.$data['status_text'].'</td>
        <td>'.$data['operation'].'</td>
    </tr>';
}
ksort($cy_rw_arr);
$cy_rw  = implode(''   , $cy_rw_arr);
###################采样验收
if($u['ypjs']){
        $sumcy_ys   = $cy_tuihui_num = 0;
        $row    = array();
        $sql_ys = $DB->query("SELECT * FROM `cy` WHERE `fzx_id` = '{$fzx_id}' AND `cy_date` > '$last_date1' AND `cy_flag`='1' AND (`sh_user_qz`='' OR `sh_user_qz` is null) AND (`cy_user_qz`!='' OR `cy_user_qz2`!='') AND `cy_user`!='{$u['userid']}' AND `cy_user2`!='{$u['userid']}' ");
        while($rs_ys= $DB->fetch_assoc($sql_ys)){
            if($rs_ys['json']!=''){
                $cy_json   = json_decode($rs_ys['json'],true);
            }else{
                $cy_json   = array();
            }
            //序号
            $sumcy_ys++;
            //采样人
            $cy_user    = '';
            if(!empty($rs_ys['cy_user'])){
                $cy_user    .= $rs_ys['cy_user'];
            }
            if(!empty($rs_ys['cy_user2'])){
                $cy_user    .= "、".$rs_ys['cy_user2'];
            }
            //状态
            $rs_ys['status_text']   = get_cyd_status_text( $rs_ys['status']);
            if(!empty($cy_json['退回'])){//这里是被退回的采样单
                $cy_tuihui_num++;
                $rs_ys['status_text']     = '<font color=red>采样记录被退回</font>';
            }
            //操作
            $rs_ys['operation'] = '<a href="'.$rooturl.'/cy/cy_record.php?cyd_id='.$rs_ys['id'].'&cy_date='.$rs_ys['cy_date'].'">查看采样记录</a>';
            $cy_ys  .= "
            <tr align='center'>
                <td nowrap>$sumcy_ys</td>
                <td align='left'>$rs_ys[group_name]</td>
                <td nowrap>$rs_ys[cy_date]</td>
                <td nowrap>$cy_user</td>
                <td nowrap>$rs_ys[status_text]</td>
                <td align='center' nowrap>$rs_ys[operation]</td>
            </tr>";
        }
        $cy_tuihui_tishi    = $sumcy_ys;
        if($cy_tuihui_num>0){
            $cy_tuihui_tishi    = "共:".$sumcy_ys."张 <font color='red' style='font-weight:bold;'>被退回:".$cy_tuihui_num."张</font>";
        }
        $cy_ys  = '<div class="widget-box collapsed" style="border:none;">
                        <div class="widget-header header-color-blue zhedie" style="background:#4B9BCC;border-color:#D9EDF7;color: #FFFFFF !important;">
                            <h4>样品验收 （'.$cy_tuihui_tishi.'）</h4>
                            <span class="widget-toolbar">
                                <a data-action="collapse" href="#"><i class="1 icon-chevron-down bigger-125"></i></a>
                            </span>
                        </div>
                        <div class="widget-body" style="border:none;">
                            <table class="table table-striped table-bordered table-hover center">
                                <thead>
                                    <tr>
                                        <th class="center">序号</th>
                                        <th class="center">采样批次</th>
                                        <th class="center">采样日期</th>
                                        <th class="center">采样人</th>
                                        <th class="center">状态</th>
                                        <th class="center">操作/记录表</th>
                                    </tr>
                                </thead>
                                <tbody>'.$cy_ys.'</tbody>
                            </table>
                        </div>
                    </div>';
}
###################未完成的化验任务
//ap.`sign_01`!='{$u['userid']}' AND ap.`sign_012`!='{$u['userid']}' 即使这张化验单未完成，但是如果本人已经签字也不出现在个人任务列表里面
$sql = "SELECT ap.`id`, ap.`userid`, ap.`userid2`, ap.`vid`,ap.`assay_element`, ap.`over`,ap.`json`, cy.`cy_date`, cy.site_type,cy.`group_name`, cy.`jcwc_date`
        FROM `cy` LEFT JOIN `assay_pay` ap ON cy.`id`=ap.`cyd_id` LEFT JOIN `assay_value` av ON ap.`vid` = av.`id`
        WHERE cy.`cy_date` > '$last_date' AND ap.`fzx_id` = '$fzx_id' AND (ap.`userid` = '{$u['userid']}' OR ap.`userid2`='{$u['userid']}') AND (ap.`sign_01`!='{$u['userid']}' AND ap.`sign_012`!='{$u['userid']}') AND ap.is_xcjc='0' AND (`sign_02`='' OR `sign_02` IS NULL)
        ORDER BY month(cy.`jcwc_date`),cy.`xdcs_qz_date`,ap.`over`";//按照cy.cy_date排序 不要按照vid 排序
$res= $DB->query($sql);
$k  = $tuiHuiNum    = 0;
$result_hy1=$result_hy2=array();
while( $row=$DB->fetch_assoc($res)) {
     //委托任务不显示批名
    if($row['site_type']=='3'){
        $row['group_name'] = '委托任务（真实名称已隐藏）';
    }
    $row['create_date'] = substr( $row['create_date'], 0, 10 );
    //是否有平行样
    if($u['zhi_kong']) {
        $px = $DB->fetch_one_assoc("SELECT COUNT(`id`) FROM `assay_order` WHERE tid = {$row['id']} AND hy_flag IN (-6,-20,-26,-60,-66)" );
        $row["$px"] = ( $px ) ? '<span title="该张化验单有平行化验任务">*</span>' : '';
    }
    //本张化验单总任务数
    $r = $DB->fetch_one_assoc( "SELECT COUNT(`id`) AS total FROM `assay_order` WHERE `tid` = '{$row['id']}'" );
    $row['total'] = $r['total'];
    //本张化验单已完成的化验任务数
    $r = $DB->fetch_one_assoc("SELECT COUNT(`id`) AS total FROM `assay_order` WHERE `tid`='{$row['id']}' AND `vd0` != ''" );
    $row['already_completed'] = $r['total'];
    /*
    if($row['did'] != '-1') {
        if( $_SESSION['ry_type'][$row['vid']]=='自配溶液' ) {
            $sql = "SELECT `did`, `bzry_name`, `bzry_nongdu` AS CA, `bzry_bdrq` AS create_date, `fx_user` AS create_man
                FROM `bzry` WHERE `id` = '{$row['scid_or_bzryid']}'";
        }else{
            $sql = "SELECT `did`, `td31` AS bzry_name, `userid` AS create_man, `td4` AS create_date, `td19` AS CA,`td20` AS CB
                FROM `standard_curve` WHERE `id` = '{$row['scid_or_bzryid']}'";
        }
        $p = $DB->fetch_one_assoc( $sql );
        //未签字化验单本人可以修改公式参数, 超级用户 admin 随时都可以修改化验参数
        if(($u['userid']==$row['userid'] && !$row['sign_01']) || $u['userid']=='admin') {
            $row['parameter'] = ( $p )
                ? '<a href="'.$rooturl.'/assay_gongshi_data.php?hyd_id='.$row['id'].'&did='.$row['did'].'&vid='.$row['vid'].'&scid_or_bzryid='.$row['scid_or_bzryid'].'&action=选择参数">'.$p['bzry_name'].'<br />'.$p['create_date'].'</a>'
                : '<a href="'.$rooturl.'/assay_gongshi_data.php?hyd_id='.$row['id'].'&did='.$row['did'].'&vid='.$row['vid'].'&action=选择参数">-</a>';
        }else {
            $row['parameter'] = ($p)
                ? $p['bzry_name'].'<br />'.$p['create_date']
                : '-';
        }
        $row['title'] = '参数a: '.$row['CA'].' b: '.$row['CB'].' 创建人: '.$p['create_man'];
    }else{
        $row['parameter'] = '-';
    }*/
    //公式参数 ，标准曲线信息或者标液标定信息
    $row['parameter'] = '-';
    if(!in_array($row['id'],$can_click_vid)){
        $row['assay_form_url'] = '<a href="'.$rooturl.'/huayan/assay_form.php?tid='.$row['id'].'" >';
    }else{
        $row['assay_form_url'] = '<a href="'.$rooturl.'/huayan/assay_form.php?tid='.$row['id'].'" >';
    }
    //化验单是否是被退回的 如果是 突出显示
    $tuiHuiZt = "";
    if($row['json']!=''){
        $payJson = json_decode($row['json'],true);
        if($payJson['退回']!=''){
                $tuiHuiZt = "<font color=red style='font-weight:bold;'>(被退回)</font>";
        }
    }
    if(!empty($tuiHuiZt)){
        $row['over'] = $tuiHuiZt;
        $tuiHuiNum++;
    }
    if($u['userid']==$row['userid']){
        $result_hy1[$k]=$row;
    }else{
        $result_hy2[$k]=$row;
    }
    $k++;
}
$result_hy=array_merge($result_hy1,$result_hy2);
if(empty($result_hy1) && empty($result_hy2)){
    $hyrw_nums= 0;//化验任务数量
}else{
    $tuiHuiStr  = '';
    if($tuiHuiNum>0){
        $tuiHuiStr  = "&nbsp;<font color='red' style='font-weight:bold;'>被退回:".$tuiHuiNum."项</font>";
    }
    $hyrw_nums="主测:".count($result_hy1)."项&nbsp;辅测:".count($result_hy2)."项".$tuiHuiStr;//化验任务数量
}
if(count($result_hy1)>0 || count($result_hy2)>0){
    $main_user  = "<a href='$rooturl/main_user.php' target='_blank'>查看个人任务汇总表</a>";
}else{
    $main_user  = "<a href='$rooturl/main_user.php' target='_blank'>查看个人任务汇总表</a>";//做完任务后，统计数据时还想看一下,临时就加上了
}
$hy_rw  = $hy_rw_user2  = '';
foreach ($result_hy as $key => $data) {
     //委托任务不显示批名
    if($data['site_type']=='3'){
        $data['group_name'] = '委托任务（真实名称已隐藏）';
    }
    //规定项目之外的项目临时不允许选择
    if(!in_array($data['vid'],$can_click_vid)){
        $click_str  = "{$data['assay_form_url']}{$data['assay_element']}</a>";
    }else{
        $click_str  = "<font style='color:#A6A6A6;cursor:pointer;' onclick=\"alert('{$data['assay_element']}的化验单正在开发中，还不能查看.');\">{$data['assay_element']}</font>";
    }
    $der = empty($data['userid2']) ? '' : '、'.$data['userid2'];
    if($u['userid']==$data['userid']){
       $hy_rw.= '<tr>
          <td>'.($key+1).'</td>
          <td>'.$click_str.'('.$data['already_completed'].'/'.$data['total'].')</td>
          <td>'.$data['jcwc_date'].'</td>
          <td>'.$data['assay_form_url'].$data['id'].'</a></td>
          <td>'.$data['group_name'].'</td>
          <td>'.$data['cy_date'].'</td>
          <td>'.$data['parameter'].'</td>
          <td>'.$data['over'].'</td>
       </tr>';
    }else{
        $hy_rw_user2    .= '<tr>
          <td>'.($key+1).'</td>
          <td>'.$click_str.'('.$data['already_completed'].'/'.$data['total'].')</td>
          <td>'.$data['jcwc_date'].'</td>
          <td>'.$data['assay_form_url'].$data['id'].'</a></td>
          <td>'.$data['group_name'].'</td>
          <td>'.$data['cy_date'].'</td>
          <td>'.$data['parameter'].'</td>
          <td>'.$data['over'].'</td>
       </tr>';
    }
}
if(!empty($hy_rw_user2)){
    $hy_rw  .= "<tr><td colspan='8' style='font-weight:bold;'>以下为辅测项目</td></tr>".$hy_rw_user2;
}

$us=$DB->fetch_one_assoc("SELECT id FROM users WHERE id='$u[id]'");
if($us[id])
{
    $ur=$DB->fetch_one_assoc("SELECT * FROM user_other WHERE uid='$us[id]'");
    if($ur[uid]<1)//说明这个用户第一次 使用这个功能 这个表里没有他的记录 就新建一个记录
        $DB->query("INSERT INTO `user_other` set uid='$us[id]'");

    $ur=$DB->fetch_one_assoc("select * from user_other where uid='$us[id]'");
    if(strlen($ur[v1])<1)
        $uv1='0';
    else
        $uv1=$ur[v1];
    if(strlen($ur[v2])<1)
        $uv2='0';
    else
        $uv2=$ur[v2];
        $tv1=" and vid in('$uv1')";
        $tv2=" and vid in('$uv2')";
    //if(strlen($ur[v1])<1  && strlen($ur[v2])<1) //说明本人没有进行任何设置
        //echo "<h1>你没有设置任何 校核与复核任务 <a  href='$rooturl/user/user_other.php' title=\"点击设置\">点击</a> 此处进行设置</h1>";
}

#####################未完成的校核任务

//获得标准曲线校核任务
if($u['jh']){
    $sql="SELECT * FROM user_other uo JOIN users u on u.id=uo.uid WHERE u.id='".$u['id']."'";
    $rs=$DB->fetch_one_assoc($sql);
    //print_rr($rs[v1]);
    if(!empty($rs['v1'])){
        if(strpos($rs['v1'],"','")){
            $vids_v1_str=" in ('".$rs['v1']."')";
        }else{
            $vids_v1_str=" = '".$rs['v1']."'";
        }
        $sql_jh="SELECT id,td31,sign_01,assay_element,status FROM standard_curve WHERE fzx_id='$fzx_id' AND sign_02='' AND sign_01!='' AND sign_01!='{$u['userid']}' AND vid ".$vids_v1_str."";
        $query_jh=$DB->query($sql_jh);
        $qx_xh_line='';
        $qx_xh_sum=0;
        while($rs_jh=$DB->fetch_assoc($query_jh)){
            if($rs_jh['status'] == '曲线被退回'){
                $qx_jh_status   = "<font color='red'>{$rs_jh['status']}</font>";
            }else{
                $qx_jh_status   = $rs_jh['status'];
            }
            $qx_xh_sum++;
             $qx_xh_line.="<tr align=center>
                            <td>".$qx_xh_sum."</td>
                            <td><a href='$rooturl/huayan/ahlims.php?app=quxian&act=index&id=$rs_jh[id]'>".$rs_jh['assay_element']."</a></td>
                            <td>".$rs_jh['sign_01']."</td>
                            <td>".$u['userid']."</td>
                            <td>".$rs_jh['td31']."</td>
                            <td>$qx_jh_status</td>
                          </tr>";
        }
    }
}




//根据完成日期排序，后面序号用到
//校核任务 查询的sql 语句
$xhsql  = "SELECT SUM(a.`over`='已完成') AS finish_over,SUM(a.`over` in ('未开始','已开始')) AS other_over,SUM(a.`json` LIKE '%退回%' AND a.`over`='已完成') AS finish_tuihui ,SUM(a.`json` LIKE '%退回%') AS all_tuihui,MIN(c.`jcwc_date`) AS jcwc_date,MIN(a.id) AS id,a.vid,a.assay_element from assay_pay as a left join cy as c on a.cyd_id=c.id where a.fzx_id='$fzx_id' AND c.status>='6' AND a.`is_xcjc`='0' AND c.cy_date > '$last_date' and a.over in ('未开始','已开始','已完成')  $tv1 GROUP BY a.`vid` ORDER BY min(c.`jcwc_date`),a.`vid`";
$xhsj   = $DB->query($xhsql);
$xhsum  = 0;
$xhsum_total = 0;//可以校核的任务总数
while( $rxh = $DB->fetch_assoc( $xhsj ) ){
	$xhsum++;
    $xhsum_total += $rxh['finish_over'];
	//突出显示未完成的进度栏
    $all_num    = $rxh['finish_over']+$rxh['other_over'];
	$jindu_bianse= '';
	if($all_num != $rxh['finish_over']){
	    $jindu_bianse   = " style='color:red;' ";
	}
	//突出显示被退回的单元格
	$tuihui_bianse  = '';
	$tuihui_jindu   = 0;
	if($rxh['all_tuihui'] > 0){
	    $tuihui_bianse  = " style='color:red;' ";
	    $tuihui_jindu   = "{$rxh['finish_tuihui']}/{$rxh['all_tuihui']}";
	}
	//判断是否有未完成的化验单
	if($rxh['other_over'] > 0){
	    $no_finish_a    = "<a href='$rooturl/huayan/ahlims.php?app=pay_list&year=".date('Y')."&month=全部&uid=全部&vid={$rxh['vid']}&status=未完成' >查看未完成的化验单</a>";
	}else{
	    $no_finish_a    = "<span style='color:#9DAEBF;' onclick=\"alert('化验单均已完成');\">查看未完成的化验单</span>";
	}
	if($rxh['finish_over'] > 0){
	    $finsh_a    = " <a href='$rooturl/huayan/assay_form.php?qz=jh&tid={$rxh['id']}'>校核已完成的化验单</a>";
	}else{
	    $finsh_a    = " <span style='color:#9DAEBF;' onclick=\"alert('没有已完成的化验单');\">校核已完成的化验单</span>";
	}
	$mian_xh    .= "<tr>
	    <td>{$xhsum}</td>
	    <td>{$rxh['assay_element']}</td>
	    <td $jindu_bianse title='已完成的化验单/全部的化验单'>{$rxh['finish_over']}/{$all_num}</td>
	    <td>{$rxh['jcwc_date']}</td>
	    <td$tuihui_bianse>$tuihui_jindu</td>
	    <td>$no_finish_a $finsh_a</td>
	</tr>";
}
$xhsum	= $xhsum+$qx_xh_sum;

##########################未完成的复核任务
//获得标准曲线复核任务
if($u['fh']){
    $sql="SELECT * FROM user_other uo JOIN users u ON u.id=uo.uid WHERE u.id='".$u['id']."'";
    $rs=$DB->fetch_one_assoc($sql);
    if(!empty($rs['v2'])){
        if(strpos($rs['v2'],"','")){
           $vids_v2_str=" in ('".$rs['v2']."')";
        }else{
           $vids_v2_str=" = '".$rs['v2']."'";
        }
        $sql_fh="SELECT id,td31,sign_01,sign_02,sign_date_01,sign_date_02,assay_element,status FROM standard_curve WHERE fzx_id='$fzx_id' AND sign_03='' AND sign_02!='' AND vid ".$vids_v2_str."";
        $query_fh=$DB->query($sql_fh);
        $qx_fh_line='';
        $qx_fh_sum=0;
        while($rs_fh=$DB->fetch_assoc($query_fh)){
            if($rs_fh['status'] == '曲线被退回'){
                $qx_fh_status   = "<font color='red'>{$rs_fh['status']}</font>";
            }else{
                $qx_fh_status   = $rs_fh['status'];
            }
            $qx_fh_sum++;
             $qx_fh_line.="<tr align=center>
                            <td>".$qx_fh_sum."</td>
                            <td><a href='$rooturl/huayan/ahlims.php?app=quxian&act=index&id=$rs_fh[id]'>".$rs_fh['assay_element']."</a></td>
                            <td>".$rs_fh['sign_01']."</td>
                            <td>".$rs_fh['sign_date_01']."</td>
                            <td>".$rs_fh['sign_02']."</td>
                            <td>".$rs_fh['sign_date_02']."</td>
                            <td>".$rs_fh['td31']."</td>
                            <td>".$u['userid']."</td>
                            <td>$qx_fh_status</td>
                          </tr>";
        }
    }
}


//复核 任务 查询的sql 语句
$fusql	= "SELECT SUM(a.`over`='已校核') AS finish_over,SUM(a.`over`='已完成') AS other_over,SUM(a.`json` LIKE '%退回%' AND a.`over`='已校核') AS finish_tuihui ,SUM(a.`json` LIKE '%退回%') AS all_tuihui,MIN(c.`jcwc_date`) AS jcwc_date,MIN(a.id) AS id,a.vid,a.assay_element from assay_pay as a left join cy as c on a.cyd_id=c.id where a.fzx_id='$fzx_id' AND c.cy_date > '$last_date' AND a.`is_xcjc`='0' and a.over in ('已完成','已校核')  $tv2 GROUP BY a.`vid` ORDER BY MIN(c.`jcwc_date`),a.vid";
$fhsj	= $DB->query($fusql);
$fhsum	= 0;
$fhsum_total = 0;//可以复核的任务总数
while( $rfh = $DB->fetch_assoc( $fhsj ) ){
	$fhsum++;
	$all_num     =  $rfh['finish_over'] + $rfh['other_over'];
    $fhsum_total += $rfh['finish_over'];
	//突出显示未完成的进度栏
	$jindu_bianse= '';
	if($all_num != $rfh['finish_over']){
	    $jindu_bianse   = " style='color:red;' ";
	}
	//突出显示被退回的单元格
	$tuihui_bianse  = '';
	$tuihui_jindu   = 0;
	if($rfh['all_tuihui'] > 0){
	    $tuihui_bianse  = " style='color:red;' ";
	    $tuihui_jindu   = "{$rfh['finish_tuihui']}/{$rfh['all_tuihui']}";
	}
	//判断是否有未完成的化验单
	if($rfh['other_over'] > 0){
	    $no_finish_a    = "<a href='$rooturl/huayan/ahlims.php?app=pay_list&year=".date('Y')."&month=全部&uid=全部&vid={$rfh['vid']}&status=已完成' >查看未校核的化验单</a>";
	}else{
	    $no_finish_a    = "<span style='color:#9DAEBF;' onclick=\"alert('化验单均已校核');\">查看未校核的化验单</span>";
	}
	if($rfh['finish_over'] > 0){
	    $finsh_a    = " <a href='$rooturl/huayan/assay_form.php?qz=fh&tid={$rfh['id']}'>去复核化验单</a>";
	}else{
	    $finsh_a    = " <span style='color:#9DAEBF;' onclick=\"alert('没有已校核的化验单');\">去复核化验单</span>";
	}
	$main_fh    .= "<tr>
	    <td>{$fhsum}</td>
	    <td>{$rfh['assay_element']}</td>
	    <td $jindu_bianse title='已校核的化验单/已检测完成的化验单'>{$rfh['finish_over']}/{$all_num}</td>
	    <td>{$rfh['jcwc_date']}</td>
	    <td$tuihui_bianse>$tuihui_jindu</td>
	    <td>$no_finish_a  &nbsp;$finsh_a</td>
	</tr>";
}
$fhsum	= $fhsum+$qx_fh_sum;
//仪器检定的查询语句

if($u['yiqi_manage']=='1'){//登陆及权限判断
    $yqsql ="select * from `yiqi` where `fzx_id`='1' and `yq_jiliang`!='非计量器具' and `yq_jiandingriqi`!='' order by yq_sbbianhao asc,yq_mingcheng asc";//内部编号排序
     $R=$DB->query($yqsql);
       $mc=$a;
       $bgr=$d;
      $i=$j= 1;
      $geshu=0;
      $mingcheng=$x='';
			$url_content='main';
			$sum=1;
    while($r=$DB->fetch_assoc($R)){
      $operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!'))
        location='./yiqi/delete.php?action=删除&yid=$r[id]'\">删除</a>
        |<a href='./yiqi/yiqi_update.php?action=修改&yid=$r[id]&page=$_GET[page]'>修改</a>";
        //    if($r['yq_state']=='启用') $color='green';
          //  elseif($r['yq_state']=='准用') $color='blue';
                //          else $color='red';
        if($r['yq_firstjianding']!='' && $r['yq_tixingriqi']!=''){
                $rq = $r['yq_firstjianding'];
                $strx = '+'.$r['yq_tixingriqi'].' days';
                $c1 =  strtotime($strx);
                $date = date("Y-m-d",$c1);
                $c2 = strtotime($date);
                if(strpos($rq,'-')!==false)
                        $rqarr = explode('-',$rq);
                if(strpos($rq,'.')!==false)
                        $rqarr = explode('.',$rq);
                if(strpos($rq,'/')!==false)
                        $rqarr = explode('/',$rq);
                $today0 = time();
                $today1 = date("Y-m-d",$today0);
                $today2 = strtotime($today);
                //下次检定的日期
                $time2 = strtotime($r['yq_firstjianding']);
                if($c2 >= $time2 && $today2 <= $time2 && $r['yq_state']!='报废'){
                        $color='red';
                }else{
                        $color='';
                }
        }else{
                        $color='';
                }

        //$xh = ($_GET['page']-1) * $pagesize;
        $xx=$sum;
        $mingcheng = $r['yq_mingcheng'];
        //下次检定日期
        $xcjd_riqi = '';
        if(!empty($r['yq_jiandingriqi']) && !empty($r['yq_jdriqi']))
            $xcjd_riqi = date('Y-m',strtotime($r['yq_jiandingriqi']) + intval($r['yq_jdriqi']) * 31 * 24 *3600);
        else
            $xcjd_riqi = '';
        if($color == 'red'){
             $lines.=temp('hn_yiqimanager_line.html');
             $geshu++;
						 $sum++;
        }

    }
    $htmlstr = "<div class=\"widget-box collapsed\" style=\"border:none;\"><div class=\"widget-header header-color-blue zhedie\" ><h4>本月需要检定的仪器($geshu)</h4><span class=\"widget-toolbar\"><a data-action=\"collapse\" href=\"#\"><i class=\"icon-chevron-down bigger-125\"></i></a>
    </span>
  </div><div class=\"widget-body\" style=\"border:none;\">
    <table class=\"table table-striped table-bordered table-hover center\">
        <thead>
            <tr align=center >
                    <th>序号</th>
                    <th>仪器名称</th>
                    <th>型号</th>
                    <th>设备原值（元）</th>
                    <th>资产编号</th>
                    <th>出厂编号</th>
                    <th>制造厂家</th>
                    <th>状态</th>
                    <th>购置日期</th>
                    <th>下次检定日期</th>
                    <th>设备存放地点</th>
                    <th>档案位置</th>
                    <th colspan=2>操作</th>
            </tr>
        </thead>

        <tbody>$lines</tbody>
    </table>
  </div>
</div>";
}
disp('main');
?>
