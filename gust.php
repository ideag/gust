<?php 
/*
Plugin Name: Gust
Plugin URI: http://wp.tribuna.lt/gust
Description: A port of the Ghost admin interface
Author: Arūnas Liuiza
Version: 0.1
Author URI: http://wp.tribuna.lt/
*/
define ('GUST_ROOT',      '/gust');
define ('GUST_NAME',      str_replace('/','',GUST_ROOT));
define ('GUST_API_ROOT',  '/api/v0\.1');
define ('GUST_TITLE',     'Gust');
define ('GUST_VERSION',   'v0.1');
define ('GUST_PLUGIN_PATH',   plugin_dir_path(__FILE__));
define ('GUST_PLUGIN_URL',    plugin_dir_url(__FILE__));


add_action('init','gust_init_rewrites');
add_action('pre_get_posts','gust_drop_in');

function gust_init_rewrites() {
  add_rewrite_tag( '%gust_api%', '(ghost|'.GUST_NAME.'|api)'); 
  add_rewrite_tag( '%gust_q%', '(.*)'); 
  add_permastruct('ghost_calls', '%gust_api%/%gust_q%');
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

function ghost_retrieve_password($user_login) {
    global $wpdb, $current_site;

    if ( empty( $user_login) ) {
        return array('error'=>'Invalid/empty username');
    } else if ( strpos( $user_login, '@' ) ) {
        $user_data = get_user_by( 'email', trim( $user_login ) );
        if ( empty( $user_data ) )
           return array('error'=>'Invalid/empty username');
    } else {
        $login = trim($user_login);
        $user_data = get_user_by('login', $login);
    }

    do_action('lostpassword_post');


    if ( !$user_data ) return array('error'=>'Invalid/empty username');

    // redefining user_login ensures we return the right case in the email
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;

    do_action('retreive_password', $user_login);  // Misspelled and deprecated
    do_action('retrieve_password', $user_login);

    $allow = apply_filters('allow_password_reset', true, $user_data->ID);

    if ( ! $allow )
        return array('error'=>'Password reset is not allowed');
    else if ( is_wp_error($allow) )
        return array('error'=>'Password reset is not allowed');

    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
    if ( empty($key) ) {
        // Generate something random for a key...
        $key = wp_generate_password(20, false);
        do_action('retrieve_password_key', $user_login, $key);
        // Now insert the new md5 key into the db
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
    }
    $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
    $message .= network_home_url( '/' ) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
    $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

    if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
    else
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $title = sprintf( __('[%s] Password Reset'), $blogname );

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);

    if ( $message && !wp_mail($user_email, $title, $message) )
      return array('error'=>'Email could not be sent. Check server configuration');
//        wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

    return array('success'=>true);
}

?>