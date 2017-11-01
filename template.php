<?php
/**
* 功能：模板在线修改
* 作者：
* 日期：2014-04-09
* 描述：模板在线修改
*/
include 'temp/config.php';
if(!$u['admin']) noquanxian('admin');

switch($_REQUEST['action']){
    case 'modi':
        $_REQUEST['data']=stripslashes($_REQUEST[data]);
        $fp=fopen("template/{$_REQUEST['name']}","wb");
	if($_REQUEST[note]!='')
	fwrite($fp,"<!-- $_REQUEST[note] -->\r\n");
        fwrite($fp,$_REQUEST['data']);
        fclose($fp);
        @chmod("$rootdir/template/{$_REQUEST['name']}",0666);
		gotourl("$rooturl/template.php?action=disp&name={$_REQUEST['name']}");
        break;
    default:
        if(file_exists("$rootdir/template/$_REQUEST[name]")){
            $fp=fopen("$rootdir/template/$_REQUEST[name]",'r');
            $headtitle="修改模板$_REQUEST[name] ";
            $lines=fgets($fp); //取第一行看注释
            if(substr($lines,0,5)=='<!-- '){
                $note=strtr($lines,array('<!-- '=>'',' -->'=>''));
                $lines='';
            }
	    $headtitle=$_REQUEST[name];
            $linecount=2;
            while(!feof($fp)){
                $linecount++;
                $lines.=fgets($fp);
            }
        }
        $lines=htmlspecialchars($lines);
        $_file='template_disp';
        disp($_file);
        break;
}
toexit();
?>
