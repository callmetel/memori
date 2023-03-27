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
