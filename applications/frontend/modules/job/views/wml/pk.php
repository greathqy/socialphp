<?php if ($needpk): ?>
想得到这个工作的人很多, 需要你艺人证明实力。<br />
<a href="<?php echo linkwml('star', 'stardetail', array('id' => $mystarinfo['id']));?>"><?php echo $mystarinfo['name'];?></a><br />
<?php if ($jobinfo['constraint'] == 'charm'): ?>
魅力
<?php elseif ($jobinfo['constraint'] == 'acting'): ?>
演技
<?php elseif ($jobinfo['constraint'] == 'sing'): ?>
歌艺
<?php endif; ?>
<?php echo $mystarinfo['attrs'][$jobinfo['constraint']];?><br />
信心: <?php echo $mystarinfo['attrs']['confidence'];?>/<?php echo $mystarinfo['confidence'];?> <a href="<?php echo linkwml('star', 'useprop');?>">使用道具</a><br />
挑战一次需要<?php echo $struggle_points;?>信心<br />
<?php foreach($players as $player): ?>
><a href="<?php echo linkwml('job', 'pkerstat', array('jid' => $jobid, 'sid' => $mystarinfo['id'], 'vcid' => $player['cid'], 'vsid' => $player['sid']));?>"><?php echo $player['star']['name'];?></a><br />
<a href="<?php echo linkwml('job', 'pkresult', array('jid' => $jobid, 'sid' => $mystarinfo['id'], 'vcid' => $player['cid'], 'vsid' => $player['sid']));?>">挑战<?php echo $player['star']['sex_text'];?></a><br />
<?php endforeach; ?>
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
<?php if (isset($nomoney)): ?>
你的艺人还不满足条件。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
你旗下的艺人<a href="<?php echo linkwml('star', 'stardetail', array('id' => $mystarinfo['id']));?>"><?php echo $mystarinfo['name'];?></a>获得了这份工作, 他已经开始工作了。<br />
剩余时间: <?php echo $expiretime;?><br /><br />
请选择<br />
<a href="<?php echo linkwml('job', 'index', array('type' => $company_type));?>">继续看工作</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a><br />
<?php endif; ?>
<?php endif; ?>
