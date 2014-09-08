<?php
/*
 * IMPORTANT: DO NOT CHANGE ANYTHING IN THIS FILE. 
 * 
 * THE ONLY FILES THAT REQUIRE CHANGES TO INTEGRATE WITH STOREFEEEDER ARE:
 * database.connect.inc.php WHERE YOU HAVE TO ENTER THE DATABASE LOGIN DETAILS
 * query.inc.php WHER YOU NEED TO ALTER THE QUERIES TO WORK WITH YOUR SYSTEM
 */

function serializeToXML(&$output, $array) {
    foreach ($array as $key => $value) {
        //turnes out that mysql functions return each row two times
        //once with a string index and once with an int index
        //we only need the string index, so skip the item if index is numeric
        if (!is_numeric($key))
            $output .= "<$key>".htmlspecialchars($value)."</$key>\n";
    }
}

function sendErrorResponse($errorCode, $message) {
    echo '<response>' . "\n";
    echo '<status>' . "\n";
    echo "<response_code>$errorCode</response_code>\n";
    echo "<message>$message</message>\n";
    echo '</status>' . "\n";
    echo '</response>' . "\n";
}

function setContentTypeAndSendPreamble(){
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
}

?>