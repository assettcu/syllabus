<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
$mainconfig = array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Syllabus Archive',

	// preloading 'log' component
	'preload'=>array('log'),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		'session'=>array(
			'autoStart'=>true,
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				'<id:\d+>'=>'site/view',
				'<action:\w+>/<id:\d+>'=>'site/<action>',
				'<action:\w+>'=>'site/<action>',
				'archive/<name:\w+>'=>'archive/<name>'
			),
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				array(
					'class'=>'CWebLogRoute',
				),
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'syllabus_dir'        => 'C:/web/assettdev.colorado.edu/syllabus/archive/',
        'JQUERY_VERSION'      => '2.1.1',
        'JQUERYUI_VERSION'    => '1.11.0'
	),
);


# Function to blend two arrays together
function mergeArray($a,$b)
{
    $args=func_get_args();
    $res=array_shift($args);
    while(!empty($args))
    {
        $next=array_shift($args);
        foreach($next as $k => $v)
        {
            if(is_integer($k))
                isset($res[$k]) ? $res[]=$v : $res[$k]=$v;
            else if(is_array($v) && isset($res[$k]) && is_array($res[$k]))
                $res[$k]=mergeArray($res[$k],$v);
            else
                $res[$k]=$v;
        }
    }
    return $res;
}

# If extended attributes are found, include them in the main configuration details
if(is_file(dirname(__FILE__).'/main-ext.php')) {
    $mainconfig = mergeArray($mainconfig, require(dirname(__FILE__).'/main-ext.php'));
}

# Return the details
return $mainconfig;