<?php if (isset($consumed)): ?>
<?php echo $star['name'];?><?php echo isset($propinfo['funny_desc']) ? $propinfo['funny_desc'] : $propinfo['desc'];?>
<br />
<?php if ($star['jobinfo']['expire']): ?>
工作完成 <?php echo $star['jobinfo']['jobname'];?> <a href="<?php echo linkwml('job', 'complete', array('id' => $star['id']));?>">验收</a><br />
<?php else: ?>
工作时间还剩<?php echo $star['jobinfo']['still'];?> <a href="<?php echo linkwml('prop', 'selectstar', array('id' => $propinfo['id']));?>">使用道具</a><br />
<?php endif; ?>
<?php else: //道具使用失败?>
<?php if (isset($cant_use)): ?>
<?php echo $propinfo['name'];?>已经超过当日使用次数限制, 不能再使用。<br />
<?php elseif (isset($star_not_in_job)): ?>
<?php echo $star['name'];?>不在工作中，不能使用道具。<br />
<?php elseif (isset($prop_not_enough)): ?>
<?php echo $propinfo['name'];?>数量不足，无法使用。<br/>
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
