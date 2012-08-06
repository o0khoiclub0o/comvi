<?php
	global $config;

	$config['static_url']	= '/comvi/templates';
	$config['sitename']		= 'Comvi';
	$config['template']		= 'default';
	$config['timezone']		= 'Asia/Bangkok';	// server timezone
	$config['language']		= 'en';
	//$config['force_ssl']	= false;

	// session config
	$config['secret']			= '0b1u7OGkfjQ1dlBn';
	$config['session']			= 0;				// if set this to 0, it maybe make some feature don't work
	$config['session_expire']	= 900;				// in seconds

	//plugin config
	$config['editor']		= 'tinymce';
	$config['list_limit']	= 20;
?>
