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

function get_base_url()
{
	$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
		=== 'on' ? "https" : "http") .
		"://" . $_SERVER['HTTP_HOST'];

	return $link;
}

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
		if (mb_detect_encoding($response["body"], null, true) === false) {
			$pdf_data = wp_remote_retrieve_body($response);
			$pdf_name = $data["pdfName"] . bin2hex(random_bytes(16)) . ".pdf";
			$pdf_location = "tmppdfs/" . $pdf_name;
			file_put_contents(ABSPATH . DIRECTORY_SEPARATOR . $pdf_location, $pdf_data);

			$pdf_url = get_base_url() . DIRECTORY_SEPARATOR . $pdf_location;
			wp_send_json_success($pdf_url);
		} else {
			wp_send_json_error(wp_remote_retrieve_body($response), 800);
		}
	}
	die();
}
add_action('wp_ajax_create_pdf', 'create_pdf');
add_action('wp_ajax_nopriv_create_pdf', 'create_pdf');

function reArrayFiles(&$file_post)
{

	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);

	for ($i = 0; $i < $file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}

	return $file_ary;
}

// Upload Images To Media Folder
function add_tmpimgs()
{
	$input_name = array_key_first($_FILES);
	$image_file = $_FILES[$input_name];
	$image_links = [];

	if ($image_file) {
		$file_ary = reArrayFiles($image_file);
		$file_index = 1;

		foreach ($file_ary as $file) {

			// Get file extension based on file type, to prepend a dot we pass true as the second parameter
			$image_location = 'tmpimgs/' . $file["name"];

			// Move the temp image file to the images directory
			move_uploaded_file(
				// Temp image location
				$file["tmp_name"],

				// New image location
				ABSPATH . DIRECTORY_SEPARATOR . $image_location
			);

			// Add image link to image_links array
			$image_url = get_base_url() . DIRECTORY_SEPARATOR . $image_location;
			$image_links["img" . $file_index] = $image_url;
			$file_index++;
		}
	}
	return wp_send_json_success($image_links);
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
