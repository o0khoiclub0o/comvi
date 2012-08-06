<?php
	define('IN_COMVI', true);

	$app = 'site';

	require 'defines.php';
	require 'import.php';

	$classname = 'C'.ucfirst($app);
	$app = new $classname();

	// Execute the application.
	$app->execute();
?>