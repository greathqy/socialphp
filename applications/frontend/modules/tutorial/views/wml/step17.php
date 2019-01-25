你想给<?php echo $star['name'];?>安排什么工作?<br />
<?php echo $star['name'];?>是<?php echo $star['type_text'];?>的, 建议去<?php echo $company_name;?>看看。<br />
<?php if ($company_type == 'ads'): ?>
<a href="<?php echo linkwml('tutorial', 'step18');?>">广告公司</a>
<?php else: ?>
广告公司
<?php endif; ?>
<br />
<?php if ($company_type == 'acting'): ?>
<a href="<?php echo linkwml('tutorial', 'step18');?>">影视公司</a>
<?php else: ?>
影视公司
<?php endif; ?>
<br />
<?php if ($company_type == 'sing'): ?>
<a href="<?php echo linkwml('tutorial', 'step18');?>">唱片公司</a>
<?php else: ?>
唱片公司
<?php endif; ?>
