<?php



add_action('rest_api_init', function () {
  register_rest_route(PLUGIN_SLUG_NAME, '/products/(?P<category_id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'list_product_by_category',
    'args' => array(
      'category_id' => array(
        'required' => true,
        'validate_callback' => function ($param, $request, $key) {
          return is_numeric($param);
        },
        'sanitize_callback' => 'absint',
      ),
    ),
  ));
});



function list_product_by_category($request)
{
  $category_id = $request->get_param('category_id');
  // check rentalproduct available dates

  $from_date = $request->get_param('fromDate');
  $end_date =  $request->get_param('endDate');

  $args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'tax_query' => array(
      array(
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $category_id
      )
    )
  );

  $query = new WC_Product_Query($args);
  $products = $query->get_products();

  if (empty($products)) {
    return array();
  }
  $response = array();
  foreach ($products as $product) {
    $product_id = $product->get_id();
    $is_rental = wcrp_rental_products_is_rental_only($product_id);
    $is_rental_purchase = wcrp_rental_products_is_rental_purchase($product_id);
    if ($is_rental || $is_rental_purchase) {
      $product_data = array(
        'id' => $product->get_id(),
        'name' => $product->get_name(),
        'sku' => $product->get_sku(),
        'type' => $product->get_type(),
        'price' => $product->get_price(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price(),
        'permalink' => $product->get_permalink(),
        'images' => array(),
        'feature_image' => wp_get_attachment_image_src($product->get_image_id(), 'full')[0],
        'categories' => array(),
        'tags' => array(),
        'attributes' => array(),
        'variations' => get_wc_rental_product_variations($product_id, $from_date, $end_date),
        'dimensions' => $product->get_dimensions(false),
        'min_price' => get_wc_rental_product_min_prices($product_id),
        'rental_only' => $is_rental,
        'rental_purchase' => $is_rental_purchase,
        'rent_available' => wcrp_rental_products_check_availability($product_id, $from_date, $end_date, 1),
      );
      $product_images = $product->get_gallery_image_ids();

      foreach ($product_images as $image_id) {
        $product_data['images'][] = wp_get_attachment_url($image_id);
      }
      $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
      foreach ($product_categories as $category) {
        $product_data['categories'][] = $category;
      }
      $product_tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
      foreach ($product_tags as $tag) {
        $product_data['tags'][] = $tag;
      }
      $product_attributes = $product->get_attributes();
      foreach ($product_attributes as $attribute_name => $attribute) {
        $product_data['attributes'][] = array(
          'name' => $attribute_name,
          'value' => $attribute->get_options(),
        );
      }
      // array_push($response,$product_data);
      $response[] = $product_data;
    }
  }

  return $response;
}

function get_wc_rental_product_variations($product_id, $from_date, $end_date)
{
  $product = wc_get_product($product_id);
  $type = $product->get_type();
  $response = array();
  if ($type === 'variable') {
    $variations = $product->get_available_variations();
    foreach ($variations as $variation) {
      $variation_id = $variation['variation_id'];
      $variation['rent_available'] = wcrp_rental_products_check_availability($variation_id, $from_date, $end_date, 1);
      $response[] = $variation;
    }
  }
  return $response;
}

function get_wc_rental_product_min_prices($product_id)
{
  $product = wc_get_product($product_id);
  $type = $product->get_type();

  $variations = $product->get_children();
  if (!empty($variations) || $type === 'variable') {

    $variation_prices = array();

    foreach ($variations as $variation) {

      if ('' !== get_post_meta($variation, '_wcrp_rental_products_rental_purchase_price', true)) {

        // str_replace happens incase the store uses a different decimal seperator and this is the _wcrp_rental_products_rental_purchase_price field, unlike normal price fields which even though entered as 1,00 get saved as 1.00, this field saves using the decimal seperator in the value, so we have to set it to the . character, otherwise the calculations later on would not work correctly. Do not need to worry about the thousand seperator as this cannot be entered on the field itself and is only used during display

        $variation_prices[] = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation, '_wcrp_rental_products_rental_purchase_price', true));
      } else {
        $variation_prices[] = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($variation, '_price', true));
      }
    }
    if (!empty($variation_prices)) {
      return min($variation_prices);
    } else {
      // rental_only
      return '';
    }
    // if (!empty($variation_prices)) {

    //   $product_price = wc_get_price_to_display($product, array('price' => min($variation_prices)));
    // } else {

    //   return ''; // If no prices set don't display

    // }
  } else {
    $product_price = wc_get_price_to_display($product, array('price' => str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($product_id, '_wcrp_rental_products_rental_purchase_price', true))));
    return $product_price;
  }
}
