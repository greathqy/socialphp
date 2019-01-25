<?php if (!$can_upgrade): ?>
你的<?php echo $typemap['name'];?>已经到最高级了，不能在升级!<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
<?php if ($profit): ?>
升级可以获得下列工作。<br />
<?php foreach ($profit as $jobid => $jobInfo): ?>
<?php echo $jobInfo['name'];?> <br />
<?php endforeach; ?>
<?php endif; ?>
升级条件:<br />
公司等级: Lv<?php echo $required['level'];?><br />
道具:<br />
<?php foreach ($required['props'] as $pid => $propInfo): ?>
<?php echo $propInfo['name'];?> x <?php echo $propInfo['num'];?><br />
<?php endforeach; ?>
<a href="<?php echo linkwml('job', 'doupgrade', array('type' => $type));?>">升级</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
