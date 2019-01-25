<?php if (isset($failed)): ?>
解锁失败,
<?php if (isset($nolevel)): ?>
公司等级不够。
<?php elseif (isset($nogb)): ?>
GB不足。
<?php elseif (isset($noprop)): ?>
道具不足。
<?php endif; ?>
<br />
<a href="<?php echo linkwml('prop', 'clothes', array('fid' => $floorid - 1));?>">返回</a>
<?php elseif (isset($unlocked)): ?>
解锁成功，你可以到<?php echo $floorid;?>楼购物了。<br />
<a href="<?php echo linkwml('prop', 'clothes', array('fid' => $floorid));?>">继续</a>
<?php else: ?>
要解锁服饰店<?php echo $floorid;?>楼，需要以下条件: <br />
<?php if (isset($require['company_level'])): ?>
公司等级: <?php echo $require['company_level'];?><br />
<?php endif; ?>
<?php if (isset($require['gb'])): ?>
<?php echo $require['gb'];?>G <br />
<?php endif; ?>
<?php if (isset($require['props'])): ?>
<?php foreach ($require['props'] as $pid => $item): ?>
<?php echo $item['name'];?> x <?php echo $item['count'];?><br />
<?php endforeach; ?>
<?php endif; ?>
<a href="<?php echo linkwml('prop', 'up', array('fid' => $floorid, 'confirm' => 1));?>">确认</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
