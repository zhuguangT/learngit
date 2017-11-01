<?php
include "../temp/config.php";
// $sitesql = "select s.id,s.tjcs,sg.group_name,sg.id from sites as s left join site_group as sg on s.id=sg.site_id where s.site_type = 0 and sg.group_name not in  ";
$sitesql = $DB->query("select id,tjcs from sites where site_type=0 and tjcs <>',,'");
while($resite = $DB->fetch_assoc($sitesql)){
	if($resite[tjcs]!=',,'){
		$tjcs = substr($resite[tjcs],1, strlen($resite[tjcs])-2);
		$gsql = "select id,site_id from site_group where site_id={$resite[id]} and site_type='0' and group_name not in ($tjcs) and act<>'0' group by site_id";
		$reg = $DB->query($gsql);
		
		while($row = $DB->fetch_assoc($gsql)){
			$da[] = $row[site_id];
		}
	}
}
var_dump($da);
?>