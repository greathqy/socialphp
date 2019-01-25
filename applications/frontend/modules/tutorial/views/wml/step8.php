<?php echo $star['name'];?> <br /> 
<?php echo $star['type_text'];?> (<a href="<?php echo linkwml('tutorial', 'step4_levelintro', array('level' => $star['talent_text']));?>"><?php echo $star['talent_text'];?>级</a>)<br />
<?php if ($star['jobinfo']['injob']): ?>
<?php if ($star['jobinfo']['expire']): ?>
完成 <?php echo $star['jobinfo']['jobname'];?><br />
<a href="<?php echo linkwml('job', 'complete', array('sid' => $star['id'], 'jid' => $star['jobinfo']['jobid']));?>">验收工作</a><br />
<?php else: ?>
在 <a href=""><?php echo $star['jobinfo']['jobname'];?></a>中<br />
还有 <?php echo $star['jobinfo']['still'];?> <a href="<?php echo linkwml('star', 'consume', array('id' => $star['id']));?>">使用道具</a><br />
<?php endif; ?>
<?php else: ?>
空闲中 <br />
<?php endif; ?>
性别: <?php echo $star['sex_text'];?><br />
名气: Lv<?php echo $star['level'];?> (<?php echo $star['upgradeinfo']['my'];?>/<?php echo $star['upgradeinfo']['need'];?>)<br />
魅力: <?php echo $star['attrs']['charm'];?>
<?php if ($star['charm_plus']): ?>
(+<?php echo $star['charm_plus'];?>)
<?php endif; ?>
<br />
歌艺: <?php echo $star['attrs']['sing'];?>
<?php if ($star['sing_plus']): ?>
(+<?php echo $star['sing_plus'];?>)
<?php endif; ?>
<br />
演技: <?php echo $star['attrs']['acting'];?>
<?php if ($star['acting_plus']): ?>
(+<?php echo $star['acting_plus'];?>)
<?php endif;?>
<br />
自信: <?php echo $star['attrs']['confidence'];?>/<?php echo $star['level_limit'];?> 
<?php if ($star['attrs']['confidence'] < $star['level_limit']): ?>
<a href="<?php echo linkwml('star', 'recover', array('id' => $star['id']));?>">使用道具</a>
<?php endif; ?>
<br />
<?php echo $confidence_restore[0];?>分钟恢复<?php echo $confidence_restore[1];?>点自信<br />
<a href="<?php echo linkwml('tutorial', 'step9', array('id' => $star['id']));?>">培训</a>
