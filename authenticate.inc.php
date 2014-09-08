<?php
/*
 * IMPORTANT: DO NOT CHANGE ANYTHING IN THIS FILE. 
 * 
 * THE ONLY FILES THAT REQUIRE CHANGES TO INTEGRATE WITH STOREFEEEDER ARE:
 * database.connect.inc.php WHERE YOU HAVE TO ENTER THE DATABASE LOGIN DETAILS
 * query.inc.php WHER YOU NEED TO ALTER THE QUERIES TO WORK WITH YOUR SYSTEM
 */

/*
 * This is the security api key used to access your shopping cart system's
 * order data and also to issue shipping/inventory updates.
 * 
 * It is unique and assinged to you by StoreFeeder. This is known only to 
 * StoreFeeder so nobody else will be able to connect to your store and access
 * the information.
 * 
 * Plase do not change the token as it will cause your integration to stop working.
 */
$token = 'your-api-token-here';

//get the token from the api request
if(isset($_GET['token']))
    $req_token = $_GET['token'];
else
    $req_token = '';

if ($token != $req_token) {
    sendErrorResponse(401, 'Invalid API Token');
    exit();
}
?>