你在<?php echo $floorid;?>楼<br />
你有 <?php echo $gb;?>金币<br />
<?php echo $db;?>钻石<br />
体力: <?php echo $power;?>/<?php echo $powerlimit;?><br />
点击货架寻找你要的东西<br />
<?php foreach ($floor['props'] as $seq => $item): ?>
<?php if ($item['flag']): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $item['pid']));?>"><?php echo $item['prop']['name'];?></a> <a href="<?php echo linkwml('prop', 'buy', array('id' => $item['pid'], 'num' => 1));?>">买</a><br />
<?php else: ?>
货架 <?php echo $seq+1;?> <a href="<?php echo linkwml('tutorial', 'step13', array('fid' => $floorid, 'seq' => $seq));?>">翻</a><br />
<?php endif; ?>
<?php endforeach; ?>
翻了 <?php echo $floor['opened'];?>/<?php echo $floor['total'];?>个货架<br />
重新进货还需: <?php echo $expire;?><br />
