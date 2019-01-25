<?php if (isset($notmeet)): ?>
你的艺人还不满足条件。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php elseif (isset($noconfidence)): ?>
<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a> 信心不足, 请<a href="<?php echo linkwml('prop', 'bag');?>">使用道具</a>恢复，或者到<a href="<?php echo linkwml('shop', 'food');?>">商店购买</a>恢复道具。
<?php else: ?>
<?php if ($result): ?>
你旗下的艺人<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a>成功挑战<a href="<?php echo linkwml('job', 'pkerstat', array('jid' => $jid, 'sid' => $star['id'], 'vsid' => $vsstar['id'], 'vcid' => $cid, 'nostruggle' => 1));?>"><?php echo $vsstar['name'];?></a>, 获得了<?php echo $jobinfo['name'];?>这份工作, 他已经开始工作了。<br />
剩余时间: <?php echo $expire;?><br />
<br />
请选择<br />
<a href="<?php echo linkwml('job', 'index', array('type' => $company_type));?>">继续看工作</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php else: ?>
<?php if (isset($accept_discount)): ?>
你旗下的艺人<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a>获得了<?php echo $jobinfo['name'];?>这份工作, 他已经开始工作了。
剩余时间: <?php echo $expire;?><br />
<a href="<?php echo linkwml('job', 'index', array('type' => $company_type));?>">继续看工作</a><br />
<a href="<?php echo linkwml('index', 'street');?>">离开此地</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php else: ?>
你旗下的艺人<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a>挑战<a href="<?php echo linkwml('job', 'pkerstat', array('jid' => $jid, 'sid' => $star['id'], 'vsid' => $vsstar['id'], 'vcid' => $cid));?>"><?php echo $vsstar['name'];?></a>失败，看来你艺人水平还不行啊，那你能否接受<?php echo $percent;?>%的工资水平呢？<br />
<a href="<?php echo linkwml('job', 'pkresult', array('acceptdiscount'=>1, 'sid' => $star['id'], 'vsid' => $vsstar['id'], 'jid' => $jid, 'vcid' => $cid));?>">是，我愿意做。</a><br />
<a href="<?php echo linkwml('job', 'pk', array('type' => $company_type, 'jid' => $jid, 'sid' => $star['id']));?>">否，换人挑战。</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
