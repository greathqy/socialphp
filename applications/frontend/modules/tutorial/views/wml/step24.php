你验收 <?php echo $star['name'];?> <?php echo $star['jobinfo']['jobname'];?> 的工作。<br />
工作过程中:<br />
没有什么事发生。<br />
<?php if (isset($job_finished)): ?>
<?php if (isset($star_upgraded_to)): ?>
最终获利<?php echo $gb_added;?>，公司名气+<?php echo $company_fame_added;?>，艺人名气+<?php echo $star_fame_added;?>。<br />
<?php if (isset($star_upgraded_to)): ?>
<?php echo $star['name'];?>升级了, 达到了<?php echo $star_upgraded_to;?>级。<br />
<?php endif; ?>
<?php if (isset($company_upgraded_to)): ?>
公司升级了, 达到<?php echo $company_upgraded_to;?>级。<br />
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo linkwml('tutorial', 'complete');?>">下一步</a>