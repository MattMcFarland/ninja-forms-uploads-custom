<?php
/*
Plugin Name: Ninja Forms - File Uploads Custom Code
Plugin URI: http://ninjaforms.com
Description: Custom code that checks the file types of File Uploads and sets featured images accordingly.
Version: 1.0
Author: The WP Ninjas
Author URI: http://ninjaforms.com
*/

function ninja_forms_custom_file_type_check( $field_id ){
	global $ninja_forms_processing;

	$user_value = $ninja_forms_processing->get_field_value( $field_id );
	$field = $ninja_forms_processing->get_field_settings( $field_id );
	$image_types = array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' );
	if ( is_array ( $user_value ) ) {
		foreach ( $user_value as $key => $data ) {
			$user_file_name = $data['user_file_name'];
			$user_file_array = explode(".", $user_file_name);
			$ext = array_pop($user_file_array);
			$ext = strtolower( $ext );
			if ( in_array( $ext, $image_types ) ) {
				$field['data']['featured_image'] = 1;
				$field['data']['post_meta_value'] = '';
			} else {
				$field['data']['featured_image'] = 0;
				$field['data']['post_meta_value'] = 'video_url';
				
			}
		}
	}
	$ninja_forms_processing->update_field_settings( $field_id, $field );
}

add_action( 'ninja_forms_upload_pre_process', 'ninja_forms_custom_file_type_check' );

function ninja_forms_custom_post_meta_filter( $value, $field_id ){
	$field = ninja_forms_get_field_by_id( $field_id );
	if ( $field['type'] == '_upload' ) {
		foreach ( $value as $id => $data ) {
			$value = $data['file_url'];
		}
	}
	return $value;
}

add_filter( 'ninja_forms_add_post_meta_value', 'ninja_forms_custom_post_meta_filter', 10, 2 );



