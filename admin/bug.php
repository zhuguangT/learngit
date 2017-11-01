<?php
//BUG  客户提交 页面 
//2011-1-17 新建功能
//得到 GET[url] 是BUG 页面的 url

include "../temp/config.php";

$url=urldecode($_GET[url]);

if($_POST[bug]=='bug')//说明用户提交了 bug 页面
{  

    $subject = "BUG错误报告";
    $bt=$_POST[biaoti];
    $lianxi=$_POST[lianxi];
    $sql="INSERT INTO `glbug` (`id` ,`tjuser` ,`title` ,`url` ,`tel` ,`beizhu` ,`indate` ,`ok` ,`gluser` ,`gldate` ,`huifu`) 
    VALUES (NULL , '$u[userid]', '$bt', '$url', '$lianxi', '$_POST[xiangxi]',CURRENT_TIMESTAMP , '等待解决', '', '', '');";
    $DB->query($sql);
     
     //提交的数据内容 
	 $td=$subject.'<br>'.'提交人：'.$u['userid'].'<br>'.'标题:'.$bt.'<br>'.'url:'.urldecode($_GET[url]).'<br>'.'联系电话:'.
	 $_POST['lianxi'].'<br>'.'详细内容:'.$_POST['xiangxi'];

      //邮件内容 转换字符
     $content    = iconv('utf-8','gb2312',$td);       

     
     //依次发送给系统维护人员
	 $headers = "From: =?utf-8?B?".base64_encode('chenglong')."?= <$value>\r\n";

	 $content = strip_tags($content);

	 $a= @mail($technicalemail,'=?utf-8?B?'.base64_encode('系统开发人员报告错误信息').'?=',$content,$headers);
	 //数据发送
     if($a)
	 {
	 echo "<script>alert('您的问题已经提交，我们会尽快解决，谢谢您对系统改进提出的宝贵意见。');window.close();</script>";
	 }
	 else 
     {
	 echo '邮件发送失败';           
     }

}



disp(admin_bug);

?>



