<?php

namespace FWF\Admin\PageContent;

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
    //page content is being called admin-menu.php
    //add_action('admin_notices', $n( 'fwf_admin_notices' ) );
    
 }
 





//display admin notices
// function myplugin_admin_notices(){
// 	settings_errors();
// }


//display custom admin notices
function myplugin_admin_notices(){
	//get the current screen
	$screen = get_current_screen();
	
	//var_dump( $screen );
	
	//return if not myplugin settings page
	if($screen->id !== 'toplevel_page_myplugin');
	
	//check if settings updated
	if( isset(  $_GET['settings-updated'] ) ){
		
		if( 'true' === $_GET['settings-updated'] ):
		
	?>
	
		<div class="notice notice-success is-dismissible">
			<p><strong><?php _e('Congratulations, you are awesome!', 'myplugin'); ?></strong></p>
		</div>
	
	
		<?php
		
			//if there is an error
			else :	
			
		?>	
			<div class="notice notice-error is-dismissible">
				<p><strong><?php _e('Houston We have a problem', 'myplugin'); ?></strong></p>
			</div>
		
	<?php
	
		endif;
	}
	
}
