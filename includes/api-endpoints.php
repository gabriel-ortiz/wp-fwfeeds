<?php

namespace FWF\Includes\ApiEndpoints;

use \FWF\Includes\Spaces                    as Spaces;
use \FWF\Includes\SpaceAvailability         as SpaceAvailability;
use \FWF\Includes\LibCal                    as LibCal;
use \FWF\Includes\Libguides                 as LibGuides;
use \FWF\Includes\LibraryHours              as LibraryHours;
use \FWF\Includes\Instagram                 as Instagram;


/**
 * 
 * This page defines the routes and endpoints for custom API requests
 * 
 */
 

add_action( 'rest_api_init', function () {

    //register the libcal API processing
    register_rest_route( 'fwf/v1', '/libcal/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_libcal_request"
        )
    );  
    
    //register libguide asset retrieval
    register_rest_route( 'fwf/v1', '/libguide-assets/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_libguide_assets"
        )
    ); 
    
    //register library hours retrieval
    register_rest_route( 'fwf/v1', '/library-hours/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_libhours_assets"
        )
    );    
    
    //register libcal bookings retrieval
    register_rest_route( 'fwf/v1', '/space-bookings/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_space_bookings"
        )
    );  

    //register libcal bookings availability retrieval
    register_rest_route( 'fwf/v1', '/space-availability/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_space_availability"
        )
    );  
    
    //register libcal bookings availability retrieval
    register_rest_route( 'fwf/v1', '/instagram/(?P<id>\d+)', array(
        'methods'   => 'GET',
        'callback'  => __NAMESPACE__ . "\\process_instagram"
        )
    );    
    
});

function process_instagram( $data ){

    $insta_id  = $data['id'];

    $insta_feed = Instagram\get_instagram_data( $insta_id );
    
    if( is_wp_error( $insta_feed ) ){
        return 'ERROR: ' . $insta_feed->get_error_message();
    }else{
        return $insta_feed;
    }  
    
}


function process_space_availability( $data ){
    $space_id  = $data['id'];

    $space_availability = SpaceAvailability\get_space_availability( $space_id );
    
    if( is_wp_error( $space_availability ) ){
        return 'ERROR: ' . $space_availability->get_error_message();
    }else{
        return $space_availability;
    }    
    
    
}


function process_space_bookings(  $data ){
    
    $space_id  = $data['id'];

    $libcal_spaces = Spaces\get_spaces( $space_id );
    
    if( is_wp_error( $libcal_spaces ) ){
        return 'ERROR: ' . $libcal_spaces->get_error_message();
    }else{
        return $libcal_spaces;
    }
    
}


function process_libhours_assets( $data ){
    //get the ID number of the room
    $libhours_id  = $data['id'];    
    
    $libhours_results = LibraryHours\get_library_hours( $libhours_id );
    
    if( is_wp_error( $libhours_results ) ){
        return 'ERROR: ' . $libhours_results->get_error_message();
    }else{
        return $libhours_results;
    }
    
    
}

function process_libcal_request(  $data ){
    
    $libcal_id  = $data['id'];

    $libcal_test = LibCal\get_libcal_events($libcal_id);
    
    if( is_wp_error( $libcal_test ) ){
        return 'ERROR: ' . $libcal_test->get_error_message();
    }else{
        return $libcal_test;
    }
    
}

function process_libguide_assets(  $data ){
    
    $libguide_id  = $data['id'];

    $libcal_test = LibGuides\get_libguide_assets($libguide_id);
    
    if( is_wp_error( $libcal_test ) ){
        return 'ERROR: ' . $libcal_test->get_error_message();
    }else{
        return $libcal_test;
    }
    
}