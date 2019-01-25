<?php echo $star['name'];?>的服饰<br />
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'top'));?>">上装</a>:
<?php if (isset($star['equip']['top']) && $star['equip']['top']): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $star['equip']['top']));?>"><?php echo $star['equip_detail']['top']['name'];?></a>
<?php else: ?>
无
<?php endif;?>
<br />
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'bottom'));?>">下装</a>:
<?php if (isset($star['equip']['bottom']) && $star['equip']['bottom']): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $star['equip']['bottom']));?>"><?php echo $star['equip_detail']['bottom']['name'];?></a>
<?php else: ?>
无
<?php endif;?>
<br />
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'd1'));?>">配饰1</a>:
<?php if (isset($star['equip']['d1']) && $star['equip']['d1']): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $star['equip']['d1']));?>"><?php echo $star['equip_detail']['d1']['name'];?></a>
<?php else: ?>
无
<?php endif;?>
<br />
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'd2'));?>">配饰2</a>:
<?php if (isset($star['equip']['d2']) && $star['equip']['d2']): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $star['equip']['d2']));?>"><?php echo $star['equip_detail']['d2']['name'];?></a>
<?php else: ?>
无
<?php endif;?>
<br />
<br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('star', 'index');?>">艺人列表</a>
