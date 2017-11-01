<?php
/*
 <? to <?php
add $cvs_ver
*/
$cvs_ver='$Id: file_fix.php,v 1.2 2009-09-27 06:28:52 lisongsen Exp $';
include '../temp/config.php';
global $fix;
$filename=$_SERVER[argv][1];
if($filename=='') exit();
if(!file_exists($filename)) exit();
$str=file_get_contents($filename);
$file=file($filename);
unlink($filename);
$fp=fopen($filename,'w');
if(strpos($str,'$cvs_ver')===FALSE) $fix[cvs_ver]=1;
while($a=each($file))
{
fix_line($a[value]);
if(trim($a[value])=='<?php') fix_cvs_ver($a[value]);
fwrite($fp,$a[value]);
}
fclose($fp);
function fix_line(& $line)
{
$line=strtr($line,array("\r"=>""));
$str=trim($line);
if($str=='<?')
{ 
$line="<?php\n";
return $line;
}
return $line;
}
function fix_cvs_ver(& $line)
{
global $fix;
if($fix[cvs_ver]==1)
{
$line.="\$cvs_ver='\$Id: "
."- $';\n";
$fix[cvs_ver]='';
}

}
?>
