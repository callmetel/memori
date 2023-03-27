<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

global $product;

get_header('shop');
?>
<div class="shop-products-wrapper container">
    <?php
    if (woocommerce_product_loop()) {

        /**
         * Hook: woocommerce_before_shop_loop.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        // do_action('woocommerce_before_shop_loop');

        woocommerce_product_loop_start();

        if (wc_get_loop_prop('total')) {
            while (have_posts()) {
                the_post();

                if (empty($product) || !$product->is_visible()) {
                    return;
                }
                $id = wc_get_product()->id;
    ?>
                <div class="shop-product shop-product-<?php echo $product->slug; ?>">
                    <h3 class="product-name text-center"><?php echo $product->name; ?></h3>
                    <div class="row">
                        <div class="column">
                            <div class="inner">
                                <div class="product-image">
                                    <img src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'single-post-thumbnail')[0]; ?>" />
                                </div>
                                <div class="product-price">
                                    <p class="text-center bold h4">$<?php echo $product->price; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="product-description">
                                <div class="inner">
                                    <?php echo $product->description; ?>
                                    <a class="et_pb_button shop-product-button et_pb_bg_layout_light" href="<?php the_permalink($id); ?>">
                                        <?php echo $id === 64 ? "Create" : "Explore"; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php
            }
        }

        woocommerce_product_loop_end();

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action('woocommerce_after_shop_loop');
    } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action('woocommerce_no_products_found');
    }
    ?>
</div>

<div class="disclaimer product-disclaimer container">
    <p class="text-center bold h6">all production of books, materials, and processing are sustianable carbon. <br /> reduced carbon emissions, sustainable and local production</p>
</div>
<?php
get_footer('shop');
