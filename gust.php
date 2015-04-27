<?php
/*
Plugin Name: Gust
Plugin URI: https://github.com/ideag/gust
Description: A port of the Ghost admin interface
Author: Arūnas Liuiza
Version: 0.4.1
Author URI: http://arunas.co/
*/
define ('GUST_SUBPATH',       gust_get_subpath());
define ('GUST_TITLE',         __('Gust','gust'));
define ('GUST_VERSION',       'v0.4.1');
define ('GUST_PLUGIN_PATH',   plugin_dir_path(__FILE__));
define ('GUST_PLUGIN_URL',    plugin_dir_url(__FILE__));

// === ACTIVATION
register_activation_hook(__FILE__,'gust_install');
function gust_install(){
  gust_init_rewrites();
  flush_rewrite_rules();
  gust_permalink_check();
}

// === FILTERS/ACTIONS
// init options
require_once('gust.class.php');
add_action('init', array( 'Gust', 'init_options' ), 100 );
// init rewrites
add_action('init','gust_init_rewrites', 101);
add_action('pre_get_posts','gust_drop_in',1);
// monitor for permalink changes
add_action('admin_init','gust_permalink_check');
// filter WordPres Admin Bar
add_action( 'admin_bar_menu', 'gust_admin_bar_filter', 999 );
// filter "Edit post" link in frontend/Admin bar
add_filter('get_edit_post_link','gust_edit_post_link',10,3);
add_filter('post_row_actions', 'gust_edit_post_link_admin', 10, 2);
add_filter('page_row_actions', 'gust_edit_post_link_admin', 10, 2);

function gust_permalink_check(){
  if (!gust_is_pretty_permalinks()) {
    add_action( 'admin_notices', 'gust_no_permalink_notice',1000 );
  }
}
function gust_no_permalink_notice() {
    ?>
    <div class="error">
        <p><?php _e('Gust: You do not use pretty permalinks. Please enable them <a href="options-permalink.php">here</a> to use Gust.', 'gust' ); ?></p>
    </div>
    <?php
}

function gust_is_pretty_permalinks(){
  global $wp_rewrite;
  if ($wp_rewrite->permalink_structure == '')
    return false;
  else
    return true;
}

function gust_init_rewrites() {
  add_rewrite_tag( '%gust_api%', '(ghost|'.GUST_NAME.'|api)');
  add_rewrite_tag( '%gust_q%', '(.*)');
  add_permastruct('gust_calls', '%gust_api%/%gust_q%',array('with_front'=>false));
}

function gust_drop_in($q) {
  if ((get_query_var('gust_api')=='ghost'||get_query_var('gust_api')==GUST_NAME||get_query_var('gust_api')=='api' )&& $q->is_main_query()) {
    define('WP_ADMIN',true);
    require_once(GUST_PLUGIN_PATH.'/assets/dispatch/dispatch.php');
    D::config('dispatch.views', GUST_PLUGIN_PATH.'views');
    D::config('dispatch.layout', false);
    D::config('dispatch.url', get_bloginfo('url'));
    $posttypes = implode('|',array_keys(Gust::$options['main_posttypes']));
    $taxonomies = implode('|',get_taxonomies(array('show_ui'=>true)));
    if (get_query_var('gust_api')=='api' && $q->is_main_query()) {
      require_once('gust-api.php');
      D::on('POST',  '/'.GUST_API_ROOT.'/session',                array('Gust_API', 'login'));
      D::on('POST',  '/'.GUST_API_ROOT.'/password',               array('Gust_API', 'forgotten'));
      D::on('GET',   '/'.GUST_API_ROOT.'/posts',                  array('Gust_API', 'posts'));
      D::on('GET',   '/'.GUST_API_ROOT.'/post(/:id@[0-9]+)',      array('Gust_API', 'post'));
      D::on('POST',  '/'.GUST_API_ROOT.'/post(/:id@[0-9]+)',      array('Gust_API', 'post_save'));
      D::on('DELETE','/'.GUST_API_ROOT.'/post(/:id@[0-9]+)',      array('Gust_API', 'post_delete'));
      D::on('GET',   '/'.GUST_API_ROOT.'/metakeys',               array('Gust_API', 'get_meta_keys'));
      D::on('GET',   '/'.GUST_API_ROOT.'/post/:id@[0-9]+/meta',   array('Gust_API', 'post_meta_list'));
      D::on('POST',  '/'.GUST_API_ROOT.'/post/:id@[0-9]+/meta',   array('Gust_API', 'post_meta_update'));
      D::on('DELETE','/'.GUST_API_ROOT.'/post/:id@[0-9]+/meta',   array('Gust_API', 'post_meta_delete'));
      D::on('GET',   '/'.GUST_API_ROOT.'/autosave/:id@[0-9]+',    array('Gust_API', 'autosave_get'));
      D::on('POST',  '/'.GUST_API_ROOT.'/autosave/:id@[0-9]+',    array('Gust_API', 'autosave'));
      D::on('POST',  '/'.GUST_API_ROOT.'/upload/:id@[0-9]+',      array('Gust_API', 'upload'));
      D::on('DELETE','/'.GUST_API_ROOT.'/upload',                 array('Gust_API', 'upload_delete'));
      D::on('GET',   '/'.GUST_API_ROOT.'/post/:id@[0-9]+/image',  array('Gust_API', 'post_image_list'));
      D::on('POST',  '/'.GUST_API_ROOT.'/post/:id@[0-9]+/image',  array('Gust_API', 'post_image_add'));
      D::on('DELETE','/'.GUST_API_ROOT.'/post/:id@[0-9]+/image',  array('Gust_API', 'post_image_delete'));
      D::on('POST',  '/'.GUST_API_ROOT.'/post/:id@[0-9]+/featured',  array('Gust_API', 'post_set_featured'));
      D::on('GET',   '/'.GUST_API_ROOT.'/:type@'.$taxonomies,               array('Gust_API', 'tax'));
      D::on('POST',  '/'.GUST_API_ROOT.'/:type@'.$taxonomies,               array('Gust_API', 'tax_add'));
      D::on('GET',   '/'.GUST_API_ROOT.'/:type@'.$taxonomies.'/:id@[0-9]+', array('Gust_API', 'tax_single'));
      D::on('POST',  '/'.GUST_API_ROOT.'/:type@'.$taxonomies.'/:id@[0-9]+', array('Gust_API', 'tax_update'));
      D::on('DELETE','/'.GUST_API_ROOT.'/:type@'.$taxonomies.'/:id@[0-9]+', array('Gust_API', 'tax_delete'));

    } else if (
        (get_query_var('gust_api')==GUST_NAME || get_query_var('gust_api')=='ghost')
        &&
        ($q->is_main_query())
      ) {
      require_once('gust-views.php');
      D::on('GET',  '/ghost(/:q@.*)',                         array('Gust_views', 'ghost'));
      D::on('GET',  '/'.GUST_NAME,                            array('Gust_views', 'root'));
      D::on('GET',  '/'.GUST_NAME.'/login',                   array('Gust_views', 'login'));
      D::on('GET',  '/'.GUST_NAME.'/signout',                 array('Gust_views', 'signout'));
      D::on('GET',  '/'.GUST_NAME.'/forgotten',               array('Gust_views', 'forgotten'));
      D::on('GET',  '/'.GUST_NAME.'/:type@'.$posttypes,       array('Gust_views', 'post_type'));
      D::on('GET',  '/'.GUST_NAME.'/editor',                  array('Gust_views', 'editor_default'));
      D::on('GET',  '/'.GUST_NAME.'/editor/:type@'.$posttypes,array('Gust_views', 'editor_new'));
      D::on('GET',  '/'.GUST_NAME.'/editor/:id@[0-9]+',       array('Gust_views', 'editor'));
      D::on('POST', '/'.GUST_NAME.'/coffee',                  array('Gust',       'paypal_submit'));
      D::on('*',    '/'.GUST_NAME.'/coffee/confirm',          array('Gust_views', 'coffee_confirm'));
    }
    D::dispatch();
    die('');
  }
}

function gust_admin_bar_filter( $wp_admin_bar ) {
  $logo = $wp_admin_bar->get_node('wp-logo');
  $logo->href = get_bloginfo('url').'/'.GUST_NAME.'/';
  $wp_admin_bar->add_node($logo);
  if (!is_admin()) {
    $name = $wp_admin_bar->get_node('site-name');
    $name->href = get_bloginfo('url').'/'.GUST_NAME.'/';
    $wp_admin_bar->add_node($name);
    $nodes = $wp_admin_bar->get_nodes();
    $args = array(
      'id'    => 'gust',
      'title' => __('Gust Dashboard','gust'),
      'href'  => get_bloginfo('url').'/'.GUST_NAME.'/',
      'meta'  => array( 'class' => 'gust' ),
      'parent'=> 'site-name'
    );
    $wp_admin_bar->add_node( $args );
    if (is_array($nodes)) foreach ($nodes as $node) {
      if ($node->id == 'dashboard') {
        $node->title = __('WP Dasboard','gust');
      }
      $wp_admin_bar->remove_node($node->id);
      $wp_admin_bar->add_node($node);
    }
  }
}

function gust_edit_post_link($link, $post_id, $context) {
  if (!is_admin()) {
    $link = get_bloginfo('url').'/'.GUST_NAME.'/editor/'.$post_id;
  }
  return $link;
}

function gust_edit_post_link_admin($actions, $post) {
  $posttypes = array_keys(Gust::$options['main_posttypes']);
  if (in_array($post->post_type,$posttypes)) {
    $actions['gust_edit'] = '<a href="'. get_bloginfo('url').'/'.GUST_NAME.'/editor/'.$post->ID . '" class="gust-edit">' . __('Edit in Gust','gust') . '</a>';
  }
  return $actions;
}
/*
function gust_uuid_post($post_id) {
  $uuid = get_post_meta($post_id,'_uuid',true);
  if (!$uuid) {
    $uuid = gust_gen_uuid();
    update_post_meta( $post_id, '_uuid', $uuid );
  }
  return $uuid;
}

function gust_gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
*/

function gust_get_subpath(){
  $url = get_bloginfo('url');
  $url = parse_url($url);
  $url = isset($url['path'])?$url['path']:'';
  return $url;
}
