<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'AMSHOP_CONNECTER_VERSION' ) ) {
	exit; // Exit if accessed directly
}

global $amshop_connecter_debug_log_version;

$amshop_connecter_debug_log_version = '0.0.1';



if ( !function_exists( 'amshop_connecter_debug_message' ) ) {
    function amshop_connecter_debug_message( $message ) {
	    if(is_array($message)) { 
	        $message = json_encode($message); 
	    } 
	    $file = fopen("../amshopConnect_logs.log","a"); 
	    echo fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message); 
	    fclose($file);
	}
}

