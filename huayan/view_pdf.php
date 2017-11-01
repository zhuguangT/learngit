<?php
/*
 * 功能：化验单与仪器打印出来的pdf关联查看和管理页面
 * 作者：lisongsen
 * 时间：2014-07-21
 * */
ob_start();
include "../temp/config.php";

$h=$DB->fetch_one_assoc("select file from pdf where id='$_GET[pid]'");

$file="/home/files/$h[file]";

read_pdf($file);
    function read_pdf($file) {

        if(strtolower(substr(strrchr($file,'.'),1)) != 'pdf') {

            echo '文件格式不对.';

            return;

        }

        if(!file_exists($file)) {

            echo '文件不存在';

            return;

        }
        //查看文件
         if($_GET['handle']=='see'){
            header('Content-type: application/pdf');
            header('filename='.$file_name);
            readfile($file);
        }
        //下载文件
        if($_GET['handle']=='download'){
            $file_name=basename($file);
            header("Content-Type:   application/mspdf");        
            header("Content-Disposition:   attachment;   filename=$file_name");        
            header("Pragma:   no-cache");        
            header("Expires:   0");   
            readfile($file);
        }
    }

?>
