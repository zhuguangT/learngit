<?php
//调试用函数库

function print_rr($obj){
    echo '<pre>';
    print_r($obj);
    echo '</pre>';
}
function pr($obj){
    print_rr($obj);
    die();
}

function e_( $msg = '' ){
    global $_header;
    $msg = $_header . $msg;
    if( !$msg ) 
        echo '<hr />'; 
    else  
        echo $msg,'<br /><hr />';
}
//显示调试信息
function debug(){
    print_rr($GLOBALS);
    exit();
}

//显示错误信息并中止程序
function halt($err_msg=''){
    global $_header;
    $d = debug_backtrace();
    e_($_header . "fatal error@{$d[0]['file']}|line no.:{$d[0]['line']}\ninfo:{$err_msg}\nmore info:\n");
    pr($d);
}


?>
