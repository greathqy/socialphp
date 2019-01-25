<?php if (isset($created)): ?>
恭喜你，<?php echo $companyname;?> 正式成立了，你现在有一个艺人位，随着公司名气的提升，可以招募更多艺人。<br />
<anchor title="继续"><go href="<?php echo linkwml('index', 'index');?>"></go>继续</anchor>
<?php else: ?>
<?php echo $nickname;?>，
欢迎来到明星之梦，在这里，你将化身为一个演艺经纪公司的老板，可以雇佣各种艺人，你需要不断培养他们，让他们努力成为影视歌三栖明星，并且拿到各项大奖，快开始你的明星之路吧。 首先，给你的公司取个响亮的名字吧:
<input name="companyname" size="15" value="<?php echo $this->setField('companyname', $companyname);?>" /><br />
<anchor title="确认"><go href="<?php echo linkwml('index', 'first_changename');?>" method="post" sendreferer="false"><postfield name="companyname" value="$(companyname)" /></go>确认</anchor><br />
<?php endif; ?>
