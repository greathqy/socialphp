你验收<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a> <?php echo $star['jobinfo']['jobname'];?> 的工作。<br />
工作过程中:<br />
<?php if (!$working_interacts): ?>
没有什么事发生。<br />
<?php else: ?>
<?php foreach ($working_interacts as $timestamp => $item): ?>
<?php if ($item['type'] == 'steal'): ?>
<?php echo $item['time'];?> <a href="<?php echo linkwml('index', 'cvisit', array('id' => $item['uid']));?>"><?php echo $item['uname'];?></a>乘<?php echo $star['name'];?>不注意，顺手牵羊拿走<?php echo $item['amount'];?>金币。<br />
<?php else: ?>
<?php if ($item['direction'] == 'sub'): ?>
<?php echo $item['time'];?> <a href="<?php echo linkwml('index', 'cvisit', array('id' => $item['uid']));?>"><?php echo $item['uname'];?></a><?php echo $item['actname'];?><?php echo $star['name'];?>收益减少<?php echo $item['amount'];?>金币。<br />
<?php else: ?>
<?php echo $item['time'];?> <a href="<?php echo linkwml('index', 'cvisit', array('id' => $item['uid']));?>"><?php echo $item['uname'];?></a>给<?php echo $star['name'];?><?php echo $item['actname'];?>，<?php echo $star['name'];?>越战越勇，额外增加<?php echo $item['amount'];?><br />
<?php endif; ?>
<?php endif;?>
<?php endforeach; ?>
<?php endif; ?>
最终获利<?php echo $gb_added;?>，公司名气+<?php echo $company_fame_added;?>，艺人名气+<?php echo $star_fame_added;?>。<br />
<?php if (isset($star_upgraded_to)): ?>
<?php echo $star['name'];?>升级了, 达到了<?php echo $star_upgraded_to;?>级。<br />
<?php endif; ?>
<?php if (isset($company_upgraded_to)): ?>
公司升级了, 达到<?php echo $company_upgraded_to;?>级。<br />
<?php endif; ?>
空闲中 <a href="<?php echo linkwml('job', 'arrange', array('id' => $star['id']));?>">安排工作</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('star', 'index');?>">艺人列表</a><br />
<a href="<?php echo linkwml('index', 'index');?>">公司首页</a>
