<?php echo $friendDetial['company_name'];?><br />
老板：<?php echo $friendDetial['boss_name'];?><br />
关系：<?php if($isFriend):?>好友<?php else:?>陌生人<?php endif;?><br />
<a href="<?php echo linkwml('friends','message',array('fid'=>$friendDetial['uid']));?>">留言</a>|
<a href="<?php echo linkwml('friends','addfriend',array('fid'=>$friendDetial['uid']));?>">加好友</a>|
<a href="<?php echo linkwml('friends','bestow',array('fid'=>$friendDetial['uid']));?>">送礼物</a><br />
<br />
艺人动态：<br />

<?php foreach ($stars as $star): ?>
<a href="<?php echo linkwml('friends', 'friendsStardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a><br />
<?php if (!$star['jobinfo']['injob']): ?>
空闲中 <br />
<?php else: ?>
<?php if ($star['jobinfo']['expire']): ?>
完成 <?php echo $star['jobinfo']['jobname'];?> 获利<?php echo $star['jobinfo']['gb_add'];?><br />
<a href="<?php echo linkwml('friends','steal',array('sid'=>$star['id']));?>">顺手牵羊</a><br />
<?php else: ?>
<?php echo $star['jobinfo']['jobname'];?> 中<br />
<a href="<?php echo linkwml('friends','wreck',array('type'=>'report'));?>">举报城管</a><br />
<a href="<?php echo linkwml('friends','bestow',array('type'=>'water'));?>">给他买水</a><br />
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
