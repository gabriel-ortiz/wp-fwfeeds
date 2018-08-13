<?php

namespace FWF\Admin\InputCallbacks;

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
 }
 
 // callback: LibCal 1.1 section
function fwf_callback_section_libcal_api_keys() {
	
	echo '<p>'. esc_html__('These settings enable you get access to LibCal\'s API using basic API keys.', 'fwf') .'</p>';
	
}


// callback: LibCal section 1.2
function fwf_callback_section_libcal_access_token() {
	
	echo '<p>'. esc_html__('These settings enable you to LibCal\'s API using an Access token.', 'fwf') .'</p>';
	
}

// callback: LibGuides section 1.1 
function fwf_callback_section_libguide_api_keys(){
	echo '<p>'. esc_html__('These settings enable you to LibGuides\'s API.', 'fwf') .'</p>';
}

// callback: LibGuides section 1.2 Access Token 
function fwf_callback_section_libguide_api_access_token(){
	echo '<p>'. esc_html__('These settings enable you to LibGuides\'s API via Access tokens.', 'fwf') .'</p>';
}

// callback: Instagram
function fwf_callback_section_instagram_access_token(){
	echo '<p>'. esc_html__('These settings enable you to Instagrams\'s API using an Access token.', 'fwf') .'</p>';
}

// callback: Instagram
function fwf_callback_section_instagram_library_hours(){
	echo '<p>'. esc_html__('These settings enable you query the Library Hours API for a specific library.', 'fwf') .'</p>';
}

// callback: text field
function fwf_callback_field_text( $args ) {
	
	$options = get_option( 'fwf_options' );
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';
	
	echo '<input id="fwf_options_'. $id .'" name="fwf_options['. $id .']" type="text" size="40" value="'. $value .'"><br />';
	echo '<label for="fwf_options_'. $id .'">'. $label .'</label>';
	
}



// radio field options
function fwf_options_radio() {
	
	return array(
		
		'enable'  => esc_html__('Enable custom styles', 'fwf'),
		'disable' => esc_html__('Disable custom styles', 'fwf')
		
	);
	
}



// callback: radio field
function fwf_callback_field_radio( $args ) {
	
	$options = get_option( 'fwf_options' );
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$selected_option = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';
	
	$radio_options = fwf_options_radio();
	
	foreach ( $radio_options as $value => $label ) {
		
		$checked = checked( $selected_option === $value, true, false );
		
		echo '<label><input name="fwf_options['. $id .']" type="radio" value="'. $value .'"'. $checked .'> ';
		echo '<span>'. $label .'</span></label><br />';
		
	}
	
}



// callback: textarea field
function fwf_callback_field_textarea( $args ) {
	
	$options = get_option( 'fwf_options' );
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$allowed_tags = wp_kses_allowed_html( 'post' );
	
	$value = isset( $options[$id] ) ? wp_kses( stripslashes_deep( $options[$id] ), $allowed_tags ) : '';
	
	echo '<textarea id="fwf_options_'. $id .'" name="fwf_options['. $id .']" rows="5" cols="50">'. $value .'</textarea><br />';
	echo '<label for="fwf_options_'. $id .'">'. $label .'</label>';
	
}



// callback: checkbox field
function fwf_callback_field_checkbox( $args ) {
	
	$options = get_option( 'fwf_options' );
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$checked = isset( $options[$id] ) ? checked( $options[$id], 1, false ) : '';
	
	echo '<input id="fwf_options_'. $id .'" name="fwf_options['. $id .']" type="checkbox" value="1"'. $checked .'> ';
	echo '<label for="fwf_options_'. $id .'">'. $label .'</label>';
	
}



// select field options
function fwf_options_select() {
	
	return array(
		
		'default'   => esc_html__('Default',   'fwf'),
		'light'     => esc_html__('Light',     'fwf'),
		'blue'      => esc_html__('Blue',      'fwf'),
		'coffee'    => esc_html__('Coffee',    'fwf'),
		'ectoplasm' => esc_html__('Ectoplasm', 'fwf'),
		'midnight'  => esc_html__('Midnight',  'fwf'),
		'ocean'     => esc_html__('Ocean',     'fwf'),
		'sunrise'   => esc_html__('Sunrise',   'fwf'),
		
	);
	
}



// callback: select field
function fwf_callback_field_select( $args ) {
	
	$options = get_option( 'fwf_options' );
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$selected_option = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';
	
	$select_options = fwf_options_select();
	
	echo '<select id="fwf_options_'. $id .'" name="fwf_options['. $id .']">';
	
	foreach ( $select_options as $value => $option ) {
		
		$selected = selected( $selected_option === $value, true, false );
		
		echo '<option value="'. $value .'"'. $selected .'>'. $option .'</option>';
		
	}
	
	echo '</select> <label for="fwf_options_'. $id .'">'. $label .'</label>';
	
}