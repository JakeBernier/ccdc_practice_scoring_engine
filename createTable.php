<?php
// run this to create DB for ccdc practice scoring engine...
include "./include/db.php";
$user = sql::user;
$pass = sql::pass;
$server = sql::server;
$db = sql::db;
$con = mysqli_connect("$server","$user","$pass");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}
mysqli_select_db($con, "$db");

// create table
$serviceTable = "create table services(
uid INT NOT NULL AUTO_INCREMENT,
service VARCHAR(50) NOT NULL,
status VARCHAR(50) NOT NULL,
last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (uid)
);";

$create = mysqli_query($con, $serviceTable);

// insert services
$insertServices1 = "insert into services (service, status)
values ('http', 'down');";
$insertServices2 = "insert into services (service, status)
values ('https', 'down');";
$insertServices3 = "insert into services (service, status)
values ('ftp', 'down');";
$insertServices4 = "insert into services (service, status)
values ('dns', 'down');";
$insertServices5 = "insert into services (service, status)
values ('sql', 'down');";
$insertServices6 = "insert into services (service, status)
values ('pop3', 'down');";
$insertServices7 = "insert into services (service, status)
values ('ldap', 'down');";
$insertServices8 = "insert into services (service, status)
values ('generic', 'down');";

$insert1 = mysqli_query($con, $insertServices1);
$insert2 = mysqli_query($con, $insertServices2);
$insert3 = mysqli_query($con, $insertServices3);
$insert4 = mysqli_query($con, $insertServices4);
$insert5 = mysqli_query($con, $insertServices5);
$insert6 = mysqli_query($con, $insertServices6);
$insert7 = mysqli_query($con, $insertServices7);
$insert8 = mysqli_query($con, $insertServices8);

?>