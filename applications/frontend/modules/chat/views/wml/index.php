咖啡屋<br />
<?php if (isset($chattoofast) && $chattoofast): ?>
您的发言速度过快,休息一下再发吧。<br />
<?php endif;?>
<input name="msg" size="15" value="" />
<anchor title="发言">
	<go href="<?php echo linkwml('chat', 'index');?>" method="post" sendreferer="false">
		<postfield name="msg" value="$(msg)" />
	</go>发言
</anchor><br />
<?php if (!$chatlist): ?>
还没有人发言。<br />
<?php else: ?>
<?php foreach ($chatlist as $chat): ?>
<?php echo $chat['nickname'];?> : <?php echo $chat['msg'];?><br />
<br />
<?php endforeach; ?>
<br />
<?php echo $pagination;?><br />
<?php endif; ?>

<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo $link_prev;?>">返回</a>