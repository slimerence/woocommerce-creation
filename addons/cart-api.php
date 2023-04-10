<?php

function kongfuseo_add_to_cart($request)
{
    $product_data = $request->get_params();
    // Validate the product data
    if (empty($product_data) || !is_array($product_data)) {
        return new WP_Error('invalid_product_data', 'Invalid product data', array('status' => 400));
    }

    // Add each product to the cart
    foreach ($product_data as $product) {
        $product_id = isset($product['product_id']) ? absint($product['product_id']) : 0;
        $variation_id = isset($product['variation_id']) ? absint($product['variation_id']) : 0;
        $quantity = isset($product['quantity']) ? absint($product['quantity']) : 1;

        if ($product_id && $quantity) {
            WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        }
    }

    // Return the cart contents
    $cart_contents = array();
    foreach (WC()->cart->get_cart() as $cart_item) {
        $cart_contents[] = array(
            'product_id' => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
            'quantity' => $cart_item['quantity'],
        );
    }

    return $cart_contents;
}


// add_action( 'rest_api_init', function () {
//   register_rest_route( 'kongfuseo/v1', '/add-to-cart', array(
//       'methods' => 'POST',
//       'callback' => 'kongfuseo_add_to_cart',
//   ) );
// } );


add_action('wp_ajax_add_multiple_to_cart', 'kongfu_add_multiple_to_cart');
add_action('wp_ajax_nopriv_add_multiple_to_cart', 'kongfu_add_multiple_to_cart');

function kongfu_add_multiple_to_cart()
{
    $products = $_POST['products'];
    $default_rental_options = wcrp_rental_products_default_rental_options();

    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $quantity = $product['quantity'];
        $variation_id = $product['variation_id'];

        // Get the product object
        $product_obj = wc_get_product($product_id);
        $cart_item_data = array();
        if (!empty($product['wcrp_rental_products_rent_from'])) {
            $cart_item_data['wcrp_rental_products_rent_from'] = $product['wcrp_rental_products_rent_from'];
            $cart_item_data['wcrp_rental_products_rent_to'] = $product['wcrp_rental_products_rent_to'];
            $cart_item_data['wcrp_rental_products_rental_form_nonce'] = $product['wcrp_rental_products_rental_form_nonce'];

            $start_days_threshold = get_post_meta($product_id, '_wcrp_rental_products_start_days_', true);
            $start_days_threshold = ('' !== $start_days_threshold ? $start_days_threshold : $default_rental_options['_wcrp_rental_products_start_days_threshold']);

            $return_days_threshold = get_post_meta($product_id, '_wcrp_rental_products_return_days_threshold', true);
            $return_days_threshold = ('' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold']);

            $cart_item_data['wcrp_rental_products_start_days_threshold'] = $start_days_threshold;
            $cart_item_data['wcrp_rental_products_return_days_threshold'] = $return_days_threshold;
            $cart_item_data['wcrp_rental_products_cart_item_timestamp'] = current_time('timestamp', false);
            
            $rentFromDate = new DateTime($product['wcrp_rental_products_rent_from']);
            $rentToDate = new DateTime($product['wcrp_rental_products_rent_to']);
            $diff = $rentFromDate->diff($rentToDate);
            $diffDays = intval($diff->format('%r%a')) + 1;
									
            $cart_item_data['wcrp_rental_products_cart_item_price'] = get_rental_product_price($product_id, $variation_id) * $diffDays;
        }

        if ($product_obj->is_type('variable')) {
            // If the product is a variable product, get the variation object
            $variation_obj = new WC_Product_Variation($variation_id);

            WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_obj->get_variation_attributes(), $cart_item_data);
        } else {
            // If the product is a simple product, add it to the cart with the specified quantity
            WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        }
    }

    wp_send_json_success(WC()->cart->get_cart());
}


function get_rental_product_price($product_id, $variation_id = 0)
{
    $product_obj = wc_get_product($product_id);
    $price = 0;
    if ($product_obj->is_type('variable')) {
        $variation_obj = new WC_Product_Variation($variation_id);
        if ('' !== get_post_meta($variation_id, '_wcrp_rental_products_rental_purchase_price', true)) {
            $price = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation_id, '_wcrp_rental_products_rental_purchase_price', true));
        } else {
            $price = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation_id, '_price', true));
        }
    } else {
        if ('' !== get_post_meta($variation_id, '_wcrp_rental_products_rental_purchase_price', true)) {
            $price = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($product_id, '_wcrp_rental_products_rental_purchase_price', true));
        } else {
            $price = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($product_id, '_price', true));
        }
    }
    return $price;
}


