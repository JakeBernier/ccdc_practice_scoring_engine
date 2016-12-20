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
// create database
$createDB = "create database ccdc;";
$do = mysqli_query($con, $createDB);
mysqli_close($con);
echo "Created DB... done!";
?>