<?php if (isset($nomoney)): ?>
钻石不够, 请<a href="">充值</a><br />
<?php elseif (isset($reloaded)): ?>
进货成功，点击继续到商店看看。<br />
<a href="<?php echo linkwml('prop', 'clothes', array('fid' => $floorid));?>">继续</a>
<?php else: ?>
立刻进货需要消耗<?php echo $amount;?>钻石。<br />
<a href="<?php echo linkwml('prop', 'store_reload', array('fid' => $floorid, 'confirm' => 1));?>">确认</a><br />
<a href="<?php echo linkwml('prop', 'clothes', array('fid' => $floorid));?>">返回</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
