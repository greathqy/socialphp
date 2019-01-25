<?php echo $job['name'];?><br />
要求:<br />
艺人<?php if ($job['constraint'] == 'charm'): ?>
魅力
<?php elseif ($job['constraint'] == 'acting'): ?>
演技
<?php elseif ($job['constraint'] == 'sing'): ?>
歌艺
<?php endif; ?>
: <?php echo $job['constraints'];?><br />
内容:<br />
工作时间: <?php echo $job['time'];?>分钟<br />
艺人名气: +<?php echo $job['star_fame'];?><br />
公司名气: +<?php echo $job['company_fame'];?><br />
公司收入: +<?php echo $job['company_cash'];?>G<br />
请选择你的艺人<br />
<?php if ($stars): ?>
<?php foreach ($stars as $star): ?>
<a href="<?php echo linkwml('job', 'pk', array('type' => $company_type, 'jid' => $job_id, 'sid' => $star['id']));?>"><?php echo $star['name'];?></a><br />
<?php if ($job['constraint'] == 'charm'): ?>
魅力
<?php elseif ($job['constraint'] == 'acting'): ?>
演技
<?php elseif ($job['constraint'] == 'sing'): ?>
歌艺
<?php endif; ?>
: <?php echo $star['attrs'][$job['constraint']];?>
<?php if ($star["{$job['constraint']}_plus"]): ?>
(+<?php echo $star["{$job['constraint']}_plus"];?>)
<?php endif; ?>
<br />
信心: <?php echo $star['attrs']['confidence'];?>/<?php echo $star['confidence'];?>
&nbsp;&nbsp;<a href="<?php echo linkwml('star', 'feed', array('id' => $star['id']));?>">使用道具</a><br />
<?php endforeach; ?>
<?php else: ?>
暂无空闲艺人<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
