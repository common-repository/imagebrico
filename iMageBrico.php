<?php

/*
Plugin Name: Image Brico
Plugin URI: http://imagebrico.com
Description: Adds a button to the TinyMCE Editor to use the Image Brico Plugin
Version: 0.4
Author: Gestisco Italia
Author URI: http://www.gestisco.it
License: GPL2
*/

function add_iMageBrico_plugin ($plugins_array) {
//function add_iMageBrico_plugin () {
	//Build the response - the key is the plugin name, value is the URL to the plugin JS
	$plugins_array['iMageBrico'] = plugins_url('pluginLoader.php', __FILE__);
	return $plugins_array;
}
add_filter('mce_external_plugins','add_iMageBrico_plugin');
function iMageBrico_register_buttons( $buttons ) {
	array_push( $buttons, 'iMageBrico');
    return $buttons;
}
add_filter('mce_buttons','iMageBrico_register_buttons' );

function iMageBrico_process_ajax() {
	$data_ori = $_REQUEST['file'];
	$fileName = $_REQUEST['fileName'];
	list($type, $data) = explode(';', $data_ori);
	list(, $data)      = explode(',', $data);
	$data = base64_decode($data);
	$temp_file = plugin_dir_path(__FILE__)."tmp_file.jpg";
	file_put_contents($temp_file, $data);
	$file_type = wp_check_filetype($temp_file, null);
	$file = array(
		'name' => "$fileName.jpg",
		'type' => $file_type,
		'tmp_name' => $temp_file,
		'error' => 0,
		'size' => filesize($temp_file),	
	);
	$overrides = array(
		// tells WordPress to not look for the POST form
		// fields that would normally be present, default is true,
		// we downloaded the file from a remote server, so there
		// will be no form fields
		'test_form' => false,

		// setting this to false lets WordPress allow empty files, not recommended
		'test_size' => true,

		// A properly uploaded file will pass this test. 
		// There should be no reason to override this one.
		'test_upload' => true, 
		'test_type' => true,
	);
	// move the temporary file into the uploads directory
	$results = wp_handle_sideload( $file, $overrides );
	if (!empty($results['error'])) {
		// insert any error handling here
		echo json_encode(array("error!",$results,$file));exit();
	} else {
		$filename = $results['file']; // full path to the file
		$local_url = $results['url']; // URL to the file in the uploads dir
		$wp_filetype = $results['type']; // MIME type of the file
		//echo json_encode($results);exit();

		$wp_upload_dir = wp_upload_dir();
		$attachment = array(
			'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $wp_filetype,
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename);

		require_once( ABSPATH . 'wp-admin/includes/image.php' );// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		//echo json_encode(array("src"=>$local_url, "alt"=>$attach_id,"result"=>$results,"att_data"=>$attach_data, "attachment"=>$attachment,"file"=>$file,"base64"=>$data_ori));
		echo json_encode(array("src"=>$local_url, "alt"=>"Image Brico"));
	}
	// After processing AJAX, this PHP function must die
	wp_die();
}
add_action('wp_ajax_iMageBrico_get_results', 'iMageBrico_process_ajax');
?>