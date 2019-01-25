<?php echo $star['name'];?><br />
<a href="<?php echo linkwml('index', 'cvisit', array('id' => $company['id']));?>"><?php echo $company['name'];?></a><br />
性别: <?php echo $star['star_type'];?><br />
<?php if(!isset($nostruggle) || !$nostruggle): ?>
<a href="<?php echo linkwml('job', 'pkresult', array('sid' => $sid, 'vsid' => $vsid, 'jid' => $jid));?>">挑战<?php echo $star['star_text'];?></a><br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
