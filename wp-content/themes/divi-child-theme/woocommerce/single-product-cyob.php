<?php

/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
global $product;

get_header('shop'); ?>
<div class="single-product-cyob">
	<?php while (have_posts()) : ?>
		<?php the_post();

		/**
		 * Hook: woocommerce_before_single_product.
		 *
		 * @hooked woocommerce_output_all_notices - 10
		 */
		do_action('woocommerce_before_single_product');

		if (post_password_required()) {
			echo get_the_password_form(); // WPCS: XSS ok.
			return;
		}
		?>
		<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
			<div id="book-build-progress" class="container">
				<div class="progress-container">
					<div class="progress" id="progress"></div>
					<div data-step="style" class="circle active"></div>
					<div data-step="build" class="circle"></div>
					<div data-step="complete" class="circle"></div>
				</div>
			</div>
			<?php
			/**
			 * Hook: woocommerce_before_single_product_summary.
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */
			do_action('woocommerce_before_single_product_summary');

			/**
			 * Hook: woocommerce_single_product_summary.
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 * @hooked WC_Structured_Data::generate_product_data() - 60
			 */
			do_action('woocommerce_single_product_summary');
			?>
			<div id="build-progress-btns">
				<button class="btn prev" disabled>Prev</button>
				<button class="btn next"><span>Next</span><span class="complete" style="display:none;">Add to Bag</span></button>
			</div>
			<?php
			/**
			 * Hook: woocommerce_after_single_product_summary.
			 *
			 * @hooked woocommerce_output_product_data_tabs - 10
			 * @hooked woocommerce_upsell_display - 15
			 * @hooked woocommerce_output_related_products - 20
			 */
			// do_action( 'woocommerce_after_single_product_summary' );
			?>
		</div>

		<?php do_action('woocommerce_after_single_product'); ?>

	<?php endwhile; // end of the loop. 
	?>
</div>

<script></script>
<?php
get_footer('shop');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
