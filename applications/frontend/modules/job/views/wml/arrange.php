<?php if ($star['jobing']): ?>
<?php echo $star['name'];?>正在<?php echo $star['jobinfo']['jobname'];?>, 你还想让<?php echo $star['sex_text'];?>打工？想累死<?php echo $star['sex_text'];?>吗？
<?php else: ?>
你想给<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a>安排什么工作? <br />
<a href="<?php echo linkwml('job', 'index', array('type'=>1));?>">广告公司</a><br />
<a href="<?php echo linkwml('job', 'index', array('type'=>2));?>">唱片公司</a><br />
<a href="<?php echo linkwml('job', 'index', array('type'=>3));?>">影视公司</a><br />
<?php endif; ?>

<br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('star', 'index');?>">艺人列表</a><br />
<a href="<?php echo linkwml('index', 'index');?>">公司首页</a>
