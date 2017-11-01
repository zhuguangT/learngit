<?php


include "../temp/config.php";
// print_rr($_GET);die;
$date = substr($_GET['pjdate'],0,4);
$id=$_GET['id'];
$year=$_GET['year'];
switch($_GET['action']){

    case '删除':
        $re=$DB->query("SELECT fujian FROM `gys_gl` WHERE id='$_GET[id]'");
        while($data=$DB->fetch_assoc($re)){
            $arr=json_decode($data['fujian']);
            if($od=opendir("./upfiles")){
                foreach($arr as $key=>$value){
                    @unlink("./upfiles/".$value);
                }
            }

        }
        $DB->query("delete from `gys_gl` where `id`='$_GET[id]'");
        $DB->query("delete from `ghs_pingjia` where `gys_id`='$_GET[id]'");
        //取消 AND `pjdate` LIKE '%$date%'
        // $DB->query("delete from `n_set` where `id`=$_GET[id]");
        //$DB->query("delete from `bzwz_detail` where `wz_id`=$_GET[wz_id]");
        //$DB->query("delete from `bzwz_ls` where `wz_id`=$_GET[wz_id]");
        gotourl("$rooturl/gys/gys_list.php");
        break;
    case 'file':
    	$re=$DB->query("SELECT fujian FROM `gys_gl` WHERE id='{$id}'");
    	while($data=$DB->fetch_assoc($re)){
    		$data=json_decode($data['fujian'],true);
			$num=$_GET['num'];
			if($od=opendir("./upfiles")){
				@unlink("./upfiles/".$data[$num]);
			}
    		unset($data["$num"]);
    		$data=JSON($data);
    		$DB->query("UPDATE `gys_gl` SET fujian='{$data}' WHERE id='{$id}'");
    		gotourl("$rooturl/gys/pingjia.php?parent_id=$id");
    	}
    	die;
    	break;

}

?>
