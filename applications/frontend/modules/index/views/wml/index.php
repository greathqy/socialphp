<?php if ($notify_sys): ?>
公告: <?php echo $notify_sys;?> (<a href="<?php echo linkwml('notify', 'index', array('type'=>'sys'));?>">更多</a>)<br />
<?php endif; ?>
<?php if ($notify_alert): ?>
通知: <?php echo $notify_alert;?> (<a href="<?php echo linkwml('notify', 'index', array('type'=>'alert'));?>">更多</a>)<br />
<?php endif; ?>
<?php if ($notify_feed): ?>
动态: <?php echo $notify_feed;?> (<a href="<?php echo linkwml('notify', 'index', array('type'=>'feed'));?>">更多</a>)<br />
<?php endif; ?>
<?php echo $company['name'];?> (<a href="<?php echo linkwml('index', 'changename');?>">改</a>)<br />
名气: Lv<?php echo $company['level'];?> (<?php echo $upgradeinfo['my'];?>/<?php echo $upgradeinfo['diff'];?>)<br />
金币: <?php echo $userinfo['gb'];?>G<br />
钻石: <?php echo $userinfo['db'];?>D<br />
老板: <?php echo $userinfo['name'];?><br />
体力: <?php echo $userinfo['power'];?>/<?php echo $userinfo['powerlimit'];?> 
<?php if ($userinfo['power'] < $userinfo['powerlimit']): ?>
<a href="<?php echo linkwml('index', 'useprop');?>">使用道具</a>
<?php endif; ?>
<br />
<?php echo $restore_power[0];?> 分钟恢复 <?php echo $restore_power[1];?> 点体力<br />
艺人: 
<?php if ($company['hire_num']): ?>
<a href="<?php echo linkwml('star', 'index');?>"><?php echo $company['hire_num'];?></a>
<?php else: ?>
<?php echo $company['hire_num'];?>
<?php endif;?>
/<?php echo $company['starlimits'];?>
<?php if ($company['hire_num'] != 0): ?>
(<?php echo $links;?>)<br />
<?php else: //没有招聘任何人?>
<br />
你一个艺人都没有, 快去<a href="<?php echo linkwml('recruit', 'candidates');?>">招聘</a>吧。<br />
<?php endif; ?>
<?php foreach ($stars as $star): ?>
>><a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a><br />
<?php if (!$star['jobinfo']['injob']): ?>
空闲中 <a href="<?php echo linkwml('job', 'arrange', array('id' => $star['id']));?>">安排工作</a><br />
<?php else: ?>
<?php if ($star['jobinfo']['expire']): ?>
完成 <?php echo $star['jobinfo']['jobname'];?><br />
<a href="<?php echo linkwml('job', 'complete', array('id' => $star['id'], 'jid' => $star['jobinfo']['jobid']));?>">验收工作</a><br />
<?php else: ?>
<a href="<?php echo linkwml('job', 'detail', array('id' => $star['jobinfo']['jobid']));?>"><?php echo $star['jobinfo']['jobname'];?></a> 中<br />
还有 <?php echo $star['jobinfo']['still'];?> <a href="<?php echo linkwml('prop', 'bag', array('type' => 1));?>">使用道具</a><br />
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if ($more_than_listed): ?>
<a href="<?php echo linkwml('star', 'index');?>">更多艺人</a><br />
<?php endif; ?>
<a href="">任务</a>
<a href="<?php echo linkwml('star', 'index');?>">艺人</a>
<a href="<?php echo linkwml('prop', 'bag');?>">背包</a>
<a href="<?php echo linkwml('index', 'honor');?>">成就</a>
<a href="<?php echo linkwml('index', 'street');?>">上街</a>
<a href="<?php echo linkwml('friends', 'index');?>">好友</a><br />
<?php if (isset($broadcast)): ?>
广播<br />
15:00 xxx: xxx
<?php endif; ?>
