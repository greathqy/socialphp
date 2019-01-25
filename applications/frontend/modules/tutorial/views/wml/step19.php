<?php echo $job['name'];?><br />
要求:<br />
艺人
<?php if ($job['constraint'] == 'charm'): ?>
魅力:
<?php elseif ($job['constraint'] == 'sing'): ?>
歌艺:
<?php elseif ($job['constraint'] == 'acting'): ?>
演技:
<?php endif; ?>
<?php echo $job['constraints'];?><br />
内容:<br />
工作时间: <?php echo $job['time'];?>分钟<br />
艺人名气: +<?php echo $job['star_fame'];?><br />
公司名气: +<?php echo $job['company_fame'];?><br />
公司收入: +<?php echo $job['company_cash'];?><br />
请选择你的艺人<br />
<a href="<?php echo linkwml('tutorial', 'step20');?>"><?php echo $star['name'];?></a><br />
<?php if ($job['constraint'] == 'charm'): ?>
魅力:
<?php elseif ($job['constraint'] == 'sing'): ?>
歌艺:
<?php elseif ($job['constraint'] == 'acting'): ?>
演技:
<?php endif; ?>
<?php echo $star['attrs'][$job['constraint']];?>
