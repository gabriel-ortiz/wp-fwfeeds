<?php

namespace FWF\Admin\Settings;

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
    add_action( 'admin_menu', $n('fwf_add_sublevel_menu') );
 }
 
 // add sub-level administrative menu
function fwf_add_sublevel_menu() {
	
	add_submenu_page(
		$parent_slug    = 'options-general.php',
		$page_title     = esc_html__('FourWinds Feed Settings', 'fwf'),
		$menu_title     = esc_html__('fwf', 'fwf'),
		$capability     = 'manage_options',
		$menu_slug      = 'fwf',
		$function       = '\FWF\Admin\Settings\fwf_display_settings_page'
    );

}

// display the plugin settings page
function fwf_display_settings_page() {
	
	// check if user is allowed access
	if ( ! current_user_can( 'manage_options' ) ) return;
	
	//var_dump( get_option('fwf_options'));

	
	?>
	
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		
		<form action="options.php" method="post">
			
			<?php
			
			// output security fields
			settings_fields( $option_group = 'fwf_options' );
			
			// output setting sections
			do_settings_sections( $page = 'fwf' );
			
			// submit button
			submit_button();
			
			?>
			
		</form>
	</div>
	
	<?php
	
}