<?php
/*
Plugin Name:  WP Fourwinds Feeds
Description:  This plugin outputs custom JSON using WP's Rest API to be routed to Fourwinds Feeds
Plugin URI:   http://gabrielortizart.com
Author:       Gabriel Ortiz
Version:      0.1.0
Text Domain:  fwf
Domain Path:  /languages
License:      GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
*/

//exit if file is called directly
if( ! defined('ABSPATH') ){
    exit;
}

//set default timezone
date_default_timezone_set('America/Los_Angeles');


//useful global constants
define( 'FWF_VERSION', '0.1.0');
define( 'FWF_PATH', plugin_dir_path( __FILE__ ) );
define( 'FWF_INC', plugin_dir_path( __FILE__ ).'includes/');
define( 'FWF_ADMIN', plugin_dir_path( __FILE__ ).'admin/');
define( 'FWF_PUBLIC', plugin_dir_path( __FILE__ ).'public/');
define( 'FWF_LANG', plugin_dir_path( __FILE__ ).'languages/');
define( 'FWF_LIBRARIES', plugin_dir_path( __FILE__ ).'Libraries/');

//global style urls
define('FWF_ASSETS', plugins_url().'/wp-fwfeeds/assets/');
define('FWF_IMAGES', plugins_url().'/wp-fwfeeds/assets/images/');
define('FWF_IMAGES_GEAR', plugins_url().'/wp-fwfeeds/assets/images/gear-icon.png');
define('FWF_IMAGES_CCL', plugins_url().'/wp-fwfeeds/assets/images/Mudd1.jpg');
define('FWF_DIST', plugins_url().'/wp-fwfeeds/dist/');

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
    define( 'MINUTE_IN_SECONDS',  60 );
}

//LOAD THE INCLUDED LIBRARIES
//require_once plugin_dir_path( __FILE__ ) . 'includes/core-functions.php';
require_once( FWF_LIBRARIES . 'extended-cpts.php' );
require_once( FWF_LIBRARIES . 'extended-taxos.php' );
require_once( FWF_INC . 'register-templates.php' );
require_once( FWF_INC . 'api-helpers.php' );
require_once( FWF_INC . 'libcal.php' );
require_once( FWF_INC . 'libguides.php' );
require_once( FWF_INC . 'libhours.php' );
require_once( FWF_INC . 'spaces.php' );
require_once( FWF_INC . 'space-availability.php' );
require_once( FWF_INC . 'instagram.php' );
require_once( FWF_INC . 'api-endpoints.php' );


//INCLUDE THE ADMIN FUNCTIONS

require_once FWF_ADMIN . 'settings-admin.php';
require_once FWF_ADMIN . 'sections-settings-admin.php';
require_once FWF_ADMIN . 'input-callbacks-admin.php';
require_once FWF_ADMIN . 'settings-notices-admin.php';

//INCLUDE THE PUBLIC FUNCTIONS



//RUN THE SETUP ADMIN FUNCTIONS
if ( is_admin() ) {

	// include dependencies
	FWF\Admin\Settings\setup();
	FWF\Admin\Settings\SectionSettings\setup();
	
}


//RUN THE PUBLIC FUNCTIONS
FWF\Includes\RegisterTemplates\setup();

// default plugin options
// function myplugin_options_default() {

// 	return array(
// 		'custom_url'     => 'https://wordpress.org/',
// 		'custom_title'   => esc_html__('Powered by WordPress','myplugin'),
// 		'custom_style'   => 'enable',
// 		'custom_message' => '<p class="custom-message">'. esc_html__('My custom message', 'myplugin').'</p>',
// 		'custom_footer'  => esc_html__('Special message for users', 'myplugin'),
// 		'custom_toolbar' => false,
// 		'custom_scheme'  => 'default',
// 	);

// }



// load text domain
function fwf_load_textdomain() {
	
	load_plugin_textdomain( 'fwf', false, FWF_LANG );
	
}
add_action( 'plugins_loaded', 'fwf_load_textdomain' );



/**
 * Converts and console logs data from PHP on the front end
 * 
 * @param $data to log, $title
 * 
 * @todo - set up hook for displaying on admin side
 *
 * @return array|mixed|string|\WP_Error
 */
function debug_to_console( $data, $title = null) {
	
	//check for title and localize arguments
	$fn_title = !empty( $title ) ? $title : 'From WP';
	$fn_data = $data;
	
	add_action( 'wp_footer', function() use ($fn_title, $fn_data){
		
	    if( is_array($fn_data) || is_object($fn_data) ) {
			echo "<script>
					if(console.debug!='undefined'){
						console.log('{$fn_title}:' , ". json_encode($fn_data) .");
					}</script>" ;
		} else {
			echo "<script>
					if(console.debug!='undefined'){	
						console.log('{$fn_title}: ".$fn_data."');
					}</script>" ;
		}		
		
	} );
}