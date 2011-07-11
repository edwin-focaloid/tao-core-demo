<?php
require_once '../install/init.php';

new tao_scripts_TaoInstall(array(
	
	'parameters' => array(
		array(
			'name' 			=> 'db_driver',
			'type' 			=> 'string',
			'shortcut'		=> 'd',
			'description'	=> 'Target available sgbd : postgres7, mysql'
		),
		array(
			'name' 			=> 'db_host',
			'type' 			=> 'string',
			'shortcut'		=> 'h',
			'description'	=> 'Database location'
		),
		array(
			'name'			=> 'db_name',
			'type' 			=> 'string',
			'shortcut'		=> 'n',
			'description'	=> 'The Database name corresponds to the Module name.'
		),
		array(
			'name'			=> 'db_pass',
			'type' 			=> 'string',
			'shortcut'		=> 'p',
			'description'	=> 'Password to access to the database'
		),
		array(
			'name'			=> 'db_user',
			'type' 			=> 'string',
			'shortcut'		=> 'u',
			'description'	=> 'Login to access to the database'
		),
		array(
			'name'			=> 'install_sent',
			'type' 			=> 'integer',
			'shortcut'		=> 'u',
			'description'	=> ''
		),
		array(
			'name'			=> 'module_host',
			'type' 			=> 'string',
			'shortcut'		=> 'm',
			'description'	=> 'The host will be used in the module namespace http://HOST/module name.rdf#).It must not be necessarily the host name of your web server.'
		),
		array(
			'name'			=> 'module_lang',
			'type' 			=> 'string',
			'shortcut'		=> 'l',
			'description'	=> 'The default language will be used when the language parameters are not specified for the graphical interface and the data.'
		),
		array(
			'name'			=> 'module_mode',
			'type' 			=> 'string',
			'description'	=> 'The deployment mode allow and deny access to resources regarding the needs of the pltaform.The test & development mode will enables the debugs tools, the unit tests, and the access to all the resources.the production mode is focused on the security and allow only the required resources to run TAO.'
		),
		array(
			'name'			=> 'module_name',
			'type' 			=> 'string',
			'description'	=> 'The name of the module will be used to identifiate this instance of TAO from the others. The module name will be used as the database name and is the suffix of the module namespace (http://host/MODULE NAME.rdf#).'
		),
		array(
			'name'			=> 'module_namespace',
			'type' 			=> 'string',
			'description'	=> 'The module\'s namespace will be used to identify the data stored by your module. Each data collected by tao is identified uniquely by an URI composed by the module namespace followed by the resource identifier (NAMESPACE#resource)'
		),
		array(
			'name'			=> 'module_url',
			'type' 			=> 'string',
			'description'	=> 'The URL to access the module from a web browser.'
		),
		array(
			'name'			=> 'user_login',
			'type' 			=> 'string',
			'description'	=> 'The login of the tao backend user'
		),
		array(
			'name'			=> 'user_pass',
			'type' 			=> 'string',
			'description'	=> 'The password of the tao backend user'
		)
	)
));
?>