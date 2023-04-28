<?php

/************************** 数据库初始化BEGIN ***************************/
const PRESET_NAME = 'creation_preset';

function kongfu_database_create()
{
  global $wpdb;
  $table_name = $wpdb->prefix . PRESET_NAME;
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
		 id mediumint(9) NOT NULL AUTO_INCREMENT,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            title varchar(255) NOT NULL DEFAULT '',
            text text NOT NULL,
            `image_url` varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
	) $charset_collate;";


  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);

  add_option('kongfu_db_version', VERSION);

  // 版本升级更新数据库使用
  $installed_ver = get_option("kongfu_db_version");

  if ($installed_ver != VERSION) {

    $table_name = $wpdb->prefix . PRESET_NAME;

    $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            title varchar(255) NOT NULL DEFAULT '',
            text text NOT NULL,
            `image_url` varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
        );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    update_option("kongfu_db_version", $kongfu_db_version);
  }
}

function kongfu_install_data()
{
  global $wpdb;

  $welcome_name = 'Test';
  $welcome_text = '';

  $table_name = $wpdb->prefix . PRESET_NAME;

  $wpdb->insert(
    $table_name,
    array(
      'time' => current_time('mysql'),
      'title' => $welcome_name,
      'text' => $welcome_text,
    )
  );
}

// register_activation_hook(__FILE__, 'kongfu_database_create');
// register_activation_hook(__FILE__, 'kongfu_install_data');


function myplugin_update_db_check()
{
  if (get_site_option('kongfu_db_version') != VERSION) {
    kongfu_database_create();
  }
}

// add_action('plugins_loaded', 'myplugin_update_db_check');

/************************** 数据库初始化END ***************************/


// add_action('rest_api_init', function () {
//   register_rest_route(PLUGIN_SLUG_NAME, '/preset/fetch', array(
//     'methods' => 'POST',
//     'callback' => 'get_preset_func',
//   ));
// });

add_action('rest_api_init', function () {
  register_rest_route(PLUGIN_SLUG_NAME, '/preset/post', array(
    'methods' => 'POST',
    'callback' => 'post_preset_func',
    'permission_callback' => '__return_true',

  ));
});

add_action('rest_api_init', function () {
  register_rest_route(PLUGIN_SLUG_NAME, '/preset/delete', array(
    'methods' => 'POST',
    'callback' => 'delete_preset_func',
    'permission_callback' => '__return_true',

  ));
});

/** 删除接口，支持批量删除 */
function delete_preset_func($request)
{
  // global $wpdb;
  // $table_name = $wpdb->prefix . PRESET_NAME;
  // $ids = $request['ids'];
  // $ids = implode(',', array_map('absint', $ids));
  // return $wpdb->query("DELETE FROM $table_name WHERE ID IN($ids)");
  $id = $request->get_param('id');

  // if (!current_user_can('delete_post', $id)) {
  //   return new WP_Error('rest_forbidden', __('You do not have permission to delete this preset.'), array('status' => 403));
  // }

  $result = wp_delete_post($id, true);

  if ($result === false) {
    return new WP_Error('rest_cannot_delete', __('Unable to delete preset.'), array('status' => 500));
  }

  return new WP_REST_Response(array('success' => true), 200);
}

/** 获取接口，支持分页 */
function get_preset_func($request)
{
  global $wpdb;
  $table_name = $wpdb->prefix . PRESET_NAME;
  $page = isset($request['page']) ? $request['page'] : 1;
  $size = isset($request['pageSize']) ? $request['pageSize'] : 10;
  $offset = $size * ($page - 1);
  $presets = $wpdb->get_results(
    "
            SELECT * FROM $table_name
            LIMIT $size
            OFFSET $offset
        "
  );
  $count_query = "select count(*) from $table_name";
  $num = $wpdb->get_var($count_query);

  return wp_send_json([
    'data' => $presets,
    'page' => $page,
    'size' => $size,
    'total' => $num,
  ]);
}

/** 更新数据 */
function post_preset_func($request)
{
  global $wpdb;
  $table_name = $wpdb->prefix . PRESET_NAME;
  $result = null;
  if (isset($request['id']) && !is_null($request['id'])) {

    $result = $wpdb->update(
      $table_name,
      array(
        'time' => current_time('mysql'),
        'title' => $request['title'],
        'text' => $request['text'],
        'image_url' => $request['image_url']
      ),
      array('ID' => $request['id']),
      array(
        '%s',    // value1
        '%s'    // value2
      ),
      array('%d')
    );
  } else {
    $result = $wpdb->insert(
      $table_name,
      array(
        'time' => current_time('mysql'),
        'title' => $request['title'],
        'text' => $request['text'],
        'image_url' => $request['image_url']
      )
    );
  }
  // check if data was successfully inserted
  if (!$result) {
    $error = new WP_Error('database_error', __('There was an error inserting the data'), array('status' => 500));
    return $error;
  }

  // return a response to the API request
  return new WP_REST_Response(array('success' => true));
}
