<?php

add_action('woocommerce_after_order_notes', 'creation_custom_checkout_field');

function creation_custom_checkout_field($checkout)
{

  $creation_url = isset($_COOKIE['creation_url']) ? $_COOKIE['creation_url'] : '';
  if (!empty($creation_url)) {
    echo '<div id="creation_custom_checkout_field"><h3>' . __('Creation') . '</h3>';
  }
  woocommerce_form_field('creation_image_url', array(
    'type'          => 'hidden',
    'class'         => array('my-field-class form-row-wide'),
    'default'       => $creation_url,
    'custom_attributes' => array(
      'readonly' => 'readonly', // Set the field to readonly
      'style' => 'display:none;', // Hide the field using CSS
    ),
  ), $checkout->get_value('creation_image_url'));

  // Render the image if the field is not empty
  if (!empty($creation_url)) {
    echo '<img src="' . esc_url($creation_url) . '" alt="My Image" />';
    echo '</div>';
  }
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
  $creation_url = get_post_meta($order->get_id(), 'creation_image_url', true);
  if (!empty($creation_url)) {
    echo '<p><strong>' . __("Creation Image") . '</strong> </p>';
    echo '<img src="' . esc_url($creation_url) . '" alt="' . __("My Image") . '" />';
  }
}


// Add 'My Image' column to the orders list in the backend
add_filter('manage_edit-shop_order_columns', 'my_custom_checkout_field_add_order_column');
function my_custom_checkout_field_add_order_column($columns)
{
  $columns['creation_image'] = __('Creation');
  return $columns;
}

// Populate 'My Image' column in the orders list in the backend
add_action('manage_shop_order_posts_custom_column', 'my_custom_checkout_field_add_order_column_content');
function my_custom_checkout_field_add_order_column_content($column)
{
  global $post;
  if ($column === 'creation_image') {
    $creation_image_url = get_post_meta($post->ID, 'creation_image_url', true);
    if (!empty($creation_image_url)) {
      echo '<div><a style="display:inline-block;" href="' . esc_url($creation_image_url) . '" target="_blank"><img src="' . esc_url($creation_image_url) . '" alt="' . __("My Image") . '" style="width:50px;height:50px;" /></a></div>';
    }
  }
}


// Add custom column to orders list
add_filter('manage_edit-shop_order_columns', 'custom_shop_order_column', 20);
function custom_shop_order_column($columns)
{
  $new_columns = (is_array($columns)) ? $columns : array();
  // Add the new column after the "Order Total" column
  $new_columns['order_fees'] = __('Order Fees', 'woocommerce');
  return $new_columns;
}

// Populate the custom column with data
add_action('manage_shop_order_posts_custom_column', 'custom_shop_order_column_content', 20);
function custom_shop_order_column_content($column)
{
  global $post, $woocommerce, $the_order;
  if ($column == 'order_fees') {
    $fees = $the_order->get_fees();
    $total_fees = 0;
    if ($fees) {
      foreach ($fees as $fee) {
        $fee_amount = wc_format_decimal($fee['amount']);
        $total_fees += $fee_amount;
      }
      echo wc_price($total_fees);
    } else {
      echo '-';
    }
  }
}


// Add 'My Image' field to the new order email
add_filter('woocommerce_email_order_meta_fields', 'my_custom_checkout_field_add_order_email_field', 10, 3);
function my_custom_checkout_field_add_order_email_field($fields, $sent_to_admin, $order)
{
  $creation_image_url = $order->get_meta('creation_image_url');
  if (!empty($creation_image_url)) {
    $fields['creation_image_url'] = array(
      'label' => __('Creation Image'),
      'value' => '<img src="' . esc_url($creation_image_url) . '" alt="' . __("My Image") . '" style="width:150px;height:150px;" />'
    );
  }
  return $fields;
}



// Add pickup date and time fields to the order review section
function add_pickup_date_and_time_to_order_review($checkout)
{
  // Get pickup date and time from checkout session data
  $pickup_date = $checkout->get_value('pickup_date');
  $pickup_time = $checkout->get_value('pickup_time');

  echo '<div class="pickup-date">';
  woocommerce_form_field('pickup_date', array(
    'type' => 'date',
    'label' => __('Pickup Date', 'text-domain'),
    'value' => $pickup_date,
  ), $pickup_date);
  echo '</div>';

  echo '<div class="pickup-time">';
  woocommerce_form_field('pickup_time', array(
    'type' => 'time',
    'label' => __('Pickup Time', 'text-domain'),
    'value' => $pickup_time,
  ), $pickup_time);
  echo '</div>';
}
add_action('woocommerce_after_order_notes', 'add_pickup_date_and_time_to_order_review');


// Save pickup date and time fields to order meta data
function save_pickup_date_and_time_to_order_meta_data($order, $data)
{
  if (!empty($_POST['pickup_date'])) {
    $order->update_meta_data('pickup_date', sanitize_text_field($_POST['pickup_date']));
  }
  if (!empty($_POST['pickup_time'])) {
    $order->update_meta_data('pickup_time', sanitize_text_field($_POST['pickup_time']));
  }
}
add_action('woocommerce_checkout_create_order', 'save_pickup_date_and_time_to_order_meta_data', 10, 2);

// Display pickup date and time fields in backend order details
function display_pickup_date_and_time_in_order_details($order)
{
  $pickup_date = $order->get_meta('pickup_date');
  $pickup_time = $order->get_meta('pickup_time');

  if (!empty($pickup_date)) {
    echo '<p><strong>' . __('Pickup Date', 'text-domain') . ':</strong> ' . $pickup_date . '</p>';
  }

  if (!empty($pickup_time)) {
    echo '<p><strong>' . __('Pickup Time', 'text-domain') . ':</strong> ' . $pickup_time . '</p>';
  }
}
add_action('woocommerce_order_details_after_order_table', 'display_pickup_date_and_time_in_order_details', 10, 1);

// Display pickup date and time fields in order email
function display_pickup_date_and_time_in_order_email($order, $sent_to_admin, $plain_text, $email)
{
  $pickup_date = $order->get_meta('pickup_date');
  $pickup_time = $order->get_meta('pickup_time');

  if (!empty($pickup_date)) {
    echo '<p><strong>' . __('Pickup Date', 'text-domain') . ':</strong> ' . $pickup_date . '</p>';
  }

  if (!empty($pickup_time)) {
    echo '<p><strong>' . __('Pickup Time', 'text-domain') . ':</strong> ' . $pickup_time . '</p>';
  }
}
add_action('woocommerce_email_order_details', 'display_pickup_date_and_time_in_order_email', 10, 4);


function add_custom_checkout_scripts()
{
  if (is_checkout()) {
    wp_enqueue_script('custom-checkout', plugin_dir_url(__FILE__) . '../assets/enhanced.js', array('jquery'), '1.0', true);
  }
}
add_action('wp_enqueue_scripts', 'add_custom_checkout_scripts');


// Display pickup date and time in backend order details detail block
function display_pickup_date_and_time_in_backend_order_details($order)
{
  $pickup_date = $order->get_meta('pickup_date');
  $pickup_time = $order->get_meta('pickup_time');

  if ($pickup_date) {
    echo '<p class="form-field form-field-wide">';
    echo '<strong>Pickup Date:</strong> ' . esc_html($pickup_date) . '<br/>';

    if ($pickup_time) {
      echo '<p><strong>Pickup Time:</strong> ' . esc_html($pickup_time) . '</p>';
    }
    echo '</p>';
  }
}
add_action('woocommerce_admin_order_data_after_order_details', 'display_pickup_date_and_time_in_backend_order_details', 10, 1);
