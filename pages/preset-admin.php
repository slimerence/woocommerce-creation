<?php
// Register Custom Post Type
function custom_post_type_presets()
{

  $args = array(
    'label'                 => __('Presets', 'text_domain'),
    'description'           => __('Custom post type for presets', 'text_domain'),
    'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
    'taxonomies'            => array('category', 'post_tag'),
    'hierarchical'          => false,
    'public'                => false,
    'show_ui'               => true,
    'show_in_rest'          => true,
    'show_in_menu'          => true,
    'menu_position'         => 5,
    'menu_icon'             => 'dashicons-art',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => true,
    
  );

  register_post_type('preset', $args);
}
add_action('init', 'custom_post_type_presets', 0);


// Disable "Add New" for Presets
function disable_presets_add_new() {
  global $submenu;
  unset($submenu['edit.php?post_type=preset'][10]); // Remove "Add New"
}
add_action('admin_menu', 'disable_presets_add_new');

// Register the 'image' field as post meta
function register_preset_image_field() {
  register_meta(
      'post',
      'image',
      array(
          'type' => 'string',
          'description' => 'Custom field for the image URL of a preset',
          'single' => true,
          'show_in_rest' => true // Enable the field in the REST API
      )
  );
}
add_action( 'init', 'register_preset_image_field' );


// Add a custom column to the 'Presets' list table for the 'image' field
function add_preset_image_column( $columns ) {
  $columns['preset_image'] = 'Image';
  return $columns;
}
add_filter( 'manage_preset_posts_columns', 'add_preset_image_column' );

// Populate the 'image' column with the 'image' field value
function populate_preset_image_column( $column_name, $post_id ) {
  if ( $column_name == 'preset_image' ) {
      $image_url = get_post_meta( $post_id, 'image', true );
      if ( $image_url ) {
          echo '<img src="' . esc_url( $image_url ) . '" width="100" height="100" />';
      }
  }
}
add_action( 'manage_preset_posts_custom_column', 'populate_preset_image_column', 10, 2 );



// add_action( 'rest_api_init', function () {
//   register_post_type( 'presets',
//       array(
//           'labels' => array(
//               'name' => __( 'Presets' ),
//               'singular_name' => __( 'Preset' )
//           ),
//           'public' => true,
//           'has_archive' => true,
//           'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' )
//       )
//   );
// } );

function create_preset_post( $request ) {
  $params = $request->get_params();

  // Create the post
  $post_id = wp_insert_post( array(
      'post_title' => $params['title'],
      'post_content' => $params['content'],
      'post_status' => 'publish',
      'post_type' => 'preset',
  ) );

  // Add the custom image field to the post meta
  update_post_meta( $post_id, 'image', $params['image'] );

  // Return the new post ID
  return $post_id;
}


add_action( 'rest_api_init', function () {
  register_rest_route( 'preset/v1', '/create', array(
      'methods' => 'POST',
      'callback' => 'create_preset_post',
      'permission_callback' => '__return_true',
      // 'permission_callback' => function() {
      //     return current_user_can( 'edit_posts' );
      // },
  ) );
} );


add_action('rest_api_init', function () {
  register_rest_route('preset/v1', '/get', array(
    'methods' => 'GET',
    'callback' => 'kongfuseo_get_presets',
    'permission_callback' => '__return_true',

  ));
});

function kongfuseo_get_presets($request) {
  $args = array(
    'post_type' => 'preset',
    'posts_per_page' => -1,
  );
  $presets = get_posts($args);
  $formatted_presets = array();
  foreach ($presets as $preset) {
    $preset_id = $preset->ID;
    $preset_categories = wp_get_post_categories($preset_id);
    $preset_tags = wp_get_post_tags($preset_id);
    $formatted_preset = array(
      'id' => $preset->ID,
      'title' => $preset->post_title,
      'content' => $preset->post_content,
      'categories' => $preset_categories,
      'tags' => $preset_tags,
      'image' => get_post_meta($preset->ID, 'image', true),
    );
    array_push($formatted_presets, $formatted_preset);
  }

  
  return $formatted_presets;
}
