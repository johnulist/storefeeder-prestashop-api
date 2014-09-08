<?
/*
 * THE CODE IN THIS FILE IS RESPONSIBLE FOR CONNECTING TO YOUR MYSQL DATABASE
 * PLEASE SET THE VARIABLES BELOW AND DO NOT EDIT THE CODE BELOW.
 */

$db_host 		= 'db-host';
$db_user 		= 'db-user';
$db_password 	= 'db-password';
$db_name 		= 'db-name';
$db_uses_UTF 	= true;


/*
 * DO NOT EDIT THE CONTENTS OF THE FILE BEYOND THIS POINT.
 */

//require functions, as we will need to send an error response if there are problems with connecting to the database
require_once 'functions.inc.php';

$con = mysql_connect($db_host, $db_user, $db_password);

if($db_uses_UTF){
    mysql_set_charset('utf8',$con); 
}

if (!$con) {
    sendErrorResponse(999, 'Could not connect: ' . mysql_error());
    exit();
 }

mysql_select_db($db_name, $con);

?>