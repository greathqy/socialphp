艺人数: <?php echo $total;?>/<?php echo $max;?>
<?php if ($still): ?>
<br />
你一个艺人都没有，快去<a href="<?php echo linkwml('recruit', 'candidates');?>">招聘</a>吧。
<?php endif;?>
<br />
<?php foreach ($stars as $star): ?>
>><a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a><br />
<?php if ($star['jobinfo']['injob']): ?>
<?php if ($star['jobinfo']['expire']): ?>
完成 <?php echo $star['jobinfo']['jobname'];?><br />
<a href="<?php echo linkwml('job', 'complete', array('sid' => $star['id'], 'jid' => $star['jobinfo']['jobid']));?>">验收工作</a><br />
<?php else: ?>
<a href="<?php echo linkwml('job', 'detail', array('id' => $star['jobinfo']['jobid']));?>"><?php echo $star['jobinfo']['jobname'];?></a> 中<br />
还有 <?php echo $star['jobinfo']['still'];?> <a href="<?php echo linkwml('prop', 'bag', array('type' => 1));?>">使用道具</a><br />
<?php endif; ?>
<?php else: ?>
空闲中 <a href="<?php echo linkwml('job', 'arrange', array('id' => $star['id']));?>">安排工作</a><br />
<?php endif; ?>
<?php endforeach; ?>
<br />
<a href="<?php echo $link_prev;?>">返回</a>
