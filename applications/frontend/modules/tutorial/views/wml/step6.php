<?php if (isset($changed)): ?>
你成功将<?php echo $old_name;?>改名为<?php echo $star['name'];?><br />
<?php $this->include_partial('_prize_alert');?>
<br />
<a href="<?php echo linkwml('tutorial', 'step7');?>">艺人列表</a>
<?php else: ?>
你想给<?php echo $star['name'];?>取个好听的名字吧？<br />
<input name="name" size="15" value="<?php echo $this->setField('name', $star['name']);?>" /><anchor title="改名"><go href="<?php echo linkwml('tutorial', 'step6');?>" method="post" sendreferer="false"><postfield name="name" value="$(name)" /></go>修改</anchor><br/>
<?php endif; ?>
