<?php if (isset($notmystar)): ?>
这个明星不是你的, 无法改名。
<?php else: ?>
<?php if (isset($nomoney)): ?>
你的宝石数不够，请<a href="">充值</a><br />
<?php endif; ?>
你想给<?php echo $star['name'];?>改什么名字?<br />
<input name="name" size="15" value="<?php echo $this->setField('name', $star['name']);?>" /><anchor title="改名"><go href="<?php echo linkwml('star', 'cname', array('id' => $star['id']));?>" method="post" sendreferer="false"><postfield name="name" value="$(name)" /></go>改名</anchor><br/>
<?php if ($star['cnamefree']): //用掉了免费改名机会?>
改名需要花费<?php echo $amount;?>个宝石。<a href="">充值</a><br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('star', 'index');?>">艺人列表</a><br />
<a href="<?php echo linkwml('index', 'index');?>">公司首页</a>
<?php endif; ?>
