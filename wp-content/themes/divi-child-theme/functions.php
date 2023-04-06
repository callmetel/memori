<?php

function fileVer($filename)
{
	return filemtime(get_stylesheet_directory() . "/" . $filename);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles()
{
	wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

// Remove Disable jQuery
function load_custom_scripts()
{
	wp_enqueue_script('divi-child-js', get_stylesheet_directory_uri() . '/scripts.js', array("jquery"), fileVer("scripts.js"), true);
}
if (!is_admin()) {
	add_action('wp_enqueue_scripts', 'load_custom_scripts', 99);
}

function remove_footer_admin()
{
	echo "Divi Child Theme";
}

add_filter('admin_footer_text', 'remove_footer_admin');

// Allow SVG Uploads
function cc_mime_types($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

// Add Shortcode Functionality for Menus 
function print_menu_shortcode($atts, $content = null)
{
	extract(shortcode_atts(array('id' => null, 'class' => null, 'name' => null,), $atts));
	return wp_nav_menu(array('menu_id' => $id, 'menu_class' => $class, 'menu' => $name, 'echo' => false));
}
add_shortcode('menu', 'print_menu_shortcode');  // add using this shortcode [menu id="custom-id" class="custom-class" name="Menu Name"]

// Disable Feeds
function itsme_disable_feed()
{
	wp_die(__('No feed available, please visit the <a href="' . esc_url(home_url('/')) . '">homepage</a>!'));
}

add_action('do_feed', 'itsme_disable_feed', 1);
add_action('do_feed_rdf', 'itsme_disable_feed', 1);
add_action('do_feed_rss', 'itsme_disable_feed', 1);
add_action('do_feed_rss2', 'itsme_disable_feed', 1);
add_action('do_feed_atom', 'itsme_disable_feed', 1);
add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);

// Disable support for comments and trackbacks in post types
function df_disable_comments_post_types_support()
{
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if (post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}
add_action('admin_init', 'df_disable_comments_post_types_support');
// Close comments on the front-end
function df_disable_comments_status()
{
	return false;
}

add_filter('template_include', 'cyob_single_product_template_include', 50, 1);
function cyob_single_product_template_include($template)
{
	if (is_singular('product') && (has_term('cyob', 'product_cat'))) {
		$template = get_stylesheet_directory() . '/woocommerce/single-product-cyob.php';
	}
	return $template;
}

add_filter('template_include', 'premade_single_product_template_include', 50, 1);
function premade_single_product_template_include($template)
{
	if (is_singular('product') && (has_term('premade', 'product_cat'))) {
		$template = get_stylesheet_directory() . '/woocommerce/single-product-premade.php';
	}
	return $template;
}

function rf_product_thumbnail_size($size)
{
	global $product;

	$size = 'full';
	return $size;
}
add_filter('woocommerce_gallery_image_size', 'rf_product_thumbnail_size');

add_filter('woocommerce_get_image_size_gallery_thumbnail', function ($size) {
	return array(
		'width'  => 300,
		'height' => 300,
		'crop'   => 1,
	);
});

add_filter('woocommerce_product_single_add_to_cart_text', 'product_cat_single_add_to_cart_button_text', 20, 1);
function product_cat_single_add_to_cart_button_text($text)
{
	$text = __('Add to Bag', 'woocommerce');
	// Only for a specific product category
	if (has_term(array('cyob'), 'product_cat'))
		$text = __('Create', 'woocommerce');

	return $text;
}

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

function create_pdf()
{
	// Get Payload & Endpoint
	$data = $_POST;
	$endpoint = $data["endpoint"];
	$payload = str_replace('\\', '', $data["payload"]);
	$api_key = "axxqpS5i10noxSzIk2RgYiVPfPXmr0xE";
	$headers = array(
		'Content-Type' => 'application/json',
		'Authorization' => 'Basic ' . base64_encode($api_key)
	);

	$response = wp_safe_remote_post($endpoint, array(
		'timeout' => 240,
		'redirection' => 10,
		'httpversion' => '1.1',
		'headers' => $headers,
		'body' => $payload
	));

	if (is_wp_error($response)) {
		wp_send_json_error('Something went wrong');
	} else {
		// TODO: Create PDF & place in tmppdfs/ dir. Then send pdf link in response
		if (mb_detect_encoding($response["body"], null, true) === false) {
			wp_send_json_success(base64_encode(wp_remote_retrieve_body($response)));
		} else {
			wp_send_json_error(wp_remote_retrieve_body($response), 800);
		}
	}
	die();
}
add_action('wp_ajax_create_pdf', 'create_pdf');
add_action('wp_ajax_nopriv_create_pdf', 'create_pdf');

// Upload Images To Media Folder
function add_tmpimgs()
{
	// TODO: Get all input files from $_POST $_FILES & add them to tmpimgs/ dir
	$input_name = array_key_first($_FILES);
	$image_file = $_FILES[$input_name];

	// Exit if no file uploaded
	if (!isset($image_file)) {
		die('No file uploaded.');
	}

	// Exit if image file is zero bytes
	if (filesize($image_file["tmp_name"]) <= 0) {
		die('Uploaded file has no contents.');
	}

	// Exit if is not a valid image file
	$image_type = exif_imagetype($image_file["tmp_name"]);
	if (!$image_type) {
		die('Uploaded file is not an image.');
	}

	// Get file extension based on file type, to prepend a dot we pass true as the second parameter
	$image_extension = image_type_to_extension($image_type, true);

	// Create a unique image name
	$image_name = bin2hex(random_bytes(16)) . $image_extension;

	// Move the temp image file to the images directory
	move_uploaded_file(
		// Temp image location
		$image_file["tmp_name"],

		// New image location
		ABSPATH . DIRECTORY_SEPARATOR . 'tmpimgs/' . $image_name
	);
	return wp_send_json_success(array("response" => "Image File (" . $image_name . ") Uploaded", "file" => $image_name));
}
add_action('wp_ajax_add_tmpimgs', 'add_tmpimgs');
add_action('wp_ajax_nopriv_add_tmpimgs', 'add_tmpimgs');

// Delete File from Server
function purge_tmpimgs()
{
	$folderpath = ABSPATH . DIRECTORY_SEPARATOR . 'tmpimgs';
	$files = glob($folderpath . '/*');

	// Deleting all the files in the list
	foreach ($files as $file) {

		if (is_file($file))

			// Delete the given file
			unlink($file);
	}
}
add_action('wp_ajax_purge_tmpimgs', 'purge_tmpimgs');
add_action('wp_ajax_nopriv_purge_tmpimgs', 'purge_tmpimgs');
