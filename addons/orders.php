<?php

add_action('woocommerce_after_order_notes', 'creation_custom_checkout_field');

function creation_custom_checkout_field($checkout)
{

  $creation_url = isset($_COOKIE['creation_url']) ? $_COOKIE['creation_url'] : '';

  echo '<div id="creation_custom_checkout_field"><h3>' . __('Creation') . '</h3>';

  woocommerce_form_field('creation_image_url', array(
    'type'          => 'text',
    'class'         => array('my-field-class form-row-wide'),
    'label'         => __('Image URL'),
    'default'       => $creation_url,
    'custom_attributes' => array(
      'readonly' => 'readonly', // Set the field to readonly
      // 'style' => 'display:none;', // Hide the field using CSS
    ),
  ), $checkout->get_value('creation_image_url'));

  // Render the image if the field is not empty
  $my_image_url = $checkout->get_value('creation_image_url');
  if (!empty($my_image_url)) {
    echo '<img src="' . esc_url($my_image_url) . '" alt="My Image" />';
  }

  echo '</div>';
}

add_action('woocommerce_checkout_create_order', 'creation_custom_checkout_field_update_order_meta', 10, 2);

function creation_custom_checkout_field_update_order_meta($order, $data)
{
  if (!empty($_POST['creation_image_url'])) {
    $order->update_meta_data('creation_image_url', sanitize_text_field($_POST['creation_image_url']));
  }
}


add_action('woocommerce_order_details_after_order_table', 'creation_custom_checkout_field_display_order_meta', 10, 1);

function creation_custom_checkout_field_display_order_meta($order)
{
  echo '<p><strong>' . __('My Image') . ':</strong> ' . get_post_meta($order->get_id(), 'creation_image_url', true) . '</p>';
}
