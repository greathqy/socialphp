你有金币<?php echo $gb;?>G<br />
钻石 <?php echo $db;?> <a href="">充值</a><br />
点击名称查看详细<br />
<?php if ($sel == 'star'): ?>
艺人用|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'index', array('type' => $store_type, 'gtype' => 'star'));?>">艺人用</a>|
<?php endif; ?>
<?php if ($sel == 'boss'): ?>
老板用
<?php else: ?>
<a href="<?php echo linkwml('prop', 'index', array('type' => $store_type, 'gtype' => 'boss'));?>">老板用</a>
<?php endif; ?><br />
<?php foreach ($props as $prop): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $prop['id']));?>"><?php echo $prop['name'];?></a><br />
单价 <?php echo $prop['price'];?><?php echo ($prop['pricetype'] == 'gb') ? 'G' : 'D';?> 买 <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 1));?>">1</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 2));?>">2</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 5));?>">5</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 10));?>">10</a><br />
<?php endforeach; ?>
<?php echo $pagination;?><br /><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
