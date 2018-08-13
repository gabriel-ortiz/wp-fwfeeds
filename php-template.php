<?php

namespace FWF\Title;

/**
 * 
 * Set up defaults and run hooks and filters on setup
 * 
 */
 
 //exit if file is called directly
if( ! defined('ABSPATH') ){
    exit;
}
 
 function setup(){
    
    $n = function( $function ){
        return __NAMESPACE__ . "\\$function";
    };
    
    //add_action( 'hook_action', $n('this_function') );
 }