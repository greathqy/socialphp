<?php if($friendstotal > 0):?>
好友: <?php echo $friendstotal;?>人<br />
<?php foreach($friendsList as $fid => $friend):?>
>><a href="<?php echo linkwml('friends', 'detail',array('fid'=>$friend['uid']));?>" ><?php echo $friend['company_name'];?></a> (LV<?php echo $friend['company_level']; ?>)<br />
老板: <?php echo $friend['boss_name'];?>
<?php endforeach;?>
<?php else:?>
您还没有好友,现在就去邀请一个好友<br />
<?php endif;?>
<br />
<br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
