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
    $orders = mysql_query($ordersQuery);

    $output .= '<orders>' . "\n";

    //proces orders
    while ($order = mysql_fetch_array($orders)) {      
        $output .= '<order>' . "\n";

        //output the date in a standard format
        $order['order_date'] = date("Y-m-d H:i:s", strtotime($order['order_date']));
        serializeToXML($output, $order);

        //get order items
        $output .= '<order_items>' . "\n";
        
        //set $order_ref variable which is used to evaluate the query in the included file
        $explodedOrder = explode('-',$order['order_ref']);
        $orderRef=$explodedOrder[0];
        //run the inculde file to evaluate the $orderItemsQuery
        require 'query.inc.php';
        $order_items = mysql_query($orderItemsQuery);

        while ($item = mysql_fetch_array($order_items)) {
            $output .= '<order_item>' . "\n";
            serializeToXML($output, $item);
            $output .= '</order_item>' . "\n";
        }

        $output .= '</order_items>' . "\n";

        $output .= '</order>' . "\n";
    }

    //close mysql connection
    mysql_close($con);

    //close tags
    $output .= '</orders>' . "\n";
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