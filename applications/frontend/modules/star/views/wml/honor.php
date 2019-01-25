<?php echo $star['name'];?>的荣誉<br />
<?php if (!$honor): ?>
你的艺人暂时没有荣誉。
<?php else: ?>
<?php foreach ($honor as $jobId => $job): ?>
<?php echo $job['name'];?>: <?php echo $job['win'];?>胜<?php echo $job['lost'];?>负<br />
胜率: <?php echo $job['ratio'];?>%<br />
<?php endforeach; ?>
<?php endif; ?>
<br />
<a href="<?php echo $link_prev;?>">返回</a>
