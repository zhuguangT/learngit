<?php
$checkLogin = false;
include('../temp/config.php');
$hubs = array();
$hub_opt = '';
$sql = "SELECT `id`, `hub_name` FROM `hub_info` WHERE `id`='1' OR `parent_id`='1'";
$query = $DB->query($sql);
while ($row = $DB->fetch_assoc($query)) {
    $hubs[] = $row;
    $hub_opt = "<option value='{$row['id']}'>{$row['hub_name']}</option>";
}
if(count($hubs) == 1) {
    header('location:bj.php?fzx_id='.$hubs[0]['id']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>安恒手机采样</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="format-detection" content="telephone=no" />
    <link href="iphone.css"   rel="stylesheet" type="text/css" />
    <script src="js/jquery.js"></script>
</head>
<body>
    <div class="toolbar">
        <h1>选择单位实验室</h1>
    </div>
    <select id="uname">
        <?php echo $hub_opt;?>
    </select>
<script type="text/javascript">

</script>
</body>
</html>
