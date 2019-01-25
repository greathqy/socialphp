<?php if (isset($opened)): ?>
成功开通培训班，有效期<?php echo $payconfig['days'];?>天。<br />
<a href="<?php echo linkwml('training', 'index');?>">继续</a>
<?php elseif (isset($prolonged)): ?>
延长了培训班有效期<?php echo $payconfig['days'];?>天<br />
<a href="<?php echo linkwml('training', 'index');?>">继续</a>
<?php elseif (isset($failed)): ?>
钻石不足，无法开通/延长，请<a href="">充值</a><br />
<?php else: ?>
<?php if ($classroom): ?>
你已经用钻石开通过培训班了，你要使用<?php echo $payconfig['price'];?>钻石延长时间<?php echo $payconfig['days'];?>天吗？<br />
<a href="<?php echo linkwml('training', 'dbopen', array('confirm' => 1));?>">确认延长</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
你要使用<?php echo $payconfig['price'];?>钻石开通培训班<?php echo $payconfig['days'];?>天吗?<br />
<a href="<?php echo linkwml('training', 'dbopen', array('confirm' => 1));?>">确认开通</a>
<?php endif; ?>
<?php endif; ?>
