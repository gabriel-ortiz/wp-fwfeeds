<?php

namespace FWF\Includes\APIHelpers;

use \Datetime;

/**
 * 
 * Set up defaults and run hooks and filters on setup
 * 
 */
 
 function setup(){
    
    $n = function( $function ){
        return __NAMESPACE__ . "\\$function";
    };
    
    //add_action( 'hook_action', $n('this_function') );
 }
 

// Date- time converting string
function fixDateTime($dateString){
    $date = new dateTime($dateString);
    return $date->format('g:i a') ;
}

//get Month only
function getMonthOnly($dateString){
    $date = new dateTime($dateString);
    return strtoupper( $date->format('M') );    
}

//get day only
function getDayOnly($dateString){
    $date = new dateTime($dateString); 
    return $date->format('d');
}

//Get Week Day
function getWeekDay($dateString){
    $date = new dateTime($dateString);
    return strtoupper( $date->format('D') );
}


//Get Month/Day
function getMonthDay($dateString){
    $date = new dateTime($dateString);
    return $date->format('m/d');
}

//test for slash at beginning of string, if so, then remove it
function removeSlash($url){
    $testUrl = substr($url, 0, 2);
    
    if( $testUrl == "//"){
        return substr($url, 2);
    }else{
        return $url;
    }
}

//test for featured image. If not there, insert default image
function testImage($featuredImg){
    if( $featuredImg != "" ){
        return $protocol . $featuredImg;
    }else{
        //return $protocol . $_SERVER['SERVER_NAME'] . '/FWFeeds/LYL/images/Mudd1.jpg';
        return FWF_ASSETS .'images/Mudd1.jpg';
    }
}

/**
 * 
 * Instragram helpers
 * 
 */
 function cleanHash($caption){
    //adds a special color to hashtags
    
    //strip tags
    $caption2 = strip_tags($caption2);
    
    //remove newlines
    $caption = str_replace(array("\r\n", "\n\r", "\r", "\n", "/[\n\<]/" ), " ", $caption);
    
    //turn string into array
    $wordArray = explode(' ', $caption);
    
    $processedArray = array();
    
    foreach($wordArray as $key => $value){
        if($value[0] == "#"){
            $processedArray[$key] = "<span style='color:#bdc0c7;'>". $value ."</span>";
            
        }else{
            $processedArray[$key] =  $value ;

        }//end of if
        

    }//end of foreach
    return $processedArray = implode(' ', $processedArray);

    
}//end of cleanHash()

function stringCleaner($caption){

        $caption = preg_replace('/(\'|&#0*39;)/', "'", $caption);    
        $caption = preg_replace('/(\'|&nbsp;)/', " ", $caption);         
        $caption = htmlspecialchars_decode($caption);
        $caption = strip_tags($caption);
        //$caption = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($caption))))));
        $caption = str_replace(array("\r\n", "\n\r", "\r", "\n", "/[\n\<]/" ), " ", $caption);

        return $caption;
}


function getExcerpt($str, $startPos=0, $maxLength=120) {
	if(strlen($str) > $maxLength) {
		$excerpt   = substr($str, $startPos, $maxLength-3);
		$lastSpace = strrpos($excerpt, ' ');
		$excerpt   = substr($excerpt, 0, $lastSpace);
		$excerpt  .= '...';
	} else {
		$excerpt = $str;
	}
	
	return $excerpt;
}
 