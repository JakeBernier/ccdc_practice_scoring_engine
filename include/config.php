<?php
// service scoring configuration

return array(
		'http' => array(
				'URL' => 'http://127.0.0.1/jake.html',
				'hash' => '72c781843ae9015b567c08467607bb1a',
		),
		'https' => array(
				'URL' => 'https://century.edu/about',
				'hash' => 'c8c63d57568b525346a84ca606efc13c',
		),
		'ftp' => array(
				'server' => '127.0.0.1',
				'user' => 'ubuntu',
				'pass' => 'ubuntu',
		),
		'dns' => array(
				'server' => '8.8.8.8',
				'record' => 'mail3.r1ddl3r.com',
				'expected' => '35.166.80.254',
		),
		'sql' => array(
				'server' => '127.0.0.1',
				'user' => 'root',
				'pass' => 'root',
				'db' => 'testdb',
				'query' => 'select data from test limit 1',
				'expected' => 'foobar',
		),
		'pop3' => array(
				'server' => '127.0.0.1',
				'user' => 'ubuntu',
				'pass' => 'ubuntu',
				'ssl' => 'true', // true or false
		),
		'ldap' => array(
				'server' => '192.168.1.189',
				'user' => 'bomb\administrator', // include domain - domain\user
				'pass' => 'p@ssw0rd',
		),
		'generic' => array(
				'server' => '127.0.0.1',
				'port' => '80',
		),
);
?>