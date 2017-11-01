<?php
include "../temp/config.php";
$sid=@$_GET['sid'];
$site_type=@$_GET['site_type'];
$group_name=@$_GET['group_name'];
$action=@$_GET['action'];
if($_GET[jd]){
	$jd=dfmto($_GET[jd]);
}else{
	$jd=$gx_jingdu;
}
if($_GET[wd]){
	$wd=dfmto($_GET[wd]);
}else{
	$wd=$gx_weidu;
}
$wd=round($wd,6);
$jd=round($jd,6);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>设置站点坐标</title>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2"></script>
</head>
<body>
<div style="width:100%;height:520px;" id="container"></div>
<div style="margin-left: 40%;"><input type="button" onclick="queding()"  value="确定"><input type="button" style="margin-left:40px;" onclick="fanhui()"  value="返回"></div>
</body>
</html>
<script type="text/javascript">
	<?php  
echo "  var zwd=$wd;
		var zjd=$jd;
		var sid=$sid;
		var site_type='$site_type';
		var gname='$group_name';
		var action='$action';
		";
		
  ?>
var map = new BMap.Map("container");
//var pwx = new BMap.Point(sjd, swd);
var zzb=new BMap.Point(zjd, zwd);
map.centerAndZoom(zzb, 14);
map.addControl(new BMap.NavigationControl());
map.enableScrollWheelZoom();
var marker = new BMap.Marker(zzb);
map.addOverlay(marker); 
marker.enableDragging(true); // 设置标注可拖拽

   function queding() {
  location='site_info.php?site_id='+sid+'&group_name='+gname+'&site_type='+site_type+'&action='+action+'&jingdu='+marker.point.lng+'&weidu='+marker.point.lat;
    };
       function fanhui() {
  location='site_info.php?site_id='+sid+'&group_name='+gname+'&site_type='+site_type+'&action='+action;
    };
</script>  
