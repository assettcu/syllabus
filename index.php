<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../compass.colorado.edu/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

$https = (@$_SERVER["HTTPS"]=="on") ? "https" : "http";
$library = ($_SERVER["SERVER_NAME"]=="assettdev.colorado.edu") ? "assettdev.colorado.edu/libraries" : "compass.colorado.edu/libraries";

defined('HTTPS') or define("HTTPS",$https);
defined('LIBRARY_DIRECTORY') or define("LIBRARY_DIRECTORY",$library);

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
