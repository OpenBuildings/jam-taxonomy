<?php

spl_autoload_register(function($class)
{
	$file = __DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.str_replace('_', '/', $class).'.php';

	if (is_file($file))
	{
		require_once $file;
	}
});

require_once __DIR__.'/../vendor/autoload.php';

Kohana::modules(array(
	'database'        => MODPATH.'database',
	'jam'             => __DIR__.'/../modules/jam',
	'jam-taxonomy' => __DIR__.'/..',
));

Kohana::$config
	->load('database')
		->set('default', array(
			'type'       => 'PDO',
			'connection' => array(
                'dsn' => 'mysql:host=localhost;dbname=test-jam-taxonomy',
				'hostname'   => 'localhost',
				'database'   => 'test-jam-taxonomy',
				'username'   => 'root',
				'password'   => '',
				'persistent' => TRUE,
			),
            'identifier'   => '`',
			'table_prefix' => '',
			'charset'      => 'utf8',
			'caching'      => FALSE,
		));
Kohana::$environment = Kohana::TESTING;
