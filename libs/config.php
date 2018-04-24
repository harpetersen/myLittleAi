<?php

$GLOBALS['config'] = array();

/* paths */
$config['admin_dir'] = 'admin';

/* site url */
$config['domain'] = 'http://localhost';
$config['security'] = 'https://localhost';
$config['admin_root'] = '/admin';
$config['www_root'] = '';

$config['nanoportal_base_url'] = 'http://localhost/store';

/* database */
define('MYSQL_HOSTNAME', 'localhost');
define('MYSQL_USERNAME', 'admin');
define('MYSQL_PASSWORD', 'password');
define('MYSQL_DATABASE', 'db');
$config['db'] = 
	array(
		'hostname' => MYSQL_HOSTNAME,
		'username' => MYSQL_USERNAME,
		'password' => MYSQL_PASSWORD,
		'database' => MYSQL_DATABASE
	);




/* Definitions */
define('PHP_SUFFIX', '.php');
define('HTML_SUFFIX', '.html');
define('HTM_SUFFIX', '.htm');
