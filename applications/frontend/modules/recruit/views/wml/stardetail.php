招聘中心<br />
<?php if (isset($star)): ?>
>><?php echo $star['name']; ?><br />
级别: <?php echo $star['type_text'];?> (<a href="<?php echo linkwml('star', 'levelintro', array('level' => $star['talent_text']));?>"><?php echo $star['talent_text']?>级</a>)<br />
性别: <?php echo $star['sex_text'];?><br />
演技: <?php echo $attrs['acting'];?><br />
歌艺: <?php echo $attrs['sing'];?><br />
魅力: <?php echo $attrs['charm'];?><br />
身价: 1000<br />
<a href="<?php echo linkwml('recruit', 'hire', array('id' => $star['_seq']));?>">雇佣</a><br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">返回公司</a><br />
<?php endif ;?>
