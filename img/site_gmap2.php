<?php
include "../temp/config.php";


//$sid=@$_GET['sid'];
//$group_name=@$_GET['group_name'];
if($_GET[jd] &&  $_GET[wd] && $_GET[yjd]  && $_GET[ywd]){
$jd=$_GET[jd];//现在的经度
$wd=$_GET[wd];//现在的纬度


$jd2=$_GET[yjd];//规定点经度
$wd2=$_GET[ywd];//规定点的纬度 
}

//转换GPS 坐标为百度地图坐标
$x=array($jd,$jd2);
$y=array($wd,$wd2);

$arr=BDGPS($x,$y);

$jd=$arr[0]['x'];
$jd2=$arr[1]['x'];

$wd=$arr[0]['y'];
$wd2=$arr[1]['y'];


/*百度的坐标转换 函数 $from 是输入坐标类型， $to 是输出坐标类型，  $x ，$y 是经纬度(数组)  返回数组
Array
(
    [error] => 0
    [x] => 116.2610991221
    [y] => 29.820560874846
)
* error 代表是否出现错误
* */
function BDGPS($x,$y,$from=0,$to=4){
	$xstr=implode(',',$x);
	$ystr=implode(',',$y);
	//目前淮委不能直接联网 只能通过 2.7 代理访问百度api 服务器了
	$handle = fopen("http://api.map.baidu.com/ag/coord/convert?from=$from&to=$to&x=$xstr&y=$ystr&mode=1", 'r');
	//$handle = fopen("http://192.168.2.7/test/bdgps.php?from=$from&to=$to&x=$xstr&y=$ystr&mode=1", 'r');
	$str= fread($handle, 500);
	$zbarr=array();
    $arr=json_decode($str,true);
    foreach($arr as $varr){
		$varr['x']=base64_decode($varr['x']);
		$varr['y']=base64_decode($varr['y']);
		$zbarr[]=$varr;
	}
	return $zbarr;
}

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
echo "  

		var  zwd='$wd';
		var zjd='$jd';		
	    var  zwd2='$wd2';//规定点的纬度
		var zjd2='$jd2';//规定点的经度
		";
		
  ?>
var map = new BMap.Map("container");
var pwx = new BMap.Point(zjd2, zwd2);//初始化点的时候需要显示的点用规定点作为
var zzb2=new BMap.Point(zjd2, zwd2);//规定点的经度和纬度
var zzb=new BMap.Point(zjd, zwd);
var iconImg =  new BMap.Icon("../img/us_mk_icon.png", new BMap.Size(23,25),{imageOffset: new BMap.Size(-23,-21),infoWindowOffset:new BMap.Size(12+5,1),offset:new BMap.Size(9,25)})

var marker = new BMap.Marker(zzb,{icon:iconImg});
var label = new BMap.Label('实际位置',{"offset":new BMap.Size(9,-15)});
marker.setLabel(label);
map.addOverlay(marker); 
marker.enableDragging(true); // 设置标注可拖拽

map.centerAndZoom(pwx, 16);//地图默认显示位置
map.addControl(new BMap.NavigationControl());
map.enableScrollWheelZoom();

var marker = new BMap.Marker(zzb2);
var label = new BMap.Label('规定位置',{"offset":new BMap.Size(9,-15)});
marker.setLabel(label);
map.addOverlay(marker); 
marker.enableDragging(true); // 设置标注可拖拽




   function queding() {
  location='static.php';
    };
       function fanhui() {
  location='static.php ' ;
    };


</script>

