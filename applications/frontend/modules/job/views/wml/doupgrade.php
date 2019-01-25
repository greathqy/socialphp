<?php if (isset($failed)): ?>
升级失败，
<?php if (isset($level_not_meet)): ?>
公司级别不足。<br />
<?php elseif ($prop_not_meet): ?>
道具不足。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
<?php echo $company_name;?> 升级成功。
<?php if ($profit): ?>
新增: <br />
<?php foreach ($profit as $jobid => $jobInfo): ?>
<?php echo $jobInfo['name'];?>&nbsp;&nbsp;
<?php endforeach; ?>
<?php endif; ?>
<br />
<a href="<?php echo linkwml('job', 'index', array('type' => $type));?>">继续</a>
<?php endif; ?>
