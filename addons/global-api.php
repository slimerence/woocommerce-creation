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

add_action('rest_api_init', 'create_image_upload_endpoint');

function create_image_upload_endpoint()
{
  register_rest_route(PLUGIN_SLUG_NAME, '/image-upload', array(
    'methods' => 'POST',
    'callback' => 'handle_image_upload',
    // 'permission_callback' => function () {
    //   return current_user_can('edit_posts');
    // },
  ));
}

function handle_image_upload($request)
{
  $file = $request->get_file_params()['file'];
  $upload_overrides = array('test_form' => false);
  $movefile = wp_handle_upload($file, $upload_overrides);

  if ($movefile && !isset($movefile['error'])) {
    $url = $movefile['url'];
    return array('url' => $url);
  } else {
    return new WP_Error('upload_error', $movefile['error']);
  }
}
