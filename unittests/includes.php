<?
include_once(dirname(__file__) . "/config.php");

include_once(ZOOP_DIR . "/zoop.php");

$zoop = &new zoop(APP_DIR);

$zoop->addComponent('db');
$zoop->addComponent('gui');
$zoop->addComponent('guicontrol');
$zoop->addComponent('guiwidget');
$zoop->addComponent('simpletest');
$zoop->addComponent('pdf');
$zoop->addComponent('spell');
$zoop->addComponent('userfiles');
$zoop->addComponent('sequence');
$zoop->addComponent('forms');
$zoop->addComponent('mail');
$zoop->addComponent('cache');
$zoop->addComponent('nusoap');
$zoop->addComponent('cache');

$zoop->init();
