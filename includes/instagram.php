<?php

namespace FWF\Includes\Instagram;

use \FWF\Includes\APIHelpers as APIHelpers;
use WP_Error;

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
 
 function get_instagram_data( $insta_id ){

	//set up results array
	$ReadyForFW = array();
	
	//setup params array
	$params = array(
	    'access_token'  => '1451148979.96f0a9b.07baf93bb7884866acfe81a5fd7cb23b',
	    'count'         => '7'
	    );

	// turn array/object into query string
	$params = http_build_query( $params );	
	

//$instagram_URL = 'https://api.instagram.com/v1/users/1451148979/media/recent/?access_token=1451148979.96f0a9b.07baf93bb7884866acfe81a5fd7cb23b&count=5';	

    $instagram_URL = 'https://api.instagram.com/v1/users/'. $insta_id .'/media/recent/?'. $params;

	
	$request = wp_remote_get( $instagram_URL , array(
		'body'   => array(),
		'debug'  => true
	) );
	
    //check for error messages
    if($request['response']['code'] !== 200){

        //execute error placeholder code
            $instaFW = array();
                $instaFW['img']             = FWF_IMAGES_GEAR;
                $instaFW['caption']         = "More Instagram photos coming soon";
                $instaFW['InstaStatus']     = $request['meta']['code'];

            array_push($ReadyForFW, $instaFW);		
		
		return $ReadyForFW;
    }
    
    $instagram_posts = json_decode( $request['body'], true) ;

    //execute good JSON response
    foreach($instagram_posts['data'] as $post){
        $instaFW = array();
            $instaFW['profilePic']  = $post['user']['profile_picture'];
            $instaFW['img']         = $post['images']['standard_resolution']['url'];
            $instaFW['username']    = "@".$post['user']['username'];
            $instaFW['caption']     =  APIHelpers\getExcerpt( APIHelpers\stringCleaner($post['caption']['text']) ) ;
            
        array_push($ReadyForFW, $instaFW); 
    }
    
    //return $instagram_posts;
    //return $request;
    
    return $ReadyForFW;
    
 }