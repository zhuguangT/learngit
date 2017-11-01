<?php
/**
* 改变站点批名子程序
*/
include "../temp/config.php";
$site_type_num = get_int($_GET['site_type']);
if( $site_type_num === false )
    die( "非法站点类别" );
if ( $_GET['group_name'] )
    $_group_name = " AND site_group.group_name = '{$_GET['group_name']}' ";
else
    die( '未得到有效批名' );
if ( $_GET['new_group_name'] )
    $_new_group_name = " AND site_group.group_name = '{$_GET['new_group_name']}' ";
else
    die( '未得到有效新批名' );

$r=$DB->fetch_one_assoc("SELECT * FROM `site_group` WHERE `group_name` = '{$_GET['new_group_name']}'");
if($r[group_name]!='')die( '批次名称重复，请换一个批次名称!' );

$old_sids = array();

$sql = "
SELECT sites.id FROM sites LEFT JOIN site_group ON site_group.site_id = sites.id 
WHERE sites.site_type = '$site_type_num' $_water_type $_group_name ";
$R = $DB->query( $sql );
while ( $r = mysql_fetch_assoc( $R ) )
    $old_sids[] = $r['id'];

$already_in_new_group = array();
$sql = "
SELECT sites.id FROM sites LEFT JOIN site_group ON site_group.site_id = sites.id 
WHERE sites.site_type = '$site_type_num' $_water_type $_new_group_name ";
$R = $DB->query( $sql );
while ( $r = mysql_fetch_assoc( $R ) )
    $already_in_new_group[] = $r['id'];

for ( $i = 0; $i < count( $old_sids ); $i++ ) {
    if ( in_array( $old_sids[$i], $already_in_new_group ) )
        $sql = "DELETE FROM site_group WHERE group_name = '{$_GET['group_name']}' AND site_id = '{$old_sids[$i]}'";
    else
        $sql = "
UPDATE site_group SET 
group_name = '{$_GET['new_group_name']}', 
ctime = now(),
cuser = '{$u['userid']}' WHERE group_name = '{$_GET['group_name']}' AND site_id = '{$old_sids[$i]}'";
    $DB->query( $sql );
}
gotourl( "$rooturl/site/site_list_new.php?site_type={$_GET['site_type']}&water_type={$_GET['water_type']}&group_name=$new_group_name" );
