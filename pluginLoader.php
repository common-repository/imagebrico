<?php 
	$protocol 			= ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
	$base_url 			= $protocol . "://" . $_SERVER['HTTP_HOST'];
	$complete_url 		= $base_url . $_SERVER["REQUEST_URI"];
	$baseUrl 			= dirname($complete_url)."/swf/";
	$js_template 		= file_get_contents(dirname(__FILE__)."/iMageBrico.js");
	$js = str_replace("<<baseUrl>>", $baseUrl, $js_template);
	echo $js;
?>