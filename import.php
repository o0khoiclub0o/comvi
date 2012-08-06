<?php

// Load the loader class.
if (!class_exists('CLoader')) {
	require PATH_LIBRARIES.'loader.php';
}

// Application
CLoader::import('application.'.$app);
?>