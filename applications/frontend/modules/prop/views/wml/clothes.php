你在<?php echo $floorid;?>楼<br />
你有金币 <?php echo $gb;?>G<br />
<?php echo $db;?>宝石 <a href="">充值</a><br />
体力: <?php echo $power;?>/<?php echo $powerlimit;?> <a href="<?php echo linkwml('prop', 'bag', array('type' => 1, 'gtype' => 'boss'));?>">使用道具</a><br />
点击货架寻找你要的东西<br />
<?php foreach ($floor['props'] as $seq => $item): ?>
<?php if ($item['flag']): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $item['pid']));?>"><?php echo $item['prop']['name'];?></a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $item['pid'], 'num' => 1));?>">买</a><br />
<?php else: ?>
货架 <?php echo $seq+1;?> <a href="<?php echo linkwml('prop', 'flipfloorprop', array('fid' => $floorid, 'seq' => $seq));?>">翻</a><br />
<?php endif; ?>
<?php endforeach; ?>
翻了 <?php echo $floor['opened'];?>/<?php echo $floor['total'];?>个货架<br />
重新进货还需: <?php echo $expire;?><br />
<a href="<?php echo linkwml('prop', 'store_reload', array('fid' => $floorid));?>">立刻进货</a><br />
<?php if (!$nomorefloor): ?>
<a href="<?php echo linkwml('prop', 'up', array('fid' => $floorid + 1));?>">上楼</a><br />
<?php endif; ?>
<?php if ($floorid != 1): ?>
<a href="<?php echo linkwml('prop', 'up', array('fid' => $floorid - 1));?>">下楼</a><br />
<?php endif; ?>
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
