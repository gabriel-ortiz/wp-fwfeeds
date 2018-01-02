<?php

namespace FWF\Includes\Libguides;

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
 
   function get_libguide_token(){
      
      $libguide_settings          = get_option( 'fwf_options' );
      
      //var_dump($libguide_settings );
      
      $access_token             = get_transient( 'libguide_token_session' );
      
      $libguide_client_id         = $libguide_settings['libguide_access_token_client_id'];
      $libguide_client_secret     = $libguide_settings['libguide__access_token_client_secret'];
      
      if( ! $libguide_client_id || ! $libguide_client_secret ){
          return new \WP_Error('api_error', 'Missing API Settings');
      }
      
    if( ! $access_token){

		$token_request = wp_remote_post( 'https://lgapi-us.libapps.com/1.2/oauth/token', array(
    			'header' => array(),
    			'body'   => array(
    				'client_id'     => $libguide_client_id,
    				'client_secret' => $libguide_client_secret,
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
			set_transient( 'libguide_token_session', $access_token, 30 * MINUTE_IN_SECONDS );		    
		}
      

    }
      
    return $access_token;
  }
  

function get_libguide_assets($guide_id){
    
    
      $libguide_settings    = get_option( 'fwf_options' );    
      
      $libguide_site_id     = $libguide_settings['libguide_site_id'];
      $libguide_key         = $libguide_settings['libguide_key'];
      
      $params = array(
            'site_id'   => $libguide_site_id,
            'key'       => $libguide_key,
            'guide_ids' => $guide_id,
            'asset_types[]' => '5' 
          );
      
     $query_string = http_build_query( $params );
     
     //https://lgapi-us.libapps.com/1.1/assets
	$request = wp_remote_get( 'https://lgapi-us.libapps.com/1.1/assets?'. $query_string ); 
	
		$assets = json_decode( $request['body'], true) ;
	return $assets;
}


	