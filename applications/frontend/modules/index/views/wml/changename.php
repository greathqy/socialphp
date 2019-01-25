<?php if (isset($changed)): ?>
公司改名成功。<br />
<a href="<?php echo linkwml('index', 'index');?>">返回公司</a>
<?php else: ?>
<?php if (isset($nomoney)): ?>
你的宝石不够无法改名，请<a href="">充值</a>。<br />
<?php endif; ?>
取个霸气的名字吧!<br />
<input type="text" size="15" name="companyname" value="<?php echo $this->setField('companyname', $company['name']);?>" />
<anchor title="改名"><go href="<?php echo linkwml('index', 'changename');?>" method="post" sendreferer="false"><postfield name="companyname" value="$(companyname)" /></go>修改</anchor><br />
改名需要花费<?php echo $changename_price;?>个宝石。<a href="">充值</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">公司首页</a>
<?php endif; ?>
