<?php if (isset($created)): ?>
恭喜你，<?php echo $companyname;?> 正式成立。<br />
<?php $this->include_partial('_prize_alert');?>
<br />
由于你的公司名气还不大, 目前只可以招聘一名艺人，赶紧上街到<a href="<?php echo linkwml('tutorial', 'step2');?>">招聘中心</a>去吧。<br />
<?php else: ?>
<?php echo $nickname;?>，
欢迎来到明星之梦，在这里，你将化身为一个演艺经纪公司的老板，可以雇佣各种艺人，你需要不断培养他们，让他们努力成为影视歌三栖明星，并且拿到各项大奖，快开始你的明星之路吧。 首先，给你的公司取个响亮的名字吧:
<input name="companyname" size="15" value="<?php echo $this->setField('companyname', $companyname);?>" /><br />
<anchor title="确认"><go href="<?php echo linkwml('tutorial', 'step1');?>" method="post" sendreferer="false"><postfield name="companyname" value="$(companyname)" /></go>确认</anchor><br />
<?php endif; ?>
