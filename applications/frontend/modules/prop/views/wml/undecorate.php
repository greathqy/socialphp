<?php if (isset($undecorated)): ?>
成功卸下<?php echo $propinfo['name'];?><br />
<a href="<?php echo linkwml('star', 'de', array('id' => $propinfo['star_id']));?>">继续</a>
<?php else: ?>
你确定要卸下<?php echo $propinfo['name'];?>吗？<br />
<a href="<?php echo linkwml('prop', 'undecorate', array('id' => $propinfo['de_id'], 'confirm' => 1));?>">卸下</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
