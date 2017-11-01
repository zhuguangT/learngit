<?php
/**
 * 功能：质控月报列表
 * 作者：Mr Zhou
 * 日期：2014-06-20
 * 描述：质控月报列表
*/
include('../temp/config.php');
$fzx_id = FZX_ID;
/************************/
if($u['is_zz']&&intval($_GET['fzx_id'])){
    $fzx_id = intval($_GET['fzx_id']);
}
/***********************/
//if($u['admin']){error_reporting(E_ALL);ini_set('display_errors', '1');}
//if(!$u['zhi_kong']){prompt('你没有查看室内质控月报的权限！');gotourl($rooturl.'/zkb/zkyb_list.php');}
//去除空格
foreach ($_GET as $key => $value) {
    $_GET[$key] = trim($value);
}
$sql = "SELECT `id`,`value_C` FROM `assay_value` WHERE 1";
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
    $assayvalueC[$row['id']]=$row['value_C'];
}
//年份 月份
$month = date('m',strtotime($_GET['date']));
$_GET['year'] = $year = date('Y',strtotime($_GET['date']));
//SQL条件
$sql_where  = ' AND `cy`.fzx_id = '.$fzx_id;
$i=$line_no=0;
$lines_per_page=15;
switch($_GET['type']){
case 'xczk':
    $_file='zkb/zkyb/xcjl_zkyb';
    $lines_per_page=10;//如果不需要分页则设置为0;
    //准备表体数据
    //采集总数
    $sql = "SELECT `vid`,COUNT(`assay_order`.`id`) AS count
                FROM `assay_order` 
                RIGHT JOIN `cy_rec` ON `cy_rec`.`id` = `assay_order`.`cid` 
                LEFT JOIN `cy` ON `cy_rec`.`cyd_id` = `cy`.`id`
                LEFT JOIN `assay_value` av ON av.id=`assay_order`.`vid`
                WHERE `cy`.`cy_date` LIKE '$date%'
                    $sql_where
                    AND av.`is_xcjc`='0'
                    AND assay_order.`sid`>=0
                    AND assay_order.`hy_flag`>='0'
                GROUP BY `vid`";
    $query = $DB->query($sql);
    while ($row=$DB->fetch_assoc($query)) {
        $cj_arr[$row['vid']] = $row['count'];
    }
    //全程空白 现场平行
    $data=get_kb_px_data($_GET['date'],$sql_where);
    //总行数
    $total_lines= count($data);
    //总页数
    $totalpage  = ($lines_per_page==0) ? 1:ceil($total_lines/$lines_per_page);
    $page=1;//初始化页数
    $xcpx_hgs=$qckb_hgs=0;//合格数
    $xcpx_vids=$qckb_vids=array();//
    $lines = '';
    $i=$xcpxs=$qckbs=0;
    //print_rr($data);
    foreach($data as $vid => $value){
        $i++;
        $px=array();
        if(isset($value['px'])){
            $xcpxs++;
            if($value['px']['ping_jia']=='合格') $xcpx_hgs++;
            $xcpx_vids[]=$value['px'];

            $px['count'] = $value['px']['count'];
            $px['kzl']  = _round($px['count']/$cj_arr[$vid]*100,2);
            $px['vid']  = $vid;
            $px['xm']  = $assayvalueC[$vid];
            $px['min']  = $value['px']['min'];
            $px['max']  = $value['px']['max'];
            $px['min']  = ($px['min']>0)?'±'.$px['min']:$px['min'];
            $px['max']  = ($px['max']>0)?'±'.$px['max']:$px['max'];
            $px['xdpc'] = ($px['min']==$px['max'])?$px['min']:$px['min'].'～'.$px['max'];
            $px['pj']   = $value['px']['ping_jia'];
        }
        $kb=array();
        if(isset($value['kb'])){ 
            $qckbs++;
            if($value['kb']['ping_jia']=='合格') $qckb_hgs++;
            $qckb_vids[]=$value['kb']['vid'];

            $kb['count'] = $value['kb']['count'];
            $kb['kzl']  = _round($kb['count']/$cj_arr[$vid]*100,2);
            $kb['vid']  = $assayvalueC[$vid];
            $kb['min']  = $value['kb']['min'];
            $kb['max']  = $value['kb']['max'];
            $kb['min']  = ($kb['min']>0)?'±'.$kb['min']:$kb['min'];
            $kb['max']  = ($kb['max']>0)?'±'.$kb['max']:$kb['max'];
            $kb['xdpc'] = ($kb['min']==$kb['max'])?$kb['min']:$kb['min'].'～'.$kb['max'];
            $kb['pj']   = $value['kb']['ping_jia'];
        }
        $cy_date = date('m',strtotime($value['cy_date'])).'月';
        $lines .= temp($_file.'_line');
        if($lines_per_page != 0){
            if($i % $lines_per_page==0 ){
                $bts .=temp($_file.'_bt');
                $bts .=temp($_file.'_bw');
                $lines='';
                $page++;
                if($i==$total_lines){
                    break;
                }
            }
        }
    }
    if(($i%$lines_per_page)>0&&$lines_per_page!=0){
        $n=$lines_per_page-$i % $lines_per_page;
        for($i=0;$i<$n;$i++){
            $excel_json[$page][] = array();
            $lines.=temp($_file.'_kbline');
        }
        $bts .=temp($_file.'_bt');
        $bts .=temp($_file.'_bw');
    }else if($lines_per_page==0){
        $bts .=temp($_file.'_bt');
        $bts .=temp($_file.'_bw');
    }
    //$excel_json = json_encode($excel_json);
    break;
case 'snzk':
    $_file='zkb/zkyb/sn_zkyb';
    //如果不需要分页则设置为0;
    $lines_per_page=13;
    $sql = "SELECT `id` FROM `cy` WHERE `cy_date` LIKE '{$_GET['date']}%' $sql_where";
    $query = $DB->query($sql);
    while($row = $DB->fetch_assoc($query)){
        $cyd_id[] = $row['id'];
    }
    $cyd_id = (count($cyd_id))?implode(',', $cyd_id):0;
    $where_cyd_id = ' AND `cy`.`id` IN('.$cyd_id.')';
    //找出所有质控项目
    $not_zk = empty($not_zk) ? 0 : $not_zk;
    //按cyd_id(批次)vid(项目)分组
    $sql    = "SELECT `vid`,`cyd_id` 
                FROM `assay_order` AS ao 
                LEFT JOIN `cy` ON  cy.`id` = ao.`cyd_id` 
                LEFT JOIN `assay_value` av ON ao.`vid` = av.`id`
                WHERE ao.`vid` NOT IN ( $not_zk ) 
                    AND av.`is_xcjc`='0' 
                    AND ao.`sid`>'-3'
                    AND (`hy_flag`<0 OR `hy_flag` IN(3,23,43,63))
                    AND `hy_flag` NOT IN('-4','-6','-7') $where_cyd_id
                    AND av.`is_xcjc`='0'
                GROUP BY ao.`vid`,`cyd_id`
                ORDER BY `vid`,`cyd_id`";
    $query  = $DB->query($sql);
    $total_lines = $DB->rows;
    //总页数
    $totalpage= ($lines_per_page==0) ? 1 : ceil($total_lines/$lines_per_page);
    $page=1;
    $vid_arr=array();
    while($row=$DB->fetch_assoc($query)){
        $vid_arr[$row['vid']][] = $row['cyd_id'];
    }
    /*/启用模板排序
    $model = "SELECT * FROM `n_set` WHERE `name` ='xm_px' AND `int3` = 1 limit 1";
     $m_row = $DB->fetch_one_assoc($model);
     if(!empty($m_row)){
        $json  = json_decode($m_row['json']);
        $xm_px = explode(',',$json->px);
        $data1 = array();
        foreach ($xm_px as $key => $value) {
            if(isset($data[$value])){
                $data1[$value] = $data[$value];
            }
        }
        $data = $data1;
        unset($data1);
    }*/
    $i=$line_no=0;
    foreach($vid_arr as $vid => $cyd_id_arr)
    {
        $line_no++;
        foreach ($cyd_id_arr as $key => $cyd_id) {
            $i++;
            $sql = "SELECT count(ao.`id`) AS `c` ,ap.`cyd_id`,ap.`td2`,ap.`userid`,ap.`time_01`,ap.`scid`,ap.`assay_element` value_C,ao.`tid`
                    FROM `assay_order` AS ao 
                    LEFT JOIN `assay_pay` AS ap ON  ap.id = ao.tid
                    WHERE ap.`cyd_id` = '$cyd_id' 
                    AND ap.`vid`= '$vid' 
                    AND `sid`>0 
                    AND `hy_flag`>=0";
            $r = $DB->fetch_one_assoc($sql);
            //室内空白
            $sql = "SELECT `vd0`,`_vd0`,`vd28`,`xiang_dui_pian_cha`,`ping_jia`,`ping_jun`
                FROM `assay_order` AS ao
                WHERE ao.`cyd_id` = $cyd_id
                AND ao.`vid` = '$vid'
                AND `hy_flag` = '-2' ORDER BY `bar_code`";
            $kb = array();
            $query = $DB->query($sql);
            if($DB->rows==2){
                $kb_arr[0] = $DB->fetch_assoc($query);
                $kb_arr[1] = $DB->fetch_assoc($query);
                if($kb_arr[0]['vd28']!=''&&$kb_arr[1]['vd28']!=''){
                    $vd0 = 'vd28';
                }else{
                    $vd0 = 'vd0';
                }
                $kb[1] = $kb_arr[0][$vd0];
                $kb[2] = $kb_arr[1][$vd0];
                $kb['avg']  = $kb_arr[1]['ping_jun'];
                $kb['jgpd'] = $kb_arr[1]['ping_jia'];
                $kb['xdpc'] = $kb_arr[1]['xiang_dui_pian_cha'];
            }else{
                $kb = array();
            }
            //曲线
            $sql = "SELECT `td21`,`CR`,`CA`,`CB` FROM `standard_curve` WHERE  id='{$r['scid']}'";
            $quxian=$DB->fetch_one_assoc($sql);
            $qx=array();
            if($quxian!=''){
                $qx['hgfc']='y='.$quxian['CB'].'x'.(($quxian['CA']>0)?'+'.$quxian['CA']:$quxian['CA']);
                $quxian_r = $quxian['CR'];
                $qx['r'] = $quxian_r >= 1 ? 0.999 : $quxian_r;
                //截距检验
                $buhege=strstr($quxian['td21'],'不合格');
                $hege= strstr($quxian['td21'],'合格')?strstr($quxian['td21'],'合格'):'';
                if($buhege!='')$hege=$buhege;
                $qx['hege']=$hege;
            }
            //室内平行
            $sql = "SELECT `tid`,`ping_jia`,
                        COUNT(`id`) AS count, 
                        MIN(ABS(`xiang_dui_pian_cha`)) AS min ,
                        MAX(ABS(`xiang_dui_pian_cha`)) AS max
                    FROM `assay_order`
                    WHERE `cyd_id` = $cyd_id
                    AND `vid` = $vid
                    AND `hy_flag` IN(-20,-60,-26,-66)
                    AND `sid`>0";
            $r1a=$DB->fetch_one_assoc($sql);
            $sql = "SELECT `ping_jia` FROM `assay_order` 
                    WHERE `cyd_id` = $cyd_id AND `vid` = $vid 
                        AND `hy_flag` IN(-20,-60,-26,-66) AND `sid`>0
                    GROUP BY `ping_jia`";
            $query = $DB->query($sql);
            $r1a['ping_jia'] = ($DB->rows>1)?'':$r1a['ping_jia'];
            if($r1a['count']){
                //平行样品测定率
                $r1a['cdl']  = _round($r1a['count']/$r['c']*100,2);
                $r1a['min']  = ($r1a['min']>0)?'±'.$r1a['min']:'0.00';
                $r1a['max']  = ($r1a['max']>0)?'±'.$r1a['max']:'0.00';
                $r1a['xdpc'] = ($r1a['count']==1)?$r1a['min']:$r1a['min'].'～'.$r1a['max'];
                //$r1a['count'] = "<a href='$rooturl/huayan/assay_form.php?tid=$r1a[tid]'>$r1a[count]</a>";
            }else{
                $r1a=array();
            }
            //加标回收
            $sql = "SELECT `tid`,
                        COUNT(ao.`id`) AS count, 
                        MIN(ABS(`xiang_dui_pian_cha`)) AS min ,
                        MAX(ABS(`xiang_dui_pian_cha`)) AS max,
                        `ping_jia`
                FROM assay_order AS ao LEFT JOIN cy ON ao.cyd_id = cy.id
                WHERE ao.cyd_id = $cyd_id
                AND ao.vid = $vid
                AND ao.hy_flag IN(-40,-60,-46,-66)
                AND ao.sid>0";
            $r2a=$DB->fetch_one_assoc($sql);
            $sql = "SELECT `ping_jia` FROM `assay_order` 
                    WHERE `cyd_id` = $cyd_id AND `vid` = $vid 
                        AND `hy_flag` IN(-40,-60,-46,-66) AND `sid`>0
                    GROUP BY `ping_jia`";
            $query = $DB->query($sql);
            $r2a['ping_jia'] = ($DB->rows>1)?'':$r2a['ping_jia'];
            if($r2a['count']){
                //加标的测定率

                $r2a['vd0_1']  = $r['vd0'];
                $r2a['vd0_2']  = $r2a['vd0'];
                //范围
                $r2a['xdpc'] = ($r2a['count']==1)?$r2a['min']:$r2a['min'].'～'.$r2a['max'];
            }else{
                $r2a['count']= $jbhs_kzl=$r2a['xdpc']=$r2a['ping_jia']='';
            }
            //标准样品
            $sql = "SELECT `tid`,
                        COUNT(ao.`id`) AS count, 
                        MIN(ABS(`xiang_dui_pian_cha`)) AS min,
                        MAX(ABS(`xiang_dui_pian_cha`)) AS max,
                        `ping_jia`
                    FROM assay_order AS ao 
                    LEFT JOIN cy ON ao.cyd_id = cy.id
                    WHERE ao.cyd_id = $cyd_id
                    AND ao.vid = $vid
                    AND ao.hy_flag IN (3,23,43,63)";
            $r3b=$DB->fetch_one_assoc($sql);
            $sql = "SELECT `ping_jia` FROM `assay_order` 
                    WHERE `cyd_id` = $cyd_id AND `vid` = $vid 
                        AND `hy_flag` IN(3,23,43,63) AND `sid`>0
                    GROUP BY `ping_jia`";
            $query = $DB->query($sql);
            $r3b['ping_jia'] = ($DB->rows>1)?'':$r3b['ping_jia'];
            if($r3b['count']){
                //标准样品测定率
                $bzyb_cdl=_round($r3b['count']/$r['c']*100,2);
                //范围
                $re_fw=($r3b['count']==1)?$r3b['min']:$r3b['min'].'～'.$r3b['max'];
                if($r3b['ping_jia']==''&&''!=$r3b['max']){
                    if($r3b['max']<= 5)
                        $r3b['ping_jia'] = '合格';
                    else $r3b['ping_jia'] = '不合格';
                }
            }else{
                $r3b['count']=$r3b['ping_jia']=$bzyb_cdl=$re_fw='';
            }

            $td1_2 = '';
            if($key == 0){
                $now_td_page = $page;
                if($lines_per_page!=0){
                    $yushu = $lines_per_page-($i % $lines_per_page)+1;
                    $yushu = ($yushu==($lines_per_page+1))?1:$yushu;
                    $rowspan=' rowspan="'.((count($cyd_id_arr)>$yushu)?$yushu:count($cyd_id_arr)).'"';
                }else{
                    $rowspan=' rowspan="'.(count($cyd_id_arr)).'"';
                }
                $td1_2 = '<td class="nohover">'.$line_no.'</td><td class="nohover" '.$rowspan.'>'.$r['value_C'].'</td>';
            }else if($now_td_page==$page){
                $td1_2 = '<td class="nohover"></td>';
            }else{
                $td1_2 = '<td></td><td></td>';
            }
            $lines.=temp($_file.'_line');
            //取消分页后不需要下面的代码
            if($lines_per_page!=0){
                if($i % $lines_per_page==0 ){
                    $bts .= temp($_file.'_bt');
                    $lines='';
                    $page++;
                    if($i==$total_lines){
                       break;
                    }
                }
            }
        }
    }
    //取消分页后不需要再使用空行补齐
    if(($i%$lines_per_page)>0&&$lines_per_page!=0){
        $n=$lines_per_page-$i % $lines_per_page;
        $i='　';
        for($j=0;$j<$n;$j++){
            $excel_json[$page][] = array();
            eval('$lines.="'.gettemplate($_file.'_kbline').'";');
        }
        $bts .= temp($_file.'_bt');
    }else if($lines_per_page==0){
        $bts .= temp($_file.'_bt');
    }
    break;
case 'snzk2':
    $_file='zkb/zkyb/sn_zkyb2';
    //如果不需要分页则设置为0;
    $lines_per_page=13;
    $sql = "SELECT `id` FROM `cy` WHERE `cy_date` LIKE '{$_GET['date']}%' $sql_where";
    $query = $DB->query($sql);
    while($row = $DB->fetch_assoc($query)){
        $cyd_id[] = $row['id'];
    }
    $cyd_id = (count($cyd_id))?implode(',', $cyd_id):0;
    $where_cyd_id = ' AND `cy`.`id` IN('.$cyd_id.')';
    //找出所有质控项目
    $not_zk = empty($not_zk) ? 0 : $not_zk;
    //按cyd_id(批次)vid(项目)分组
    $sql    = "SELECT `vid`,`cyd_id` 
                FROM `assay_order` AS ao 
                LEFT JOIN `cy` ON  cy.`id` = ao.`cyd_id` 
                LEFT JOIN `assay_value` av ON ao.`vid` = av.`id`
                WHERE ao.`vid` NOT IN ( $not_zk ) 
                    AND av.`is_xcjc`='0' 
                    AND ao.`sid`>'-3'
                    AND (`hy_flag`<0 OR `hy_flag` IN(3,23,43,63))
                    AND `hy_flag` NOT IN('-4','-6','-7') $where_cyd_id
                    AND av.`is_xcjc`='0'
                GROUP BY ao.`vid`,`cyd_id`
                ORDER BY `vid`,`cyd_id`";
    $query  = $DB->query($sql);
    $total_lines = $DB->rows;
    //总页数
    $totalpage= ($lines_per_page==0) ? 1 : ceil($total_lines/$lines_per_page);
    $page=1;
    $vid_arr=array();
    while($row=$DB->fetch_assoc($query)){
        $vid_arr[$row['vid']][] = $row['cyd_id'];
    }
    /*/启用模板排序
    $model = "SELECT * FROM `n_set` WHERE `name` ='xm_px' AND `int3` = 1 limit 1";
     $m_row = $DB->fetch_one_assoc($model);
     if(!empty($m_row)){
        $json  = json_decode($m_row['json']);
        $xm_px = explode(',',$json->px);
        $data1 = array();
        foreach ($xm_px as $key => $value) {
            if(isset($data[$value])){
                $data1[$value] = $data[$value];
            }
        }
        $data = $data1;
        unset($data1);
    }*/

    $i=$line_no=0;
    foreach($vid_arr as $vid => $cyd_id_arr)
    {
        $line_no++;
        foreach ($cyd_id_arr as $key => $cyd_id) {
            $i++;
            $sql = "SELECT count(ao.`id`) AS `c` ,ap.`cyd_id`,ap.`td2`,ap.`scid`,ap.`userid`,ap.`time_01`,ap.`scid`,ap.`assay_element` value_C,ao.`tid`,ao.`bar_code`,ao.`vd0`
                    FROM `assay_order` AS ao 
                    LEFT JOIN `assay_pay` AS ap ON  ap.id = ao.tid
                    WHERE ap.`cyd_id` = '$cyd_id' 
                    AND ap.`vid`= '$vid' 
                    AND `sid`>0
                    AND `hy_flag`>=0";
            $r = $DB->fetch_one_assoc($sql);
                //曲线
            $sql = "SELECT `td21`,`CR`,`CA`,`CB` FROM `standard_curve` WHERE  id='{$r['scid']}'";
            $quxian=$DB->fetch_one_assoc($sql);
            $qx=array();
            if($quxian!=''){
                $qx['hgfc']='y='.$quxian['CB'].'x'.(($quxian['CA']>0)?'+'.$quxian['CA']:$quxian['CA']);
                $quxian_r = $quxian['CR'];
                $qx['r'] = $quxian_r >= 1 ? 0.999 : $quxian_r;
                //截距检验
                $buhege=strstr($quxian['td21'],'不合格');
                $hege= strstr($quxian['td21'],'合格')?strstr($quxian['td21'],'合格'):'';
                if($buhege!='')$hege=$buhege;
                $qx['hege']=$hege;
            }

            //室内平行
            $sql = "SELECT `tid`,`vd0`,`xiang_dui_pian_cha`
                    FROM `assay_order`
                    WHERE `cyd_id` = $cyd_id
                    AND `vid` = $vid
                    AND `hy_flag` IN(-20,-60,-26,-66)
                    AND `sid`>0";
            $r1a=$DB->fetch_one_assoc($sql);
            if($r1a['tid']){
                //平行样品测定率
                $r1a['vd0_1']  = $r['vd0'];
                $r1a['vd0_2']  = $r1a['vd0'];
                $r1a['xdpc']   = $r1a['xiang_dui_pian_cha'];
                //$r1a['count'] = "<a href='$rooturl/huayan/assay_form.php?tid=$r1a[tid]'>$r1a[count]</a>";
            }else{
                $r1a=array();
            }
            //加标回收
            $sql = "SELECT `tid`,`vd0`,`vd30`,`ping_jun`,`xiang_dui_pian_cha`
                FROM assay_order AS ao LEFT JOIN cy ON ao.cyd_id = cy.id
                WHERE ao.cyd_id = $cyd_id
                AND ao.vid = $vid
                AND ao.hy_flag IN(-40,-60,-46,-66)
                AND ao.sid>0";
            $r2a=$DB->fetch_one_assoc($sql);
            if($r2a['xiang_dui_pian_cha']){
                if(in_array($vid, array(723,725,605,685,683,687)))
                {
                    $r2a['vd0_1']  = $r2a['ping_jun'];
                    $r2a['vd0_2']  = 0;
                    $r2a['cha']    = $r2a['vd0_1']-$r2a['vd0_2'];
                    $r2a['m']      = $r2a['vd30'];
                    $r2a['xdpc']   = $r2a['xiang_dui_pian_cha'];
                }
                else
                {
                    $r2a['vd0_1']  = $r2a['vd0'];
                    $r2a['vd0_2']  = $r['vd0'];
                    $r2a['cha']    = $r2a['vd0_1']-$r2a['vd0_2'];
                    $r2a['m']      = $r2a['vd30'];
                    $r2a['xdpc']   = $r2a['xiang_dui_pian_cha'];
                }
            }
            else
            {
                $r2a['ping_jia']=$r2a['cdl']=$r2a['vd0_1']=$r2a['vd0_2']=$r2a['cha']=$r2a['m']=$r2a['xdpc']="";
            }
            $td1_2 = '';
            $td1_2 = '<td class="nohover">'.$line_no.'</td><td class="nohover" '.$rowspan.'>'.$r['bar_code'].'</td><td class="nohover" '.$rowspan.'>'.$r['value_C'].'</td>';
            $lines.=temp($_file.'_line');
            //取消分页后不需要下面的代码
            if($lines_per_page!=0){
                if($i % $lines_per_page==0 ){
                    $bts .= temp($_file.'_bt');
                    $lines='';
                    $page++;
                    if($i==$total_lines){
                       break;
                    }
                }
            }
        }
    }
    //取消分页后不需要再使用空行补齐
    if(($i%$lines_per_page)>0&&$lines_per_page!=0){
        $n=$lines_per_page-$i % $lines_per_page;
        $i='　';
        for($j=0;$j<$n;$j++){
            $excel_json[$page][] = array();
            eval('$lines.="'.gettemplate($_file.'_kbline').'";');
        }
        $bts .= temp($_file.'_bt');
    }else if($lines_per_page==0){
        $bts .= temp($_file.'_bt');
    }
    break;
case 'mykh':
    $lines_per_page=20;
    $_file='zkb/zkyb/by_zkyb';
    $sql = "SELECT rec.id,rec.by_id,rec.cyd_id,rec.create_date,rec.bar_code,
            ao.id, ao.vid, ao.tid, ao.vd0, ao.ping_jia,
            ap.userid, ap.time_01 AS fx_date,
            b.wz_bh,consistence,eligible_bound
        FROM `assay_order` AS `ao`
        LEFT JOIN `cy_rec` AS `rec` ON ao.`cid`=rec.`id` 
        LEFT JOIN `cy` ON rec.`cyd_id`=cy.`id`
        LEFT JOIN `assay_pay` ap ON ao.`tid`=ap.`id`
        LEFT JOIN `bzwz` AS b ON rec.`by_id`=b.`id`
        LEFT JOIN `bzwz_detail` bd ON rec.`by_id`=bd.`wz_id` AND ao.`vid`=bd.`vid`
        WHERE ao.`hy_flag` IN (3,23,43,63)
        $sql_where
        ORDER BY rec.`id`";
    $page_calc=$DB->query($sql);
    $total_lines=$DB->rows;
    if($total_lines<=0){
        gotourl($url[$_u_][1],'没有找到盲样考核的数据');
    }
    $totalpage=ceil($total_lines / $lines_per_page);
    $line_num=0;
    $page=1;
    while($row=$DB->fetch_assoc($page_calc)){
        $line_num++;
        $lines .= temp($_file.'_line');
        if($lines_per_page != 0){
            if($line_num % $lines_per_page==0 ){
                $bts .=temp($_file.'_bt');
                $lines='';
                $page++;
                if($line_num==$total_lines){
                    break;
                }
            }
        }
    }
    if(($line_num%$lines_per_page)>0&&$lines_per_page!=0){
        $n=$lines_per_page-$line_num % $lines_per_page;
        $line_num = '&nbsp;';
        for($i=0;$i<$n;$i++){
            $excel_json[$page][] = array();
            $lines.=temp($_file.'_line');
        }
        $bts .=temp($_file.'_bt');
    }else if($lines_per_page==0){
        $bts .=temp($_file.'_bt');
    }
    break;
}
disp($_file);

/*
* 根据给定的采样单编号及化验任务标志得到 控制项目名称 不合格项目名称 及合格率
*/
function get_data($cyd_id,$hy_flag){
    global $DB;
    $hy_flag=implode(',',$hy_flag);
    $huayan_renwu=$DB->query("SELECT * FROM `assay_order` WHERE `cyd_id`=$cyd_id AND `hy_flag`=$hy_flag order by `vid`");
    $total=$bhg=0;
    $vid=$bhg_vid='';
    $vids=$bhg_vids=array();
    while($x=$DB->fetch_assoc($huayan_renwu)){
        if($vid!=$x['vid']){
            $vid=$x['vid'];
            $vids[]=$x['vid'];
        }
        $total++;
        if($x['ping_jia']=='不合格'){
            if($bhg_vid!=$x['vid']){
                $bhg_vid=$x['vid'];
                $bhg_vids[]=$x['vid'];
            }
            $bhg++;
        }
    }
    $kzxm=($vids) ? get_c_items($vids) : '-';
    if(!$bhg_vids){
        $bhgxm='-';
        $hgl=100;
    }
    else{
        $bhgxm=($bhg_vids) ? get_c_items($bhg_vids) : '-';
        $hgl=round(($total-$bhg)/$total*100,1);
    }
    return array('控制项目'=>$kzxm,'不合格项目'=>$bhgxm,'合格率'=>$hgl);
}

/*得到全程序空白(1)现场平行(-6)的表体数据*/
function get_kb_px_data($date,$sql_where){
    global $DB;
    $data=array();
    //现场平行
    $sql = "SELECT `cy_date`,`vid`,`assay_order`.`hy_flag`,`ping_jia`,
                COUNT(`assay_order`.`id`) AS count, 
                MIN(ABS(`xiang_dui_pian_cha`)) AS min ,
                MAX(ABS(`xiang_dui_pian_cha`)) AS max
                FROM `assay_order` 
                LEFT JOIN `cy` ON `assay_order`.`cyd_id` = `cy`.`id`
                LEFT JOIN `assay_value` av ON av.id=`assay_order`.`vid`
                WHERE `cy`.`cy_date` LIKE '$date%'
                    $sql_where
                    AND av.`is_xcjc`='0'
                    AND `assay_order`.`hy_flag` = '-6' 
                GROUP BY `vid`,`ping_jia`";
    $query=$DB->query($sql);
    while($row=$DB->fetch_assoc($query)){
        $r = $data[$row['vid']]['px'];
        if($r['count']!=''&&$r['ping_jia']!=$row['ping_jia']&&$row['ping_jia']!='不合格'){
            $r['ping_jia']  = '';
        }else{
            $r['ping_jia']  = $row['ping_jia'];
        }
        $r['min']       = (''!=$r['min']&&$r['min']<$row['min'])?$r['min']:$row['min'];
        $r['max']       = (''!=$r['max']&&$r['max']>$row['max'])?$r['max']:$row['max'];
        $r['count']     = $row['count']+intval($r['count']);
        $r['cy_date']   = $row['cy_date'];
        $data[$row['vid']]['px']         = $r;
        $data[$row['vid']]['cy_date']    = $row['cy_date'];
     }
     //全程空白
     $sql = "SELECT  `cyd_id`,`cy_date`,`vid`,`hy_flag`,`ping_jia`,
                COUNT(`assay_order`.`id`) AS count, 
                MIN(ABS(`xiang_dui_pian_cha`)) AS min ,
                MAX(ABS(`xiang_dui_pian_cha`)) AS max
                FROM `assay_order` 
                LEFT JOIN `cy` ON `assay_order`.`cyd_id` = `cy`.`id`
                LEFT JOIN `assay_value` av ON av.id=`assay_order`.`vid`
                WHERE `cy`.`cy_date` LIKE '$date%' $sql_where
                    AND `sid`=0 AND `hy_flag` > 0 
                    AND av.`is_xcjc`='0'
                GROUP BY `vid`,`ping_jia`";
    $qckb=array();
    $query=$DB->query($sql);
    while($row=$DB->fetch_assoc($query)){
        $r = $data[$row['vid']]['kb'];
        if($r['count']!=''&&$r['ping_jia']!=$row['ping_jia']&&$row['ping_jia']!='不合格'){
            $r['ping_jia']  = '';
        }else{
            $r['ping_jia']  = $row['ping_jia'];
        }
        $r['min']       = ($r['min']<$row['min'])?$r['min']:$row['min'];
        $r['max']       = ($r['max']>$row['max'])?$r['max']:$row['max'];
        $r['count']     = $row['count']+intval($r['count']);
        $r['cy_date']   = $row['cy_date'];
        $data[$row['vid']]['kb']         = $r;
        $data[$row['vid']]['cy_date']    = $row['cy_date'];
    }
    /*
    //启用模板排序
    $model = "SELECT * FROM `n_set` WHERE `name` ='xm_px' AND `int3` = 1 limit 1";
    $m_row = $DB->fetch_one_assoc($model);
    if(!empty($m_row)){
    $json  = json_decode($m_row['json']);
    $xm_px = explode(',',$json->px);
    $data1 = array();
    foreach ($xm_px as $key => $value) {
        if(isset($data[$value])){
            $data1[$value] = $data[$value];
        }
    }
    $data = $data1;
    unset($data1);
    }*/
    return $data;
}
