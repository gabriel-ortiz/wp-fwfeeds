<?php

namespace FWF\Includes\LibCal;

use \FWF\Includes\APIHelpers as APIHelpers;

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
 
 /**
  * 
  * Helper function to get Access token to interact with LibCal API
  * 
  * @return string|\WP_Error will return access code or an error message
  * 
  */
  
  function get_libcal_token(){
      
      $libcal_settings          = get_option( 'fwf_options' );
      
      //var_dump($libcal_settings );
      
      $access_token             = get_transient( 'libcal_token_session' );
      
      $libcal_client_id         = $libcal_settings['libcal_client_id'];
      $libcal_client_secret     = $libcal_settings['libcal_client_secret'];
      
      if( ! $libcal_client_id || ! $libcal_client_secret ){
          return new \WP_Error('api_error', 'Missing API Settings');
      }
      
    if( ! $access_token){

		$token_request = wp_remote_post( 'https://api2.libcal.com/1.1/oauth/token', array(
    			'header' => array(),
    			'body'   => array(
    				'client_id'     => $libcal_client_id,
    				'client_secret' => $libcal_client_secret,
    				'grant_type'    => 'client_credentials'
    			)
    		) 
		);
		
		if( ! $token_request ){

		?>   
			<div class="notice notice-error is-dismissible">
				<p><strong><?php _e('Houston We have a problem', 'fwf'); ?></strong></p>
			</div>		   
		<?php    
		}else{
		    
			$token_request_data     = json_decode( $token_request['body'] );
			$access_token           = $token_request_data->access_token;
			// token is valid for an hour (3600 seconds), store in transient for 30 minutes
			set_transient( 'libcal_token_session', $access_token, 30 * MINUTE_IN_SECONDS );		    
		}
      

    }
      
    return $access_token;
  }
  
  /**
 * Fetch Events from LibCal
 *
 * @return array|string|\WP_Error
 */
function get_libcal_events( $cal_id ) {
    
    $params = array();
    
	//get access token
	$access_token = get_libcal_token();
	
	//$params['cal_id'] = 1889; // hardcoded calendar id for now
	//assign the LYL calendar if no arguments are passed in
	$params['cal_id'] = $cal_id ?: 1889;
	
	//set up results array
	$ReadyForFW = array();

	
	// turn array/object into query string
	$params = http_build_query( $params );
	
	
	$request = wp_remote_get( '	https://api2.libcal.com/1.1/events?' . $params , array(
		'headers' => array(
			'Authorization' => 'Bearer '. $access_token
		),
		'body'   => array(),
		'debug'  => true
	) );
	
	//var_dump($request);
	$events = json_decode( $request['body'], true) ;
	$events = $events['events'];	
	
	if ( $request['response']['code'] !== (int)200 || empty( $events ) ) {
		//return $request->get_error_message();
		$ReadyForFW['title']			= 'No upcoming events';
		$ReadyForFW['featured_img']		= FWF_IMAGES_CCL;
		
		return $ReadyForFW;
		
	} else {

	
		$headerData = array();
		
		$headerData['response']			= $request['response']['code'];
		$headerData['header-expires']	= $request['headers']['expires'];
		$headerData['access_token']		= get_transient('libcal_token_session');
		
		array_push($ReadyForFW, $headerData);
	
	    foreach($events as $event){
	        $LYL_FW = array();
	            $LYL_FW['title']            = $event['title'];
	            $LYL_FW['featured_img']     = APIHelpers\testImage( $event['featured_image'] );
	            $LYL_FW['location']         = $event['location']['name'];
	            $LYL_FW['start']            = APIHelpers\fixDateTime( $event['start'] );
	            $LYL_FW['end']              = APIHelpers\fixDateTime( $event['end'] );
	            $LYL_FW['weekday']          = APIHelpers\getWeekDay( $event['start'] );
	            $LYL_FW['Month_Day']        = APIHelpers\getMonthDay( $event['start'] );
	            $LYL_FW['month']            = APIHelpers\getMonthOnly( $event['start'] );
	            $LYL_FW['day']              = APIHelpers\getDayOnly( $event['start'] );
	            $LYL_FW['description']      = APIHelpers\stringCleaner( $event['description'] );
	        
	        //add results to ready for FW array    
	        array_push($ReadyForFW, $LYL_FW);
	    }   

	}
	
	//return $request;
	return $ReadyForFW;

}