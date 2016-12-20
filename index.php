<html>
<h1 style="font-weight: bold; text-align: center;">CCDC Practice Scoring Engine</h1>
<style>
nav {display block;background-color:#4CAF50;padding:0.5em;color: white; font-size: large}
nav a {background-color:#4CAF50;color:#fff;padding:0.2em;text-decoration:none;}
nav a:hover {background-color:#006400;padding:0.5em 0.2em 0.5em 0.2em}
</style>
<style>
table {
    border-collapse: collapse;
    margin: 0px auto;
}
table, th, td {
    border: 1px solid black;
    padding: 7px;
}
tr:nth-child(even) {background-color: #f2f2f2}
th {
    background-color: #4CAF50;
    color: white;
}
</style>
<meta http-equiv="refresh" content="5">
<table>
<tr>
	<th>Service</th>
	<th>Status</th>
	<th>Last Checked</th>
</tr>
<?php
// status page - refreshes every 5 seconds... queries DB to find out status of services....
// refresh also runs all the php service scripts...

// errors for debugging
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$config = include('./include/config.php');
// connect to db
include "./include/db.php";
$user = sql::user;
$pass = sql::pass;
$server = sql::server;
$db = sql::db;
$table = sql::table;
$con = mysqli_connect("$server","$user","$pass");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}
mysqli_select_db($con, $db);
$query = 'select * from '. $table;
$result = mysqli_query($con, $query);

// HTTP scoring engine
$output = DownloadUrl($config['http']['URL']);
$md5 = md5($output);

// If the hashes match, update the database..
if ($md5 == $config['http']['hash']){
	$http_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'http';";
	$http_db = mysqli_query($con, $http_update);
} else {
	$http_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'http';";
	$http_db = mysqli_query($con, $http_update);
}

function DownloadUrl($Url){
	// is curl installed?
	if (!function_exists('curl_init')){
		die('CURL is not installed! - on ubuntu try: sudo apt-get install php-curl');
	}
	// create a new curl resource
	$ch = curl_init();
	// set URL to download
	curl_setopt($ch, CURLOPT_URL, $Url);
	// remove header? 0 = yes, 1 = no
	curl_setopt($ch, CURLOPT_HEADER, 0);
	// don't verify the cert
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	// should curl return or print the data? true = return, false = print
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// timeout in seconds
	curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	// download the given URL, and return output
	$output = curl_exec($ch);
	// close the curl resource, and free system resources
	curl_close($ch);
	// print output
	return $output;
}

// HTTPS scoring engine
// uses same downloadUrl function as http...
$httpsoutput = DownloadUrl($config['https']['URL']);
$httpsmd5 = md5($httpsoutput);

// If the hashes match, update the database..
if ($httpsmd5 == $config['https']['hash']){
	$https_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'https';";
	$https_db = mysqli_query($con, $https_update);
} else {
	$https_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'https';";
	$https_db = mysqli_query($con, $https_update);
}

// ftp scoring engine
// Attempt to connect to the FTP server
$ftpuser = $config['ftp']['user'];
$ftppass = $config['ftp']['pass'];
$ftpserver = $config['ftp']['server'];
$connection = ftp_connect($ftpserver,21,2);

if(ftp_login($connection,$ftpuser,$ftppass)) {
	$ftp_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'ftp';";
	$ftp_db = mysqli_query($con, $ftp_update);
} else {
	$ftp_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'ftp';";
	$ftp_db = mysqli_query($con, $ftp_update);
}
	 
// dns scoring engine
$record = $config['dns']['record'];
$ns = $config['dns']['server'];

if (fsockopen($ns, 53, $errno, $errstr, 2)) { //note this checks tcp for timeout
	$ip = `nslookup $record $ns`; // the backticks execute the command in the shell
	$ips = array();
	if(preg_match_all('/Address: ((?:\d{1,3}\.){3}\d{1,3})/', $ip, $match) > 0){
		$ips = $match[1];
	}
	// if anything in array matches expected, update DB
	foreach ($ips as $i) {
		if ($i == $config['dns']['expected'] ){
			$dns_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'dns';";
			$dns_db = mysqli_query($con, $dns_update);
		} else {
			$dns_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'dns';";
			$dns_db = mysqli_query($con, $dns_update);
		}
	}
} else {
	$dns_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'dns';";
	$dns_db = mysqli_query($con, $dns_update);
}

// sql scoring engine
$sqlserver = $config['sql']['server'];
$sqluser = $config['sql']['user'];
$sqlpass = $config['sql']['pass'];
$sqldb = $config['sql']['db'];
$query = $config['sql']['query'];

$output = TRYMYSQL($sqlserver, $sqluser, $sqlpass, $sqldb, $query);

if ($output == $config['sql']['expected']) {
	$sql_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'sql';";
	$sql_db = mysqli_query($con, $sql_update);
} else {
	$sql_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'sql';";
	$sql_db = mysqli_query($con, $sql_update);
}

function TRYMYSQL ($sqlserver, $sqluser, $sqlpass, $sqldb, $query) {
	$mysqli = mysqli_init();
	if (!$mysqli) {
		die('mysqli_init failed');
	} if (!$mysqli->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
		die('Setting MYSQLI_INIT_COMMAND failed');
	} if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2)) {
		die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}
	$mysqli->real_connect($sqlserver, $sqluser, $sqlpass);
	mysqli_select_db($mysqli, $sqldb);
	if($mysqli) {
		$res = mysqli_query($mysqli, $query);
		if($res) {
			$r = mysqli_fetch_array($res);
			return $r[0];
		} else {
			return;
		}
	} else {
		return;
	}
}

// pop3 scoring engine
// requires php-imap    apt-get install php-imap
$popserver = $config['pop3']['server'];
$popuser = $config['pop3']['user'];
$poppass = $config['pop3']['pass'];
$popssl = $config['pop3']['ssl'];
imap_timeout(IMAP_READTIMEOUT, 2);
imap_timeout(IMAP_OPENTIMEOUT, 2);
if ($popssl == "true") {
	if (imap_open("{".$popserver.":995/pop3/ssl/novalidate-cert}", $popuser, $poppass)) {
		$pop_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'pop3';";
		$pop_db = mysqli_query($con, $pop_update);
	} else {
		$pop_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'pop3';";
		$pop_db = mysqli_query($con, $pop_update);
	}
} else {
	if (imap_open("{".$popserver.":110/pop3}INBOX", $popuser, $poppass)) {
		$pop_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'pop3';";
		$pop_db = mysqli_query($con, $pop_update);
	} else {
		$pop_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'pop3';";
		$pop_db = mysqli_query($con, $pop_update);	
	}
}

// ldap scoring engine
// requires php-ldap      apt-get install php-ldap
$ldapuser = $config['ldap']['user'];
$ldappass = $config['ldap']['pass'];
$ldapserver = $config['ldap']['server'];

if (fsockopen($ldapserver, 389, $errno, $errstr, 2)) {
	$ldapcon = ldap_connect($ldapserver);
	if ($ldapcon) {
		$ldapbind = ldap_bind($ldapcon, $ldapuser, $ldappass);
		if ($ldapbind) {
			$ldap_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'ldap';";
			$ldap_db = mysqli_query($con, $ldap_update);
		} else {
			$ldap_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'ldap';";
			$ldap_db = mysqli_query($con, $ldap_update);
		}
	} else {
			$ldap_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'ldap';";
			$ldap_db = mysqli_query($con, $ldap_update);
	}
} else {
	$ldap_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'ldap';";
	$ldap_db = mysqli_query($con, $ldap_update);
}

// generic engine checks if specified port is open on specified server
$genserver = $config['generic']['server'];
$genport = $config['generic']['port'];
if (fsockopen($genserver,$genport, $errno, $errstr, 2)) {
	$gen_update = "update " . $table . " set status = 'UP', last_checked=(CURRENT_TIMESTAMP) where service = 'generic';";
	$gen_db = mysqli_query($con, $gen_update);
} else {
	$gen_update = "update " . $table . " set status = 'DOWN', last_checked=(CURRENT_TIMESTAMP) where service = 'generic';";
	$gen_db = mysqli_query($con, $gen_update);
}

// show service status from database...
echo "
<style>
table {
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid black;
    padding: 7px;
}
tr:nth-child(even) {background-color: #f2f2f2}
th {
    background-color: #4CAF50;
    color: white;
}
</style>	
";
while ($row = mysqli_fetch_array($result)) {
	$proto = $row[service];
	$stat = $row[status];
	$ts = $row[last_checked];
	echo "
	<tr>
	<td>" . $proto . "</td>
	<td>" . $stat . "</td>
	<td>" . $ts . "</td>
	</tr>
	";
}
?>
</table>
</html>