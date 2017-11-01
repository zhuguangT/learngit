<?php
//site_func.php 与站点有关的函数
################更新站点基本信息
function update_site_info( $sid, $site_info ) {
    global $DB;
    $sql = "UPDATE sites SET ";
    reset( $site_info );
    while (list($key, $value) = each($site_info)) 
        $sql .= "$key = '$value',";
    $sql = rtrim( $sql, ',' );
    $sql .= " WHERE id = $sid ";
    //echo $sql;die();
    $DB->query( $sql );
    if ( mysql_affected_rows() == 1 )
        return true;
    return false;
}
######################保存新建站点
function save_new_site( $site_info ) {
    global $DB;
    $sql = "INSERT INTO sites SET ";
    reset( $site_info );
    while (list($key, $value) = each($site_info)) 
        $sql .= "$key = '$value',";
    $sql = rtrim( $sql, ',' );
    $DB->query( $sql );
    if ( $DB->affected_rows() == 1 )
        return $DB->insert_id();
    return false;
}

function is_dup_site( $site_info ) {
    global $DB;
    $sql = "SELECT * FROM sites WHERE 
        site_type = '{$site_info['site_type']}' AND
        water_type = '{$site_info['water_type']}' AND
        river_name = '{$site_info['river_name']}' AND
        site_name = '{$site_info['site_name']}' ";
    $res = $DB->query( $sql );
    $row = $DB->fetch_assoc( $res );
    return $row ? $row['id'] : false;
}

function is_dup_site_group( $sid, $group_name ) {
    global $DB;
    $sql = "SELECT * FROM site_group WHERE site_id = $sid AND group_name = '$group_name' ";
    $res = $DB->query( $sql );
    return $DB->fetch_assoc( $res );
}

#################删除一个或多个站点
function delete_site( $sids ) {
    global $DB;
    $sid = join( ',', $sids );
    $DB->query( "DELETE FROM `sites` WHERE id in ( $sid )" );
    $DB->query( "DELETE FROM `cy_rec` WHERE `sid` in ( $sid )" );
    $DB->query( "DELETE FROM `mission` WHERE `sid` in ( $sid )" );
    $DB->query( "DELETE FROM `assay_order` WHERE `sid` in ( $sid )" );
    $DB->query( "DELETE FROM `ypys_detail` WHERE `sid` in ( $sid )" );
    $DB->query( "DELETE FROM `site_group` WHERE `site_id` in ( $sid )" );
    return true;
}
####################更新站点的统计参数
function update_site_tjcs( $sid, $site_tjcs,$site_type,$old_tjcs='',$vids='') {
    if($site_tjcs!='kong'){
        $site_tj=implode(',',$site_tjcs);
    }else{
        $site_tj='';
    }
    $fzx_id  = FZX_ID;//中心
    if ( !$sid ){
        return false;
    }
    global $DB, $u;
        if($old_tjcs!=''){//站点存在原有统计参数
            $site_old_tjcs=explode(',',$old_tjcs);
            if($site_tj==''){//站点修改后无统计参数
                foreach($site_old_tjcs as $key=>$value){
                    $DB->query( "DELETE FROM `site_group` WHERE site_id = $sid AND group_name = '$value' AND site_type='0' AND fzx_id='".$fzx_id."'");
                }
                $DB->query("INSERT INTO site_group SET site_id = $sid,site_type='0',act='1', group_name = '',assay_values='$vids',ctime = now(), fzx_id='$fzx_id', cuser='{$u['userid']}'"); 
            }else{//站点修改后有统计参数
                $same = array_intersect( $site_old_tjcs, $site_tjcs );//新老统计参数交集（保留的）
                $sites_old_tjcs= array_diff( $site_old_tjcs, $same );//老统计参数和保留的取差集（去掉的）
                $sites_new_tjcs= array_diff( $site_tjcs, $same );//新统计参数和保留的取差集（新增的）
                //print_rr($sites_old_tjcs);print_rr($sites_new_tjcs);die;
                foreach($sites_old_tjcs as $key=>$value){
                    $DB->query("DELETE FROM `site_group` WHERE site_id = $sid AND group_name = '$value' AND site_type='0' AND fzx_id='".$fzx_id."'");
                }
                foreach($sites_new_tjcs as $key=>$value){
                    $DB->query("INSERT INTO site_group SET site_id = $sid,site_type='0',act='1', group_name = '$value', assay_values='$vids',ctime = now(), fzx_id='$fzx_id', cuser='{$u['userid']}'");
                }
            }
        }else{//站点原有统计参数不存在
            $sql = "SELECT * FROM site_group WHERE site_id = $sid AND fzx_id='$fzx_id' AND site_type='0' AND group_name = '' AND act='1'";
            $r = $DB->fetch_one_assoc( $sql );
            $DB->query( "DELETE FROM `site_group` WHERE id = $r[id]");
            if($site_tj!=''){////站点修改后有统计参数
                foreach($site_tjcs as $key=>$value){
                    $DB->query("INSERT INTO site_group SET site_id = $sid,site_type='0',act='1', group_name = '$value',assay_values='$vids',ctime = now(), fzx_id='$fzx_id', cuser='{$u['userid']}'"); 
                }
            }
        }
    
    //修改sites表中的统计参数
    $site_tj=','.$site_tj.',';
    $DB->query( "UPDATE sites SET tjcs = '$site_tj' WHERE id = $sid");
    return true;
}
#####################更新站点的批信息
function update_site_group($sid,$site_group,$site_type,$fzx_id,$vids) {
    if ( !$sid )
        return false;
    global $DB, $u;
    $i = 0;
    $old_group = get_site_group( $sid,$site_type,$fzx_id );
    if(isset($old_group)){
        if($site_group=='kong'){
        $to_be_deleted =$old_group;
        $to_be_added ='kong';
        }else{
            $same = array_intersect( $old_group, $site_group );//老批次和新批次取交集（不变的）
            $to_be_deleted = array_diff( $old_group, $same );//老批次和不变的取差集（隐藏的）
            $to_be_added = array_diff( $site_group, $same );//新批次和不变的取差集（新增的）
            reset( $to_be_added );
        }
        reset( $to_be_deleted );
        while( list( , $value ) = each ( $to_be_deleted  ) )
            $DB->query( "UPDATE site_group SET act='0' WHERE site_id = $sid AND group_name = '$value' AND fzx_id = '$fzx_id' AND site_type='$site_type'");
        if($to_be_added!='kong'){
            while ( list( , $group_name ) = each( $to_be_added ) ) {
                $sql = "INSERT INTO site_group SET site_id = $sid,site_type = '$site_type', group_name = '$group_name', ctime = now(), fzx_id='$fzx_id', cuser='{$u['userid']}',assay_values='$vids'";
                $DB->query( $sql );
            }
        }
    }else{//去掉
        if($site_group!='kong'){
            $same = array_intersect( $old_group, $site_group );//老批次和新批次取交集（不变的）
            $to_be_deleted = array_diff( $old_group, $same );//老批次和不变的取差集（隐藏的）
            $to_be_added = array_diff( $site_group, $same );//新批次和交集取差集（新增的）
            reset( $to_be_added );
            reset( $to_be_deleted );
            while( list( , $value ) = each ( $to_be_deleted  ) ){
                $DB->query( "UPDATE site_group SET act='0' WHERE site_id = $sid AND group_name = '$value' AND fzx_id = '$fzx_id' AND site_type='$site_type'");
            }
            while ( list( , $group_name ) = each( $to_be_added ) ) {
            $sql = "INSERT INTO site_group SET site_id = $sid,site_type = '$site_type', group_name = '$group_name', ctime = now(), fzx_id='$fzx_id', cuser='{$u['userid']}'";
            $DB->query( $sql );
            }
        }
    }
    return true;
}

#####################更新某批中某站点的化验项目
function update_sg_assay_values( $sid, $group_name, $assay_values ,$fids='') {
    global $DB;
    $sql = "UPDATE site_group SET assay_values = '$assay_values',fids='$fids' WHERE site_id = $sid AND group_name = '$group_name'";
    return $DB->query( $sql );
}
#####################得到某批中某站点目前关联的化验项目
function get_sg_assay_values( $sid, $group_name ) {
    global $DB;
    $sql = "SELECT assay_values FROM site_group WHERE site_id = '$sid' AND group_name = '$group_name'";
    $r = $DB->fetch_one_assoc( $sql );
    return elementsToArray( $r['assay_values'] );
}
#####################取出一个站点所属的所有批
function get_site_group( $sid,$site_type,$fzx_id ) {
    global $DB;
    $sql = "SELECT group_name FROM site_group WHERE site_id = '$sid' AND fzx_id='$fzx_id' AND site_type='$site_type' AND act = '1' ORDER BY group_name";
    $res = $DB->query( $sql );
    $result = array();
    while( $row = $DB->fetch_array( $res ) )
        $result[] = $row[0];
    return $result;
}
##################取得某一类站点某一水样类别的所有批
function get_groups( $site_type, $water_type ) {
    global $DB;
    $fzx_id  = FZX_ID;//中心
    $site_group = array();
    $sql = "
        SELECT DISTINCT group_name FROM site_group 
        WHERE site_group.site_type = '{$site_type}'  AND site_group.fzx_id='$fzx_id' AND act='1'
        ORDER BY group_name";
    $res = $DB->query( $sql );
    while( $row = $DB->fetch_array( $res ) )
        $site_group[] = $row[0];
    return $site_group;
}

#####################更新站点的化验项目信息
function update_site_assay_value( $sid, $vids ) {
    global $DB;
    sort($vids);
    $assay_values = join( '|', $vids );
    $sql = "UPDATE sites SET assay_values  = '$assay_values' WHERE id = $sid ";
    $DB->query( $sql );
    if( $DB->affected_rows() == 1 )
        return true;
    return false;
}

#############更新某批中某站点对应的化验项目  vids 是数组
function update_site_group_assay_value( $sid, $site_group, $vids ) {
    global $DB;
    sort($vids);
    $assay_values = join( '|', $vids );
    $sql = "UPDATE site_group SET assay_values  = '$assay_values' WHERE site_id = $sid AND group_name = '$site_group' ";
    $DB->query( $sql );
    if($DB->affected_rows() == 1 )
        return true;
    return false;
}
################更新某批中某站点对应的化验项目 vids 是字符串 $xids  是项目方法的id s 的字符串
function update_site_group_assay_value2( $sid,$sgid='',$fzx_id,$site_type,$site_group,$vids,$xids ) {
    global $DB,$u;
    
    if($sgid!=''){
        $sql = "UPDATE site_group SET act='0' WHERE id=$sgid AND site_id = $sid AND group_name = '$site_group' ";
        $DB->query( $sql );
    }
    $sqll = "INSERT INTO site_group SET site_id = $sid,site_type=$site_type,group_name = '$site_group',assay_values='$vids', ctime = now(), fzx_id=$fzx_id, cuser='{$u['userid']}'";
    $DB->query( $sqll );
    if($DB->affected_rows() == 1 )
        return true;
    return false;
}
#################站点管理，分中心站搜索批次
function get_group_names( $site_type_num = 0, $water_type = '',$act='' ) {
    global $DB;global $u;
    $sql_where  = [];
    if($site_type_num!='' && $site_type_num!='全部'){
        $sql_where[]    = " and s.site_type='".$site_type_num."'";
    }
    if($water_type!=''&&$water_type!='全部'){
        $sql_where[]    = " and s.water_type='".$water_type."'";
    }
    $result = array();
    if($u['xiangxi']!=1){
       $sql_where[] = "and sg.sort<10000";
    }
    if($act !='all'){
       $sql_where[] = "and sg.act='1' ";
    }
    $sql_where_str  = implode(' ', $sql_where);
    $sql="SELECT DISTINCT sg.group_name FROM `site_group` sg  LEFT JOIN sites s ON s.id = sg.site_id  WHERE sg.fzx_id='".$u['fzx_id']."' {$sql_where_str} AND sg.group_name!='' ORDER BY sg.group_name";

    $res=$DB->query($sql);
    while( $row = $DB->fetch_assoc( $res ) ){
        $result[] = $row['group_name'];
    }
    return $result;
}
###################站点管理，总中心搜索统计参数
function get_group_tj( $site_type_num = 0, $water_type = '' ) {
    global $DB;
 if($water_type!='') $water_type_find=" and sites.water_type='$water_type'";
    $result = array();
if($u[xiangxi]!=1) $act="and site_group.sort<10000";
   $sql="SELECT DISTINCT site_group.group_name FROM site_group  LEFT JOIN sites  ON sites.id = site_group.site_id WHERE sites.site_type = '$site_type_num' $water_type_find  $act order by site_group.group_name";
    $res=$DB->query($sql);
    while( $row = $DB->fetch_assoc( $res ) )
        $result[] = $row['group_name'];
    return $result;
}
?>
