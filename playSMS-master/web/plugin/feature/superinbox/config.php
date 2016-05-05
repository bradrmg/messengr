<?php
defined('_SECURE_') or die('Forbidden');

$menutab = $core_config['menutab']['my_account'];
$menu_config[$menutab][] = array(
	//'index.php?app=main&inc=feature_phonebook&op=phonebook_list',
	'index.php?app=main&inc=feature_superinbox&op=super_inbox',
	_('Super Inbox'),
	2 
);

