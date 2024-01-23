<?php
/*
 * Plugin Name:       WooCommerce Admin Filter By SKU
 * Plugin URI:        https://github.com/mehrshaddarzi/woocommerce-admin-product-sku-filter
 * Description:       Add New Product SKU filter in Woocommerce product list table
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Mehrshad Darzi
 * Author URI:        https://github.com/mehrshaddarzi
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woocommerce-admin-product-sku-filter
 * Domain Path:       /text-domain
 */

class WooCommerce_Admin_Product_SKU_Filter
{

    public function __construct()
    {
        add_action('restrict_manage_posts', array($this, 'admin_posts_filter_restrict_manage_posts'));
        add_filter('parse_query', array($this, 'parse_query'), 10);
    }

    public function parse_query($query)
    {
        // Check in edit.php
        global $pagenow;
        if ($pagenow != "edit.php") {
            return $query;
        }

        //modify the query only if it is admin and main query.
        if (!(is_admin() and $query->is_main_query())) {
            return $query;
        }

        //we want to modify the query for the targeted custom post.
        if ('product' != $query->query['post_type']) {
            return $query;
        }

        // Setup Category ID
        if (isset($_GET['product_sku']) and !empty($_GET['product_sku'])) {

            $search_input = sanitize_text_field($_GET['product_sku']);
            $product_id = wc_get_product_id_by_sku($search_input);
            $post__in = [0];
            if ($product_id) {
                // Check Parent ID for Variation
                $get_product = wc_get_product($product_id);
                $post__in = [$get_product->is_type('variation') ? $get_product->get_parent_id() : $get_product->get_id()];
            }

            $query->query_vars['post__in'] = $post__in;
        }

        return $query;
    }

    public function admin_posts_filter_restrict_manage_posts($post_type)
    {
        global $wpdb;

        if ($post_type != 'product') {
            return;
        }

        echo '<input type="text" name="product_sku" value="' . (!empty($_GET['product_sku']) ? trim($_GET['product_sku']) : '') . '" style="width: 140px; text-align: left;" dir="rtl" placeholder="SKU">';
    }

}

new WooCommerce_Admin_Product_SKU_Filter();