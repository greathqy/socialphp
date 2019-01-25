<?php if ($working_stars): ?>
你要对谁使用 <?php echo $propinfo['name'];?><br />
<?php foreach($working_stars as $star): ?>
<a href="<?php echo linkwml('prop', 'star_consume', array('id' =>$propinfo['id'], 'sid' => $star['id']));?>"><?php echo $star['name'];?></a> (<?php echo $star['jobinfo']['jobname'];?>)
<br />
<?php if ($star['jobinfo']['expire']): ?>
工作完成 <a href="<?php echo linkwml('job', 'complete', array('id' => $star['id']));?>">验收工作</a><br />
<?php else: ?>
还剩 <?php echo $star['jobinfo']['still'];?><br />
<?php endif; ?>
<?php endforeach; ?>
<br />
<?php else: ?>
你没有艺人在打工, 该道具无法使用。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
