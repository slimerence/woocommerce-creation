<?php


function add_menu_item()
{
  add_menu_page(
    "Creations Setting Pannel",
    "Our Creations",
    "manage_options",
    PLUGIN_SLUG_NAME,
    "my_vue_panel_page",
    'dashicons-tickets-alt',
    5
  );
  add_submenu_page(
    PLUGIN_SLUG_NAME,        // parent slug
    'All Presets',             // page title
    'All Presets',             // menu title
    'manage_options',       // capability
    'edit.php?post_type=preset', // menu slug
  );
  add_submenu_page(
    PLUGIN_SLUG_NAME,        // parent slug
    'Create Preset',              // page title
    'Create Preset',              // menu title
    'manage_options',       // capability
    'our-creations-presets', // menu slug
    'our_creations_presets_callback' // callback function
  );
}

add_action("admin_menu", "add_menu_item");


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
    plugin_dir_url(__FILE__) . '../app/dist/js/chunk-vendors.js',
    array(),
    DEV_MODE ? time() : VERSION,
    true
  );
  wp_enqueue_script(
    PLUGIN_SLUG_NAME . '-main',
    plugin_dir_url(__FILE__) . '../app/dist/js/app.js',
    array(),
    DEV_MODE ? time() : VERSION,
    false
  );
  wp_enqueue_style(
    PLUGIN_SLUG_NAME . '-chunk',
    plugin_dir_url(__FILE__) . '../app/dist/css/chunk-vendors.css',
    array(),
    DEV_MODE ? time() : VERSION
  );
  wp_enqueue_style(
    PLUGIN_SLUG_NAME,
    plugin_dir_url(__FILE__) . '../app/dist/css/app.css',
    array(),
    DEV_MODE ? time() : VERSION
  );
}



function our_creations_presets_callback()
{

?>
  <script type="text/javascript">
    const vue_isadmin = true;
  </script>
  <div id="app"></div>
<?php
  wp_enqueue_style('chunk-style', plugin_dir_url(__FILE__) . '../planner/dist/css/chunk-vendors.css', array(), DEV_MODE ? time() : VERSION, 'all');
  wp_enqueue_style('main-style', plugin_dir_url(__FILE__) . '../planner/dist/css/app.css', array(), DEV_MODE ? time() : VERSION, 'all');
  wp_enqueue_script('chunk-js', plugin_dir_url(__FILE__) . '../planner/dist/js/chunk-vendors.js', NUll, DEV_MODE ? time() : VERSION, false);
  wp_enqueue_script('main-js', plugin_dir_url(__FILE__) . '../planner/dist/js/app.js', NUll, DEV_MODE ? time() : VERSION, false);
  wp_localize_script('main-js', 'kongfuseo_addon_data', array(
    'nonce' => wp_create_nonce('rental-cart')
  ));
}
