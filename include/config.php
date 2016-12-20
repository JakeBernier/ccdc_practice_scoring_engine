<?php
// service scoring configuration

return array(
		'http' => array(
				'URL' => 'http://192.168.2.13/jake.html',
				'hash' => '72c781843ae9015b567c08467607bb1a',
		),
		'https' => array(
				'URL' => 'https://192.168.1.2/about',
				'hash' => 'c8c63d57568b525346a84ca606efc13c',
		),
		'ftp' => array(
				'server' => '192.168.4.5',
				'user' => 'ubuntu',
				'pass' => 'ubuntu',
		),
		'dns' => array(
				'server' => '192.168.3.4',
				'record' => 'foo.bar.com',
				'expected' => '192.168.2.2',
		),
		'sql' => array(
				'server' => '192.168.2.11',
				'user' => 'root',
				'pass' => 'root',
				'db' => 'testdb',
				'query' => 'select data from test limit 1',
				'expected' => 'foobar',
		),
		'pop3' => array(
				'server' => '19.168.1.7',
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
				'server' => '192.168.5.7',
				'port' => '8080',
		),
);
?>
