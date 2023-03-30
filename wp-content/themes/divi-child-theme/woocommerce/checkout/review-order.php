<?php

/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
?>
<div class="order-review">
    <h4 class="title">Order Information</h4>
    <div class="review-wrapper row">
        <div class="col">
            <div class="products">
                <?php
                do_action('woocommerce_review_order_before_cart_contents');

                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                ?>
                        <div class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                            <?php $id = wc_get_product($_product)->id; ?>
                            <div class="product-image">
                                <img src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail')[0]; ?>" />
                                <div class="product-remove">
                                    <?php
                                    echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">Remove</a>',
                                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                                            esc_html__('Remove this item', 'woocommerce'),
                                            esc_attr($product_id),
                                            esc_attr($_product->get_sku())
                                        ),
                                        $cart_item_key
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-name">
                                    <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
                                </div>
                                <div class="product-total">
                                    <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                    ?>
                                </div>
                                <div class="product-quantity">
                                    <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('quantity: <span>%s</span>', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                    ?>
                                    <?php // echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                    ?>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                }

                do_action('woocommerce_review_order_after_cart_contents');
                ?>
            </div>
        </div>
        <div class="col">
            <div class="cart-info">

                <div class="cart-totals">
                    <p class="cart-subtotal">
                        <span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                        <span><?php wc_cart_totals_subtotal_html(); ?></span>
                    </p>

                    <div class="cart-coupons">
                        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                            <p class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                                <span><?php wc_cart_totals_coupon_label($coupon); ?></span>
                                <span><?php wc_cart_totals_coupon_html($coupon); ?></span>
                            </p>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-shipping">
                        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

                            <?php do_action('woocommerce_review_order_before_shipping'); ?>

                            <?php wc_cart_totals_shipping_html(); ?>

                            <?php do_action('woocommerce_review_order_after_shipping'); ?>

                        <?php endif; ?>
                    </div>

                    <div class="cart-fees">
                        <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                            <p class="fee">
                                <span><?php echo esc_html($fee->name); ?></span>
                                <span><?php wc_cart_totals_fee_html($fee); ?></sp>
                            </p>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-tax">
                        <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                                <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited 
                                ?>
                                    <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                                        <th><?php echo esc_html($tax->label); ?></th>
                                        <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="tax-total">
                                    <p><?php echo esc_html(WC()->countries->tax_or_vat()); ?>: <?php wc_cart_totals_taxes_total_html(); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php do_action('woocommerce_review_order_before_order_total'); ?>

                    <div class="order-total">
                        <p><?php esc_html_e('Total', 'woocommerce'); ?>: <?php wc_cart_totals_order_total_html(); ?></p>
                    </div>

                    <?php do_action('woocommerce_review_order_after_order_total'); ?>
                </div>
                <div class="order-notice text-center">
                    <p>Orders will be delivered within 12-16 business days</p>
                    <p>Please double check address and order information as orders cannot be changed, cancelled or returned once placed</p>
                </div>
            </div>
        </div>
    </div>
</div>