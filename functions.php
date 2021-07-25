<?php

function  posts_endpoint($request_data) {
    $posttype = explode("/", $_SERVER['REQUEST_URI'])[3];
    $args = array(
        'post_type' => $posttype,
        'posts_per_page'=>-1, 
        'numberposts'=>-1
    );
    
    $posts = get_posts($args);
    foreach ($posts as $key => $post) {
        $posts[$key]->acf = get_fields($post->ID);
    }
    return  $posts;
}

function post_single($slug) {
    $posttype = explode("/", $_SERVER['REQUEST_URI'])[3];
    $args = [
      'name' => $slug['slug'],
      'post_type' => $posttype
    ];

    $posts = get_posts($args);
    foreach ($posts as $key => $post) {
      $posts[$key]->acf = get_fields($post->ID);
    }
    $content = apply_filters('the_content', $posts[0]->post_content);
    $obj_merged = (object) array_merge((array) $posts[0], array("content"=>$content));
    return $obj_merged;
}

function custom_wp_menu() {
    return wp_get_nav_menu_items('Navigation Menu');
}

function register_menu() {
  register_nav_menus(
    array(
      'navigation-menu' => __( 'Navigation Menu' )
    )
  );
}

function event_post_type() {
  register_post_type('event',
    array(
      'rewrite' => array('slug' => 'events'),
      'labels' => array(
        'name' => 'Events',
        'singular_name' => 'Event',
        'add_new_item' => 'Add New Event',
        'edit_item' => 'Edit Event'
      ),
      'menu_icon' => 'dashicons-calendar',
      'public' => true,
      'has_archive' => false,
      'supports' => array(
        'title', 'thumbnail', 'editor', 'excerpt'
      )
    )
  );
}

function team_member_post_type() {
  register_post_type('team-member',
    array(
      'rewrite' => array('slug' => 'team-members'),
      'labels' => array(
        'name' => 'Team Members',
        'singular_name' => 'Team Member',
        'add_new_item' => 'Add New Team Member',
        'edit_item' => 'Edit Team Member'
      ),
      'menu_icon' => 'dashicons-admin-users',
      'public' => true,
      'has_archive' => false,
      'supports' => array(
        'title', 'thumbnail', 'excerpt'
      )
    )
  );
}

function style_select_button( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}

function insert_formats( $init_array ) {
     $style_formats=array(
        array(
            'title' => 'Button',
            'block' => 'a',
            'classes' => 'button',
            'wrapper' => false,     
        ),
    );
 
    $init_array['style_formats'] = json_encode( $style_formats );
    return $init_array;
}

function mte_add_editor_styles() {
  add_editor_style( 'editor-style.css' );
}

add_filter( 'mce_buttons_2', 'style_select_button' );
add_filter( 'tiny_mce_before_init', 'insert_formats' );
add_action( 'init', 'mte_add_editor_styles' );

add_action('init', 'register_menu' );
add_action('init', 'event_post_type');
add_action('init', 'team_member_post_type');

add_theme_support('post-thumbnails');

add_action( 'rest_api_init', function () {
  register_rest_route( 'api', '/post/', array(
    'methods' => 'GET',
    'callback' => 'posts_endpoint'
  ));
  register_rest_route( 'api', '/event/', array(
    'methods' => 'GET',
    'callback' => 'posts_endpoint'
  ));
  register_rest_route( 'api', '/team-member/', array(
    'methods' => 'GET',
    'callback' => 'posts_endpoint'
  ));
  register_rest_route( 'api', '/menu/', array(
    'methods' => 'GET',
    'callback' => 'custom_wp_menu',
  ));
  register_rest_route('api', '/post/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'post_single',
  ));
  register_rest_route('api', '/event/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'post_single',
  ));
  register_rest_route('api', '/page/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'post_single',
  ));
  register_rest_route('api', '/team-member/(?P<slug>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'post_single',
  ));
});

?>