<?php
$curDir = getcwd();
include(dirname(dirname(dirname($curDir))) . DIRECTORY_SEPARATOR . 'startup.php');

$meditator = Factory::create('meditator');
$meditator->setDefaultRender('html')
          ->setRouter('http');
$options = array(
    'proj_dir' => SYS_ROOT,
    'app_name' => 'backend',
    );
$meditator->service($options);