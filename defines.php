<?php
if (!defined('DS'))	define('DS', DIRECTORY_SEPARATOR);
define('PATH_BASE', dirname(__FILE__).DS);
define('PATH_CONFIGURATION',	PATH_BASE.'config'.DS);
define('PATH_LIBRARIES',		PATH_BASE.'libs'.DS);
define('PATH_CONTROLLERS',		PATH_BASE.'mvc'.DS.'controllers'.DS);
define('PATH_MODELS',			PATH_BASE.'mvc'.DS.'models'.DS);
define('PATH_VIEWS',			PATH_BASE.'mvc'.DS.'views'.DS);
define('PATH_THEMES',			PATH_BASE.'templates'.DS);

error_reporting(E_ALL);
set_magic_quotes_runtime(0);
?>