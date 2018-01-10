<?php
namespace FWF\Includes\RegisterTemplates;

/**
 * 
 * Set up custom template for Testimonial Post type
 * 
 * @return void
 * 
 */
 
 function setup(){
    //create namespace for functions
    $n = function($function){
          return __NAMESPACE__. "\\$function";
    };
     /* Filter the single_template with our custom function*/
    //add_filter('single_template', $n( 'testimonial_custom_template' ) );

    add_action ( 'init', $n( 'url_for_room_availability' ) );
    add_filter ( 'query_vars', $n( 'room_availability_query_var' ) );
    add_filter ( 'template_include', $n( 'template_for_room_availability' ) );        

 }
 
function testimonial_custom_template($single) {

    global $wp_query, $post;

    /* Checks for single template by post type */
    if ( $post->post_type == 'testimonial' ) {
        if ( file_exists( CPT_TEMPLATES . '/single-testimonial.php' ) ) {
            return CPT_TEMPLATES . '/single-testimonial.php';
        }
    }
    
    return $single;
}

function url_for_room_availability() {
    add_rewrite_rule( '^room_availability/(.+)/?$', 'index.php?room_availability=$matches[1]', 'top' );
    
    
}

function room_availability_query_var ( $vars ) {
    $vars[] = 'room_availability';
    return $vars;
}

function template_for_room_availability( $template ) {
    
    $avail_check = $_GET['room_availability'];
    

    if ( isset( $avail_check )  ) {
        $template = FWF_PUBLIC . 'view-availability.php';
        return $template;
    }else{
        return $template;
    }


}

