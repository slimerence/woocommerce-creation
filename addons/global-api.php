<?php


add_action('rest_api_init', function () {
  register_rest_route(PLUGIN_SLUG_NAME, '/rental-option/get', array(
      'methods' => 'GET',
      'callback' => 'get_rental_global_option',
  ));
});

function get_rental_global_option($request)
{
  $default_rental_options = wcrp_rental_products_default_rental_options();
  return $default_rental_options;
}