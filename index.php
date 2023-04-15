<?php
/*
 * Plugin Name:       Kongfuseo Addon
 * Description:       Enhance woocommerce functions with customized design products.
 * Version:           1.1.7
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Kongfuseo Co.
 * Author URI:        https://www.kongfuseo.com.au/
 * Text Domain:       KongfuseoAddon
 * Domain Path:       /languages
  */


const PLUGIN_SLUG_NAME = 'kongfuseo-admin-setting-panel';
const VERSION = '1.1.7';
const DEV_MODE = false;
const CATEGORY_OPTION = 'kongfuseo-creation-category';

define( 'KONGFU_EXT_FILE', __FILE__ ); 
define( 'KONGFU_EXT_DIR', plugin_dir_path( KONGFU_EXT_FILE ) );

require_once KONGFU_EXT_DIR . 'addons/planner-api.php';
require_once KONGFU_EXT_DIR . 'addons/cart-api.php';
require_once KONGFU_EXT_DIR . 'addons/global-api.php';
require_once KONGFU_EXT_DIR . 'addons/media-library.php';
require_once KONGFU_EXT_DIR . 'addons/preset-api.php';


function my_vue_panel_page()
{
    $setting_data_option = get_option(PLUGIN_SLUG_NAME);
    $creation_category = get_option(CATEGORY_OPTION);
?>
    <script type="text/javascript">
        const vue_wp_api_url = "<?php echo get_site_url() . '/wp-json/' . PLUGIN_SLUG_NAME ?>";
        const vue_wp_settings_data = <?php
                                        if ($setting_data_option) {
                                            echo json_encode($setting_data_option);
                                        } else {
                                            echo '{}';
                                        }
                                        ?>;
        const vue_wp_category_data = <?php
                                        if ($creation_category) {
                                            echo json_encode($creation_category);
                                        } else {
                                            echo '{}';
                                        }
                                        ?>;
    </script>
    <div id="app-kongfuseo"></div>
<?php
    wp_enqueue_script(
        PLUGIN_SLUG_NAME . '-chunk',
        plugin_dir_url(__FILE__) . 'app/dist/js/chunk-vendors.js',
        array(),
        DEV_MODE ? time() : VERSION,
        true
    );
    wp_enqueue_script(
        PLUGIN_SLUG_NAME . '-main',
        plugin_dir_url(__FILE__) . 'app/dist/js/app.js',
        array(),
        DEV_MODE ? time() : VERSION,
        false
    );
    wp_enqueue_style(
        PLUGIN_SLUG_NAME . '-chunk',
        plugin_dir_url(__FILE__) . 'app/dist/css/chunk-vendors.css',
        array(),
        DEV_MODE ? time() : VERSION
    );
    wp_enqueue_style(
        PLUGIN_SLUG_NAME,
        plugin_dir_url(__FILE__) . 'app/dist/css/app.css',
        array(),
        DEV_MODE ? time() : VERSION
    );
}

function add_menu_item()
{
    add_menu_page(
        "Creations Setting Pannel",
        "Our Creations",
        "manage_options",
        PLUGIN_SLUG_NAME,
        "my_vue_panel_page",
        'dashicons-tickets-alt',
        30
    );
}

add_action("admin_menu", "add_menu_item");



/**
 * Rest api for saving setting
 * @param $request
 * @return mixed
 */
function save_settings_func($request)
{
    $user = wp_get_current_user();
    if (is_super_admin($user->ID)) {
        $params = $request->get_json_params();
        return update_option(PLUGIN_SLUG_NAME,  $params);
    } else {
        return new WP_Error('not_allowed', null, array('status' => 403,));
    }
}

// 保存option api
add_action('rest_api_init', function () {
    register_rest_route(PLUGIN_SLUG_NAME, '/save', array(
        'methods' => 'POST',
        'callback' => 'save_settings_func',
    ));
});

function get_settings_func($request)
{
    $setting_data_option = get_option(PLUGIN_SLUG_NAME);
    return json_encode($setting_data_option);
}

// 获取option api
add_action('rest_api_init', function () {
    register_rest_route(PLUGIN_SLUG_NAME, '/get', array(
        'methods' => 'GET',
        'callback' => 'get_settings_func',
    ));
});


// 获取图片api
if (!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

function kongfuseo_enquene_media()
{
    wp_enqueue_media();
}

add_action('admin_enqueue_scripts', 'kongfuseo_enquene_media');


// This limits users so that they can only see content that they have uploaded to the media library
function hideMediaFromOtherUsers($query)
{
    $user_id = get_current_user_id();
    if ($user_id && !current_user_can('manage_options')) {
        $query['author'] = $user_id;
    }
    return $query;
}
add_filter('ajax_query_attachments_args', 'hideMediaFromOtherUsers');


// init frontend planner
function init_kongfuseo_planner()
{
    wp_enqueue_style('chunk-style', plugin_dir_url(__FILE__) . 'planner/dist/css/chunk-vendors.css', array(), DEV_MODE ? time() : VERSION, 'all');
    wp_enqueue_style('main-style', plugin_dir_url(__FILE__) . 'planner/dist/css/app.css', array(), DEV_MODE ? time() : VERSION, 'all');
    wp_enqueue_script('chunk-js', plugin_dir_url(__FILE__) . 'planner/dist/js/chunk-vendors.js', NUll, DEV_MODE ? time() : VERSION, false);
    wp_enqueue_script('main-js', plugin_dir_url(__FILE__) . 'planner/dist/js/app.js', NUll, DEV_MODE ? time() : VERSION, false);
    wp_localize_script('main-js', 'kongfuseo_addon_data', array(
        'nonce' => wp_create_nonce('rental-cart')
    ));
    $str = '<div id="app"></div>';

    return $str;
}

if (!shortcode_exists('kongfuseo_planner')) {
    add_shortcode('kongfuseo_planner', 'init_kongfuseo_planner');
}


// add multiple products to woocommerce shop cart
function woocommerce_maybe_add_multiple_products_to_cart()
{
    // Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
    if (!class_exists('WC_Form_Handler') || empty($_REQUEST['add-to-cart']) || false === strpos($_REQUEST['add-to-cart'], ',')) {
        return;
    }

    // Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
    remove_action('wp_loaded', array('WC_Form_Handler', 'add_to_cart_action'), 20);

    $product_ids = explode(',', $_REQUEST['add-to-cart']);
    $count       = count($product_ids);
    $number      = 0;

    foreach ($product_ids as $product_id) {
        if (++$number === $count) {
            // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
            $_REQUEST['add-to-cart'] = $product_id;

            return WC_Form_Handler::add_to_cart_action();
        }

        $product_id        = apply_filters('woocommerce_add_to_cart_product_id', absint($product_id));
        $was_added_to_cart = false;
        $adding_to_cart    = wc_get_product($product_id);

        if (!$adding_to_cart) {
            continue;
        }

        $add_to_cart_handler = apply_filters('woocommerce_add_to_cart_handler', $adding_to_cart->product_type, $adding_to_cart);

        /*
         * Sorry.. if you want non-simple products, you're on your own.
         *
         * Related: WooCommerce has set the following methods as private:
         * WC_Form_Handler::add_to_cart_handler_variable(),
         * WC_Form_Handler::add_to_cart_handler_grouped(),
         * WC_Form_Handler::add_to_cart_handler_simple()
         *
         * Why you gotta be like that WooCommerce?
         */
        if ('simple' !== $add_to_cart_handler) {
            continue;
        }

        // For now, quantity applies to all products.. This could be changed easily enough, but I didn't need this feature.
        $quantity          = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

        if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity)) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
        }
    }
}

// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action('wp_loaded',        'woocommerce_maybe_add_multiple_products_to_cart', 15);


/******* product category ********/
/**
 * Lists all product categories and sub-categories in a tree structure.
 *
 * @return array
 */
function list_product_categories()
{
    $categories = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'orderby'    => 'name',
            'hide_empty' => false,
        )
    );

    $categories = treeify_terms($categories);

    return wp_send_json([
        'data' => $categories
    ]);
}

/**
 * Converts a flat array of terms into a hierarchical tree structure.
 *
 * @param WP_Term[] $terms Terms to sort.
 * @param integer   $root_id Id of the term which is considered the root of the tree.
 *
 * @return array Returns an array of term data. Note the term data is an array, rather than
 * term object.
 */
function treeify_terms($terms, $root_id = 0)
{
    $tree = array();

    foreach ($terms as $term) {
        if ($term->parent === $root_id) {
            array_push(
                $tree,
                array(
                    'name'     => $term->name,
                    'slug'     => $term->slug,
                    'id'       => $term->term_id,
                    'count'    => $term->count,
                    'children' => treeify_terms($terms, $term->term_id),
                )
            );
        }
    }

    return $tree;
}


add_action('rest_api_init', function () {
    register_rest_route(PLUGIN_SLUG_NAME, '/product-category/get', array(
        'methods' => 'GET',
        'callback' => 'list_product_categories',
    ));
});


function list_categories_by_ids($request)
{
    global $wpdb;
    $ids = $request['ids'];

    $list = array();
    foreach ($ids as $id) {
        array_push($list, get_term_by('term_id', $id, 'product_cat'));
    }
    // $sql = "SELECT t.* 
    // FROM `wp_terms` t
    // INNER JOIN `wp_term_taxonomy` tt ON(t.`term_id` = tt.`term_id`)
    // WHERE tt.`taxonomy` = 'product_cat' and t.`term_id` IN ($ids)";
    // $sql = "SELECT t.*, tt.*
    //     FROM wp_terms AS t 
    //     INNER JOIN wp_term_taxonomy AS tt ON tt.term_id = t.term_id 
    //     INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
    //     WHERE tt.taxonomy IN ('product_cat')";
    return $list;
    // return $wpdb->get_results($sql);
}

add_action('rest_api_init', function () {
    register_rest_route(PLUGIN_SLUG_NAME, '/product-category/fetch', array(
        'methods' => 'POST',
        'callback' => 'list_categories_by_ids',
    ));
});

