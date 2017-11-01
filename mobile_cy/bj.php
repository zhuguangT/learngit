<?php
$checkLogin = false;
include('../temp/config.php');
$syncphp= 'bjsync.php';
//随着采样记录表信息可变的手机采样页面
if(!empty($_GET['fzx_id'])){
    $fzx_id = $_GET['fzx_id'];
}else{
    $fzx_id = '';
    // header('location:sel_hub.php');
}
$sitecy = temp('mobile_cy/template/gtsj_sjcy','1');
//bj.manifest
?>
<!DOCTYPE html>
<html manifest="bj.appcache">
<head>
    <title>安恒手机采样</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="format-detection" content="telephone=no" />
    <link href="iphone.css"   rel="stylesheet" type="text/css" />
    <script src="js/jquery.js"></script>
    <script src="js/md5.js"></script>
    <script src="js/sticky.js"></script>
    <script src="js/xccy.js"></script>
</head>
<body>
    <input type="hidden" name="fzx_id" id="fzx_id" value="<?php echo $fzx_id; ?>">
    <input type="hidden" name="water_describe" id="water_describe" value="<?php echo $water_describe?>">
    <!-- 登录模块 -->
    <section id="login" class="reverse">
        <form >
            <div class="toolbar">
                <h1>登录系统</h1>
            </div>
            <ul class="menu">
                <li >用户名：<select id="uname"></select></li>
                <li>密&nbsp;&nbsp;码：<input id="pass" style="border: none;background: #C6CAE4;border-radius: 8px;font-size: 16px;" type="password" ></li>
            </ul>
        </form>
    </section>
    <!-- 任务列表 -->
    <section id="liebiao" class="reverse">
        <div class="toolbar">
            <!-- <button type="button" name="go_back" class="back" onclick="showsite(dqym,this)">返回</button> -->
            <h1 class="listbut"><span id="listtop_user"></span>任务列表<span id="lastdate" style="font-size:12px"></span></h1>
        </div>
        <ul id="zdlist" class="menu"></ul>
        <div class="toolbar center">
            <input type="hidden" value="-1" name="times"/>
            <button type="button" class="butcd" onclick="showsite(dqym,this)">当月</button>
            <button type="button" class="butcd" onclick="showsite(lastym,this)">前月</button>
            <button type="button" class="butcd" onclick="dbsync(true)">同步</button>
            <button type="button" class="butcd" onclick="exit()">退出</button>
        </div>
    </section>
    <!-- 采样表单 -->
    <section id="cyrec" class="reverse">
        <div class="toolbar">
            <button type="button" id="back" class="back">返回</button>
            <h1 class="cy" name="site_name" data-t="0"></h1>
        </div>
        <ul class="menu">
        <?php echo $sitecy; ?>
        </ul>
    </section>
<script type="text/javascript">
$(document).ready(function(){
    init();
    //测量出手机页面高度，并赋予列表页最小高度  列表最小高度 = min_height - 166px
    // var min_height = $(window).height() - 166;
    // $("#zdlist").css({'min-height':min_height});
})
var db = new StickyStore({
    name: 'cy',
    adapters: ['localStorage', 'indexedDB', 'webSQL', 'cookie'],
    ready: function() {},
    expires: (24*60*60*1000),
    size: 5
})
var syncphp='bjsync.php'
$("input[name='xc_img']").change(function(){
    $("input[name='up']").click();
});

</script>
</body>
</html>
