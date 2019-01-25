欢迎来到<?php echo '';//$company_name;?>，现在有以下工作可选:<br />
普通工作<br />
<?php foreach ($normal as $jobId => $jobDetail): ?>
><a href="<?php echo linkwml('job', 'detail', array('id' => $jobId));?>"><?php echo $jobDetail['name'];?></a><br />
<?php endforeach; ?>
特殊工作
<?php if (!isset($special) || !$special): ?>
>暂无<br />
<?php else: ?>
<?php foreach($special as $jobId => $jobDetail): ?>
><a href="<?php echo linkwml('job', 'detail', array('id' => $jobId));?>"><?php echo $jobDetail['name'];?></a><br />
<?php endforeach; ?>
<?php endif; ?>
<a href="<?php echo linkwml('job', 'upgrade', array('type' => $type));?>">升级</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
