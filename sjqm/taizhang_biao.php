<?php echo $print_head;?>
<style type="text/css">
	table.single tr td{ height: 35px;line-height: 35px;border:1px solid black;}
</style>
<?php foreach ($data as $table_key => $sj) { ?>
	<div style="text-align:center;width:26cm;margin:0 auto;">
		<div style="float:left;width:200px">&nbsp;</div>单位名称：<?php echo $u['hub_name'];?>
		<div style="float:right;width:200px">打印时间：<?php echo date('Y-m-d');?></div>
	</div>
	<h3 class="header smaller center title">库房管理台账</h3>
	<table style="margin-bottom:2px;padding:0;width:26cm;">
		<tr>
			<td style="width:50px;text-align:right;">名称：</td>
			<td style="border-bottom:1px solid #000;width:100px;"><?php echo $sj[0]['name'];?></td>
			<td style="width:50px;text-align:right;">规格：</td>
			<td style="border-bottom:1px solid #000;width:50px;"><?php echo $sj[0]['guige'];?></td>
			<td style="width:50px;text-align:right;">级别：</td>
			<td style="border-bottom:1px solid #000;width:80px;"><?php echo $sj[0]['jibie'];?></td>
			<td style="width:100px;text-align:right;">化学试剂别名：</td>
			<td style="border-bottom:1px solid #000;width:80px;"><?php echo $sj[0]['proc_nice(increment)'];?></td>
			<td style="width:70px;text-align:right;">分子式：</td>
			<td style="border-bottom:1px solid #000;width:80px;"><?php echo $sj[0]['fenzi_shi'];?></td>
		</tr>
	</table>
	<table class="single" style="width:26cm;text-align:center" align="center">
		<tr>
			<td rowspan=2>年</td>
			<td rowspan=2>月</td>
			<td rowspan=2>日</td>
			<td rowspan=2>摘&nbsp;&nbsp;&nbsp;&nbsp;要</td>
			<td rowspan=2>生产批号</td>
			<td rowspan=2>成产厂家</td>
			<td rowspan=2>有效期</td>
			<td colspan=4>入库</td>
			<td colspan=4>出库</td>
			<td colspan=4>结存</td>
		</tr>
		<tr>
			<td>数量</td>
			<td>单位</td>
			<td>单价<br />(元)</td>
			<td>金额</td>
			<td>数量</td>
			<td>单位</td>
			<td>单价<br />(元)</td>
			<td>金额</td>
			<td>数量</td>
			<td>单位</td>
			<td>单价<br />(元)</td>
			<td>金额</td>
		</tr>
		<?php foreach ($sj as $key => $value) { ?>
		<tr>
			<td><?php echo $value['Y'];?></td>
			<td><?php echo $value['m'];?></td>
			<td><?php echo $value['d'];?></td>
			<td><?php echo $value['zhaiyao'];?></td>
			<td><?php echo $value['pihao'];?></td>
			<td><?php echo $value['changjia'];?></td>
			<td><?php echo $value['youxiaoqi'];?></td>

			<td><?php echo $value['type']=='r'? $value['shuliang'] : '&nbsp;';?></td>
			<td><?php echo $value['type']=='r'? $value['danwei'] : '&nbsp;';?></td>
			<td><?php echo $value['danjia'] ? ($value['type']=='r'? $value['danjia'] : '&nbsp;') : '&nbsp;';?></td>
			<td><?php echo $value['danjia'] ? ($value['type']=='r'? $value['shuliang']*$value['danjia'] : '&nbsp;') : '&nbsp;';?></td>

			<td><?php echo $value['type']=='c'? $value['shuliang'] : '&nbsp;';?></td>
			<td><?php echo $value['type']=='c'? $value['danwei'] : '&nbsp;';?></td>
			<td><?php echo $value['danjia'] ? ($value['type']=='c'? $value['danjia'] : '&nbsp;') : '&nbsp;';?></td>
			<td><?php echo $value['danjia'] ? ($value['type']=='c'? $value['shuliang']*$value['danjia'] : '&nbsp;') : '&nbsp;';?></td>

			<td><?php echo $value['jiecun'];?></td>
			<td><?php echo $value['danwei'];?></td>
			<td><?php echo $value['danjia'] ? $value['danjia'] : '&nbsp;';?></td>
			<td><?php echo $value['danjia'] ? $value['jiecun']*$value['danjia'] : '&nbsp;';?></td>
		</tr>
		<?php
			}
		?>
	</table>
	<div style="page-break-before: always;"></div>
<?php } echo $print_foot;?>