<?php 
/*
Plugin Name: Gust
Plugin URI: http://wp.tribuna.lt/gust
Description: A port of the Ghost admin interface
Author: Arūnas Liuiza
Version: 0.2
Author URI: http://wp.tribuna.lt/
*/
define ('GUST_ROOT',      '/gust');
define ('GUST_NAME',      str_replace('/','',GUST_ROOT));
define ('GUST_API_ROOT',  '/api/v0\.1');
define ('GUST_TITLE',     'Gust');
define ('GUST_VERSION',   'v0.1.1');
define ('GUST_PLUGIN_PATH',   plugin_dir_path(__FILE__));
define ('GUST_PLUGIN_URL',    plugin_dir_url(__FILE__));

add_action('init','gust_init_rewrites');
add_action('pre_get_posts','gust_drop_in');

function gust_init_rewrites() {
  add_rewrite_tag( '%gust_api%', '(ghost|'.GUST_NAME.'|api)'); 
  add_rewrite_tag( '%gust_q%', '(.*)'); 
  add_permastruct('gust_calls', '%gust_api%/%gust_q%');
  flush_rewrite_rules();
}

function gust_drop_in($q) {
  if ((get_query_var('gust_api')=='ghost'||get_query_var('gust_api')==GUST_NAME||get_query_var('gust_api')=='api' )&& $q->is_main_query()) {
    define('WP_ADMIN',true);
    require_once(GUST_PLUGIN_PATH.'/assets/flight/Flight.php');
    Flight::set('flight.views.path', GUST_PLUGIN_PATH.'/views');
    if (get_query_var('gust_api')=='api' && $q->is_main_query()) {
      require_once('gust.class.php');
      require_once('gust-api.php');
    } else if (get_query_var('gust_api')==GUST_NAME && $q->is_main_query()) {
      require_once('gust.class.php');
      require_once('gust-views.php');
    } else if (get_query_var('gust_api')=='ghost' && $q->is_main_query()) {
      require_once('gust.class.php');
      require_once('gust-views.php');
    }
    Flight::start();
    die('');
  }
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
function get_avatar_url($id_or_email, $size=96, $default='', $alt=false){
    $get_avatar = get_avatar( $id_or_email, $size, $default, $alt );
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}


?>