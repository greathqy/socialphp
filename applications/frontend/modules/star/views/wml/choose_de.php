<?php echo $star['name'];?>的服饰<br />
<?php if ($pos == 'top'): ?>
上装|
<?php else: ?>
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'top'));?>">上装</a>|
<?php endif; ?>
<?php if ($pos == 'bottom'): ?>
下装|
<?php else: ?>
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'bottom'));?>">下装</a>|
<?php endif; ?>
<?php if ($pos == 'd1'): ?>
配饰1|
<?php else: ?>
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'd1'));?>">配饰1</a>|
<?php endif; ?>
<?php if ($pos == 'd2'): ?>
配饰2
<?php else: ?>
<a href="<?php echo linkwml('star', 'choose_de', array('id' => $star['id'], 'pos' => 'd2'));?>">配饰2</a>
<?php endif; ?>
<br />
<?php if (!$props): ?>
没有合适的服饰，去<a href="<?php echo linkwml('prop', 'clothes');?>">服饰店</a>购买吧。<br />
<?php else: ?>
<?php foreach ($props as $prop): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $prop['psid']));?>"><?php echo $prop['prop']['name'];?></a>
<?php if ($prop['sid']): ?>
(<a href="<?php echo linkwml('prop', 'undecorate', array('id' => $prop['psid']));?>">卸</a>)<br />
<?php else: ?>
(<a href="<?php echo linkwml('prop', 'do_decorate', array('pid' => $prop['psid'], 'sid' => $star['id'], 'pos' => $pos, 'redirect' => 1));?>">装</a>)<br />
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
<br />
<?php echo $pagination;?>
