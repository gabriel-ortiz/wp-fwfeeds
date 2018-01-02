<?php

namespace FWF\Admin\Settings\SectionSettings;

 // disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
	
}

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
    add_action( 'admin_init', $n( 'fwf_register_settings' ) );
 }
 




// register plugin settings
function fwf_register_settings() {
	
	register_setting( 
		$option_group = 'fwf_options', 
		$option_name  = 'fwf_options'
	); 
	
	
	add_settings_section( 
		$id         = 'fwf_section_libcal_1.0', 
		$title      = esc_html__('LibCal 1.0 Endpoint API Credentials', 'gp_wp'), 
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_section_libcal_api_keys', 
		$page       = 'fwf'
	);
	
	add_settings_section( 
		$id         = 'fwf_section_libcal_1.1', 
		$title      = esc_html__('LibCal 1.1 Endpoint Access Token Credentials', 'gp_wp'), 
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_section_libcal_access_token', 
		$page       = 'fwf'
	);
	
	add_settings_section( 
		$id         = 'fwf_section_libguide_1.1', 
		$title      = esc_html__('LibGuides 1.1 Endpoint API Credentials', 'gp_wp'), 
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_section_libguide_api_keys', 
		$page       = 'fwf'
	);
	

	add_settings_section( 
		$id         = 'fwf_section_libguide_access_token', 
		$title      = esc_html__('LibGuides 1.2 Access Token', 'gp_wp'), 
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_section_libguide_api_access_token', 
		$page       = 'fwf'
	);	
	
	add_settings_section( 
		$id         = 'fwf_section_istagram', 
		$title      = esc_html__('Instagram API Access Token Credentials', 'gp_wp'), 
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_section_instagram_access_token', 
		$page       = 'fwf'
	);	


	/**
	 * 
	 * And now for the fields
	 * 
	 */

	add_settings_field(
		$id         = 'libcal_iid',
		$title      = esc_html__('LibCal IID', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libcal_1.0', 
		$args       = [ 'id' => 'libcal_iid', 'label' => esc_html__('LibCal system ID.', 'fwf') ]
	);	 
	
	add_settings_field(
		$id         = 'libcal_key',
		$title      = esc_html__('LibGuide Key', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libcal_1.0', 
		$args       = [ 'id' => 'libcal_key', 'label' => esc_html__('Unique key for this site used to identify authorized requests.', 'fwf') ]
	);	
	

//LibCal 1.1 Endpoint Access Token Credentials
	add_settings_field(
		$id         = 'libcal_client_id',
		$title      = esc_html__('LibGuide Client ID', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libcal_1.1', 
		$args       = [ 'id' => 'libcal_client_id', 'label' => esc_html__('Unique client ID for Access token.', 'fwf') ]
	);
	
	add_settings_field(
		$id         = 'libcal_client_secret',
		$title      = esc_html__('LibGuide Client Secret', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libcal_1.1', 
		$args       = [ 'id' => 'libcal_client_secret', 'label' => esc_html__('Unique secret string for Access token.', 'fwf') ]
	);	
	

//Libguide 1.1 Endpoint API keys
	add_settings_field(
		$id         = 'libguide_site_id',
		$title      = esc_html__('LibGuide Site ID', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libguide_1.1', 
		$args       = [ 'id' => 'libguide_site_id', 'label' => esc_html__('	ID of the Springshare site from which the data should be retrieved.', 'fwf') ]
	);
	
	add_settings_field(
		$id         = 'libguide_key',
		$title      = esc_html__('LibGuide Key', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libguide_1.1', 
		$args       = [ 'id' => 'libguide_key', 'label' => esc_html__('	Unique key for this site used to identify authorized requests.', 'fwf') ]
	);
	
	
//libguide 1.2 access token
	add_settings_field(
		$id         = 'libguide_access_token_client_id',
		$title      = esc_html__('LibGuide Access Token Client ID', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libguide_access_token', 
		$args       = [ 'id' => 'libguide_access_token_client_id', 'label' => esc_html__('	Client ID of the Springshare site from which the data should be retrieved.', 'fwf') ]
	);
	
	add_settings_field(
		$id         = 'libguide__access_token_client_secret',
		$title      = esc_html__('LibGuide Access token client Secret', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_libguide_access_token', 
		$args       = [ 'id' => 'libguide__access_token_client_secret', 'label' => esc_html__('	Unique key for this site used to identify authorized requests.', 'fwf') ]
	);
	
//instagram Access Toke Key	
	add_settings_field(
		$id         = 'insta_key',
		$title      = esc_html__('Instagram Key', 'fwf'),
		$callback   = '\FWF\Admin\InputCallbacks\fwf_callback_field_text',
		$page       = 'fwf', 
		$section    = 'fwf_section_istagram', 
		$args       = [ 'id' => 'insta_key', 'label' => esc_html__('Unique ID for Instagram Authorization', 'fwf') ]
	);	
	
	
	

    
} 



