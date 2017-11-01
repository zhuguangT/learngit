<?php
//更新站点显示顺序
include "../temp/config.php";
if( $u['userid'] == '' )
    nologin();
$data = array();
if( $_GET['d'] && is_array( $_GET['d'] ) )
    $data = $_GET['d'];

$group_name = mysql_real_escape_string($_GET['group_name']);
while( list( $sid, $sort ) = each( $data ) ) {
    $sql = "UPDATE sites SET sort = '$sort' WHERE id = '$sid' ";
    $DB->query( $sql );
}
gotourl( $_SESSION['back_url'] );
