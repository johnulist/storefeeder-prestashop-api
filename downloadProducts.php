<?php
/*
 * IMPORTANT: DO NOT CHANGE ANYTHING IN THIS FILE. 
 * 
 * THE ONLY FILES THAT REQUIRE CHANGES TO INTEGRATE WITH STOREFEEEDER ARE:
 * database.connect.inc.php WHERE YOU HAVE TO ENTER THE DATABASE LOGIN DETAILS
 * query.inc.php WHER YOU NEED TO ALTER THE QUERIES TO WORK WITH YOUR SYSTEM
 */

//enable error reporting - only for testing
//ini_set("display_errors", "1");
//ERROR_REPORTING(E_ALL);

//for live environment - disable error reporting
ini_set("display_errors", "0");

//this file includes functions that are used in the code
require_once 'functions.inc.php';

setContentTypeAndSendPreamble();

//this file contains code that verifies is the api key is correct
require_once 'authenticate.inc.php';

//this file includes code that connects to mysql
require_once 'database.connect.inc.php';


try {
    $output = '<response>' . "\n";

    $output .= '<status>' . "\n";
    $output .= "<response_code>200</response_code>\n";
    $output .= "<message>OK</message>\n";
    $output .= '</status>' . "\n";

    //run the inculde file to set the $ordersQuery variable
    require 'query.inc.php';
    $products = mysql_query($productDownloadQuery);

    $output .= '<products>' . "\n";

    //proces orders
    while ($product = mysql_fetch_array($products)) {      
        $output .= '<product>' . "\n";
        
        $image_id = $product['image_url'];
        $url = "http://www.nosoloposters.com/img/p/";
        $folder_path = implode("/", str_split($image_id, 1));
        $final_url = $url . $folder_path . "/" . $image_id . ".jpg";
        $product['image_url'] = $final_url;
        $product['website_url'] = "http://www.nosoloposters.com/".$product['website_url'].".html";

        serializeToXML($output, $product);
        
        $output .= '</product>' . "\n";
    }

    //close mysql connection
    mysql_close($con);

    //close tags
    $output .= '</products>' . "\n";
    $output .= '</response>' . "\n";
} catch (Exception $ex) {
    //there was a problem during processing - send error response
    //quit processing to make sure that the original response is not sent
    sendErrorResponse(123, $ex->getMessage());
    exit();
}

//no errors during processing so send the output
echo $output;
?>