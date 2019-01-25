请选择培训班:<br />
<?php foreach ($training['classes'] as $i => $class): ?>
<?php if ($class['spare']): ?>
<a href="<?php echo linkwml('training', 'attend', array('id' => $i));?>">培训<?php echo $i;?>班</a><br />
空闲可以参加。<br />
<?php else: ?>
培训<?php echo $i?>班<br />
该班名额已满，需要等待<?php echo $class['still'];?><br />
<a href="<?php echo linkwml('training', 'bruteforce', array('id' => $i));?>">插班</a><br />
<?php endif; ?>
<?php endforeach; ?>
<a href="<?php echo linkwml('training', 'openclassroom');?>">开设新培训班</a><br />
<a href="<?php echo linkwml('training', 'upgrade');?>">升级培训中心</a><br />
<a href="<?php echo linkwml('training', 'unlock');?>">解锁培训项目</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
