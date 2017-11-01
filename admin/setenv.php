<?php
$cvsver='$Id: setenv.php,v 1.7 2010-03-01 11:28:21 lisongsen Exp $';
include "../temp/config.php";
$ok=0;
switch($_GET[action]) {
case 'debug':
    $ok=1;
    break;
case 'xiangxi':
    $ok=1;
    break;
}

if( $ok == 1 ) {
    if( $u[$_GET['action']] == 1 )
        unset( $_SESSION[u][$_GET[action]] );
    else
        $_SESSION[u][$_GET['action']] = 1;
}
if( $_GET['goback'] ) 
    goback();
else
    gotourl( $_SESSION['url_stack'][1] );
?>
