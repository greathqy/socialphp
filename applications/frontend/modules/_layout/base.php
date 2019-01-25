<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>
<card id="<?php echo isset($__card_id__) ? $__card_id__ : 'main';?>" title="<?php echo isset($__title__) ? $__title__ : '明星之梦';?>">
<?php if (isset($__DEBUG_DATA__)): ?>
<?php echo $__DEBUG_DATA__;?>
<?php endif; ?>
<?php if (isset($msg) && $msg): ?>
<?php echo $msg;?><br />
<?php endif; ?>
<?php if (isset($errors)): ?>
<?php foreach ($errors as $fieldName => $error):?>
!!! <?php echo $error['text'];?><br />
<?php endforeach;?>
<?php endif; ?>
<?php include($this->templateDir . $this->template . '.php');?>
</card>
</wml>
