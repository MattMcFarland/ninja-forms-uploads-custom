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

	
	/*
	This makes user_value get the following fields:
	file_name: "file.png"
	file_path: "/var/www/vhosts/gdfiles.net/hvac-hacks/wp-content/uploads/ninja-forms/tmp/B5z75/ninja_forms_field_609"
	file_url: "//example.com/file.png"
	user_file_name: "file.png"
	*/
	$user_value = $ninja_forms_processing->get_field_value( $field_id );
	
	/*
	$field[data]
		calc_auto_include: "0"
		class: ""
		conditional: ""
		desc_pos: "none"
		desc_text: ""
		email_attachment: "0"
		help_text: ""
		label: "File Upload"
		label_pos: "left"
		media_library: "0"
		post_meta_value: ""
		profile_meta_value: ""
		req: "0"
		show_desc: "0"
		show_help: "0"
		upload_multi: "0"
		upload_multi_count: ""
		upload_rename: ""
		upload_types: ""	
	*/
	$field = $ninja_forms_processing->get_field_settings( $field_id );
	$image_types = array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' );
	
	if ( is_array ( $user_value ) ) {
		foreach ( $user_value as $key => $data ) {
			$dir = $data['file_path'];
			$data_url = $data['file_url'];
			$url_array = explode("/",$data_url);
			array_pop($url_array);
			$url = implode("/", $url_array);
			fb('url='.$url);
			$user_file_name = $data['user_file_name'];
			$user_file_array = explode(".", $user_file_name);
			$ext = array_pop($user_file_array);
			//Warning: filenames can't have more than one . chracter
			$base = $user_file_array[0];
			$newjpg = $base.".jpg";
			
			$ext = strtolower( $ext );
			if ( in_array( $ext, $image_types ) ) {
				fb('convert file to '.$dir.'/'.$newjpg);
				//auto-orient the image and convert to jpg.
				curl_exec('mogrify -auto-orient -verbose -format jpg '.$dir."/".$user_file_name,$output);
				$output = shell_exec('mogrify -auto-orient -verbose -format jpg '.$dir."/".$user_file_name);
				fb($output);
				$data['file_name'] = $base.'.jpg';
				$data['file_url'] = $url."/".$base.'.jpg';
				$data['user_file_name'] = $base.'.jpg';
				fb($data['file_name'] . ', ' . $data['file_url'] . ', ' . $data['user_file_name']);
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



