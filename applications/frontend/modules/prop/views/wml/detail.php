你有金币 <?php echo $gb;?>G<br />
钻石 <?php echo $db;?> <a href="">充值</a><br />
<a href="<?php echo linkwml('prop', 'detail', array('id' => $prop['id']));?>"><?php echo $prop['name'];?></a><br />
道具介绍:
<?php echo $prop['desc'];?><br />
单价 <?php echo $prop['price'];?><?php echo $prop['pricetype'] == 'gb' ? 'G' : '钻石';?> 
<?php if (isset($is_decorate)): ?>
<a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id']));?>">买</a><br />
<?php else: ?>
买 <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 1));?>">1</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 2));?>">2</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 5));?>">5</a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $prop['id'], 'num' => 10));?>">10</a><br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
