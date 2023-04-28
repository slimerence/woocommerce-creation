<?php


add_action('rest_api_init', function () {
  register_rest_route('cart/v1', '/price', array(
    'methods' => 'POST',
    'callback' => 'test_rental_price',
    'permission_callback' => '__return_true',

    // 'permission_callback' => function() {
    //     return current_user_can( 'edit_posts' );
    // },
  ));
});

function test_rental_price($request)
{
  $response = get_rental_item_price(813, '2023-04-30', '2023-05-04', 815);
  return wp_send_json($response, 200);
}

function get_rental_item_price($product_id, $rentFromDate, $rentToDate, $variation_id)
{
  $product = wc_get_product($product_id);
  $product_price = wc_get_price_to_display($product); // Inc or exc vat depending on settings
  $product_tax_class = $product->get_tax_class(); // May have been overridden earlier via cart_item_rental_purchase_overrides()
  $product_tax_status = $product->get_tax_status();
  $product_type = $product->get_type();
  $final_price = 0;
  $response = null;

  if (wcrp_rental_products_is_rental_only($product_id) || (wcrp_rental_products_is_rental_purchase($product_id))) {
    $default_rental_options = wcrp_rental_products_default_rental_options();

    $pricing_type = get_post_meta($product_id, '_wcrp_rental_products_pricing_type', true);
    $pricing_type = ('' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type']);

    $pricing_period = get_post_meta($product_id, '_wcrp_rental_products_pricing_period', true);
    $pricing_period = ('' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period']);

    $pricing_period_multiples = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_multiples', true);
    $pricing_period_multiples = ('' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples']);

    $pricing_period_multiples_maximum = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_multiples_maximum', true);
    $pricing_period_multiples_maximum = ('' !== $pricing_period_multiples_maximum ? $pricing_period_multiples_maximum : $default_rental_options['_wcrp_rental_products_pricing_period_multiples_maximum']);

    $pricing_period_additional_selections = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_additional_selections', true);
    $pricing_period_additional_selections = ('' !== $pricing_period_additional_selections ? $pricing_period_additional_selections : $default_rental_options['_wcrp_rental_products_pricing_period_additional_selections']);
    $pricing_period_additional_selections_array = WCRP_Rental_Products_Misc::days_colon_value_pipe_explode($pricing_period_additional_selections, false);

    $pricing_tiers = get_post_meta($product_id, '_wcrp_rental_products_pricing_tiers', true);
    $pricing_tiers = ('' !== $pricing_tiers ? $pricing_tiers : $default_rental_options['_wcrp_rental_products_pricing_tiers']);

    $pricing_tiers_data = get_post_meta($product_id, '_wcrp_rental_products_pricing_tiers_data', true);
    $pricing_tiers_data = ('' !== $pricing_tiers_data ? $pricing_tiers_data : $default_rental_options['_wcrp_rental_products_pricing_tiers_data']);

    $price_additional_periods_percent = get_post_meta($product_id, '_wcrp_rental_products_price_additional_periods_percent', true);
    $price_additional_periods_percent = ('' !== $price_additional_periods_percent ? $price_additional_periods_percent : $default_rental_options['_wcrp_rental_products_price_additional_periods_percent']);

    $price_additional_period_percent = get_post_meta($product_id, '_wcrp_rental_products_price_additional_period_percent', true);
    $price_additional_period_percent = ('' !== $price_additional_period_percent ? $price_additional_period_percent : $default_rental_options['_wcrp_rental_products_price_additional_period_percent']);

    $default_rental_options = wcrp_rental_products_default_rental_options();
    $prices_include_tax = get_option('woocommerce_prices_include_tax');
    $price_decimals = wc_get_price_decimals();
    $price_decimal_separator = wc_get_price_decimal_separator();
    $taxes_enabled = get_option('woocommerce_calc_taxes');
    $tax_display_shop = get_option('woocommerce_tax_display_shop');

    $product_taxes = WC_Tax::get_rates($product_tax_class);

    if (!empty($product_taxes)) { // This ensures array_shift does not cause fatal error if empty, WooCommerce Tax extension can return this empty when the automated taxes option is enabled

      $product_tax_rates = array_shift($product_taxes);
      $product_tax_rate = round(array_shift($product_tax_rates));
    } else {

      $product_tax_rate = 0;
    }

    if ('period_selection' == $pricing_type) {

      // Total overrides are not used for the period selection pricing type, this is condition exists because the meta may still exist containing total overrides when product was previously a different pricing type
      $total_overrides = '';
      $total_overrides_json = '{}';
    } else {

      $total_overrides = get_post_meta($product_id, '_wcrp_rental_products_total_overrides', true);
      $total_overrides = ('' !== $total_overrides ? $total_overrides : $default_rental_options['_wcrp_rental_products_total_overrides']);
      $total_overrides_json = WCRP_Rental_Products_Misc::days_colon_value_pipe_explode($total_overrides, true);
    }

    // 处理全局设置

    $totalOverrides = json_decode($total_overrides_json);
    $rentFromDateObject = new DateTime($rentFromDate);
    $rentToDateObject = new DateTime($rentToDate);
    $diff = $rentFromDateObject->diff($rentToDateObject);
    $rentedDays = intval($diff->format('%r%a')) + 1;


    if (property_exists($totalOverrides, $rentedDays)) {
      $totalOverridesPrice = $totalOverrides->$rentedDays;
      if ('yes' == $taxes_enabled) {

        if ('taxable' == $product_tax_status) {
          if ('no' == $prices_include_tax && 'incl' == $tax_display_shop) {
            $totalOverridesPrice = $totalOverridesPrice * (1 + ($product_tax_rate / 100));
          } elseif ('yes' == $prices_include_tax && 'excl' == $tax_display_shop) {
            $totalOverridesPrice = $totalOverridesPrice / (1 + ($product_tax_rate / 100));
          }
        }
      }
      $final_price = $totalOverridesPrice;
    } else {

      // 首先处理rentalPrice
      $maybe_use_pricing_period_additional_selections_price = false;
      if (wcrp_rental_products_is_rental_purchase($product_id)) {
        $rentalPrice = floatval(wc_get_price_to_display($product, array('price' => str_replace($price_decimal_separator, '.', get_post_meta($product_id, '_wcrp_rental_products_rental_purchase_price', true)))));
        if ('period_selection' == $pricing_type) {
          $maybe_use_pricing_period_additional_selections_price = true;
        }
      } else {
        $rentalPrice = floatval($product_price);
        if ('period_selection' == $pricing_type) {
          $maybe_use_pricing_period_additional_selections_price = true;
        }
      }

      $rent_period = false;
      if (true == $maybe_use_pricing_period_additional_selections_price) {
        if (false !== $rent_period) {
          if (array_key_exists($rent_period, $pricing_period_additional_selections_array)) {
            $rentalPrice = floatval(wc_get_price_to_display($product, array('price' => str_replace($price_decimal_separator, '.', $pricing_period_additional_selections_array[$rent_period]))));
          }
        }
      }

      // variation product
      if ('variable' == $product_type && isset($variation_id)) {
        // $variation = wc_get_product($variation_id);
        if (wcrp_rental_products_is_rental_purchase($product_id)) {
          $priceStr = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation_id, '_wcrp_rental_products_rental_purchase_price', true));
        } else {
          $priceStr = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation_id, '_price', true));
        }
        $rentalPrice = floatval($priceStr);
      }

      $pricingTierPercent = 0; // If there aren't any matching days greater than the rental days then % is 0 (no change)
      $pricingTierHighest = 0;
      if (!empty($pricing_tiers_data)) {
        for ($i = 0; $i < count($pricing_tiers_data['days']); $i++) {

          // Highest used as days maybe unordered e.g. 1 is 10%, 5 is 20%, 3 is 15% so we want to use the highest
          if (intval($pricing_tiers_data['days'][$i]) > $pricingTierHighest) { // intval as days

            if ($rentedDays > intval($pricing_tiers_data['days'][$i])) {

              $pricingTierHighest = intval($pricing_tiers_data['days'][$i]); // intval as days
              $pricingTierPercent = floatval($pricing_tiers_data['percent'][$i]); // floatval as can be multiple decimal places

            }
          }
        }
      }
      if ($pricingTierPercent > 0) { // If positive
        $percentMultiplier = 1 + ($pricingTierPercent / 100);
      } else { // If negative
        $percentMultiplier = 1 - (abs($pricingTierPercent) / 100);
      }


      if ('fixed' == $pricing_type) {
        if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {
          $final_price = $rentalPrice * $percentMultiplier;
        } else {
          $final_price = $rentalPrice;
        }
      } elseif ('period' == $pricing_type) {
        if ('1' !== $pricing_period) {

          if ('yes' == $pricing_period_multiples) {

            if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {

              if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {
                $final_price = ($rentalPrice + ((($rentalPrice * $price_additional_period_percent) / 100) * (($rentedDays / $pricing_period) - 1))) * $percentMultiplier;
              } else {
                $final_price =  $rentalPrice * ($rentedDays / $pricing_period) * $percentMultiplier;
              }
            } else {

              if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {
                $final_price = $rentalPrice + ((($rentalPrice * $price_additional_period_percent) / 100) * (($rentedDays / $pricing_period) - 1));
              } else {
                $final_price = $rentalPrice * ($rentedDays / $pricing_period);
              }
            }
          } else {
            $final_price = $rentalPrice;
          }
        } else {

          if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {

            if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {
              $final_price = ($rentalPrice + ((($rentalPrice * $price_additional_period_percent) / 100) * ($rentedDays - 1))) * $percentMultiplier;
            } else {
              $final_price = $rentalPrice * $rentedDays * $percentMultiplier;
            }
          } else {

            if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {
              $final_price = $rentalPrice + ((($rentalPrice * $price_additional_period_percent) / 100) * ($rentedDays - 1));
            } else {
              $final_price = $rentalPrice * $rentedDays;
            }
          }
        }
      } elseif ('period_selection' == $pricing_type) {
        $periodSelectionPrice = $rentalPrice;
        $final_price = $periodSelectionPrice;
      }
    }


    $response = $final_price;
    return $response;
  } else {
    return null;
  }
}
