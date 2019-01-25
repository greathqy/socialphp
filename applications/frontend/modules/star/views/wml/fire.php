<?php if (isset($inwork)): ?>
<?php echo $star['name'];?>正在打工中, 不能解雇。<br />
<a href="<?php echo linkwml('star', 'index');?>">继续</a>
<?php elseif (isset($fired)): ?>
你已经成功解雇了<?php echo $star['name'];?><br />
<a href="<?php echo linkwml('star', 'index');?>">继续</a>
<?php else: ?>
你确定要解雇<?php echo $star['name'];?>吗? <br />
<a href="<?php echo linkwml('star', 'fire', array('confirm' => 1, 'id' => $star['id']));?>">确定</a><br />
<a href="<?php echo linkwml('star', 'index');?>">取消</a><br />
<?php endif; ?>
