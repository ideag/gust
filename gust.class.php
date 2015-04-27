<?php
class Gust {
  static $options = array();
  protected static $includes_dir;
  public static function init_options() {
    self::$includes_dir = plugin_dir_path( __FILE__ );
    require_once self::$includes_dir . 'gust-options-data.php';
    require_once self::$includes_dir . 'gust-options.php';
    $gust_settings = new Gust_Options( Gust_Options_Data::get() );
    self::$options   = $gust_settings->get();
    define ('GUST_NAME',          self::$options['main_endpoint']);
    define ('GUST_ROOT',          GUST_SUBPATH.'/'.GUST_NAME);
    define ('GUST_API_ROOT',      '/api/'.GUST_NAME);
  }

  public static function auth($cap='edit_posts',$id=false) {
    if(is_user_logged_in() && current_user_can($cap,$id)) {
      return true;
    } else {
      return false;
    }
  }

  // login
  public static function login($user,$pass) {
    $creds['user_login']    = $user;
    $creds['user_password'] = $pass;
    $creds['remember'] = true;
    $user = wp_signon( $creds, false );
    if (is_wp_error($user)) {
      $return = array(
        'error' => $user->get_error_message()
      );
      $return['error'] = preg_replace('(href="[^"]*lostpassword")', 'href="'.GUST_ROOT.'/forgotten"', $return['error']);
    } else {
      $return = array('success'=>true);
      $return['user'] = wp_get_current_user();
    }
    return $return;
  }
  public static function delete_image($post_id,$data) {
    $del = false;
    $feat_id = get_post_thumbnail_id( $post_id );
    if ($data['id']==$feat_id) {
      delete_post_thumbnail($post_id );
      $del = true;
    }
    $res = wp_delete_attachment($data['id'], true );
    if ($res===false) {
      $return = array('error'=>'Failed to delete');
    } else {
      $return = array('success'=>'Image deleted','id'=>$data['id'],'del'=>$del);
    }
    return $return;
  }
  public static function delete($url) {
    $id = Gust::get_attachment_id_from_src($url);
    $res = wp_delete_attachment($id, true );
    if ($res===false) {
      $return = array('error'=>'Failed to delete');
    } else {
      $return = array('success'=>'Image deleted');
    }
    return $return;
  }
  public static function upload($id) {
        $ret = array('files'=>array());
        if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $uploadedfile = $_FILES['uploadimage'];
        $upload_overrides = array( 'test_form' => false );
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        if ( $movefile ) {
          $wp_filetype = $movefile['type'];
          $filename = $movefile['file'];
          $wp_upload_dir = wp_upload_dir();
          $attachment = array(
              'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
              'post_mime_type' => $wp_filetype,
              'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
              'post_content' => '',
              'post_status' => 'inherit'
          );
          $attach_id = wp_insert_attachment( $attachment, $filename,$id);
          include_once( ABSPATH . 'wp-admin/includes/image.php' );
          $attach_data = wp_generate_attachment_metadata($attach_id,$filename);
//          print_r($attach_data);
          wp_update_attachment_metadata($attach_id,$attach_data);
          $file = array(
            'name'  => basename($filename),
            'size'  => filesize($filename),
            'url'   => $attachment['guid'],
            'thumbnailUrl' => $attachment['guid'],// TO DO
            'deleteUrl' => $attachment['guid'], // TO DO
            'deleteType' => 'DELETE'
          );
          $ret['files'][] = $file;
          if(isset($_POST['new_upload']) && $_POST['new_upload']) {
            $i = wp_get_attachment_image_src($attach_id);
            $result['success'] = array(
              'id' => $attach_id,
              'url' => $i[0],
              'featured' => false
            );
            return $result;
          } else {
            return $attachment['guid'];
          }
        }
  }
  public static function get_autosave($id){
    $post = wp_get_post_autosave( $id );
    if (!is_wp_error($post)&&$post) {
      $ret = array();
      $ret['post'] = array(
        'id' =>$post->ID,
        'title' => $post->post_title,
        'markdown' => Gust::content_markdown($post->ID,$post->post_content),
        'time' => $post->post_modified_gmt!='0000-00-00 00:00:00'?date(DATE_W3C,strtotime($post->post_modified_gmt)):''
      );
    } else {
      $ret = false;
    }
    return $ret;
  }
  public static function put_autosave($id,$data){
    $old_revision = wp_get_post_autosave( $id );
    $post = get_post($id);
    $post->post_title = $data['title'];
    $post->post_content = $data['text'];
    unset($post->post_date);
    unset($post->post_date_gmt);
    unset($post->post_modified);
    unset($post->post_modified_gmt);
    $revision_id = _wp_put_post_revision( $post, true );
    add_metadata('post',$revision_id, '_md', $data['markdown'],true);
    if ($old_revision) {
      wp_delete_post_revision($old_revision->ID);
    }
    # add_metadata instead of add_post_meta, because it does not work on revisions
    return $revision_id;
  }
  public static function new_post($type) {
    $arr = array('post_type'=>$type,'post_status'=>'auto-draft','post_content'=>' ','post_author'=>get_current_user_id());
    $id = wp_insert_post($arr,true);
    return $id;
  }
  public static function get_tax_term($type,$id) {
    $term = get_term($id*1,$type);
    if (!is_wp_error($term )&&$term) {
      $return = array('success'=>true);
      $return['term'] = $term;
    } elseif (!$term) {
      $return = array('error'=>__('Term does not exist','gust'));
    } else {
      $return = array('error'=>$term->get_error_message());
    }
    return $return;
  }
  public static function update_tax_term($type,$id,$data) {
    $arr = array();
    if (isset($data['slug']))
      $arr['slug'] = $data['slug'];
    if (isset($data['parent']))
      $arr['parent'] = $data['parent'];
    $term = wp_insert_term(
      $id,
      $type,
      $arr
    );
    if (!is_wp_error($term )) {
      $return = array('success'=>__('Term updated','gust'));
      $return['term'] = get_term($term['term_id']*1,$type);
    } else {
      $return = array('error'=>$term->get_error_message());
    }
    return $return;
  }
  public static function delete_tax_term($type,$id,$data) {
    $term = wp_delete_term(
      $id,
      $type
    );
    if ($term) {
      $return = array('success'=>__('Term deleted','gust'));
      $return['term_id'] = $id;
    } else {
      $return = array('error'=>__('Error deleting term.','gust'));
    }
    return $return;
  }

  public static function new_tax_term($type,$data) {
    $arr = array();
    if (isset($data['slug']))
      $arr['slug'] = $data['slug'];
    if (isset($data['parent']))
      $arr['parent'] = $data['parent'];
    $term = wp_insert_term(
      $data['title'],
      $type,
      $arr
    );
    if (!is_wp_error($term )) {
      $return = array('success'=>__('Category added.','gust'));
      $return['term'] = get_term($term['term_id']*1,$type);
    } else {
      $return = array('error'=>$term->get_error_message());
    }
    return $return;
  }
  public static function get_meta_list($post_id) {
    $result = get_post_custom( $post_id );
    $return = array();
    if (is_array($result)) foreach ($result as $key=>$value) {
      if (is_array($value)) foreach ( $value as $k => $v ) {
        if ($key[0]!='_')
          $return[] = array('name'=>$key,'value'=>$v);
      }
    }
    return $return;
  }
  public static function update_meta($post_id,$data) {
    $meta_key = $data['name'];
//    if ($data['value']==$data['old_value']) {
//      $return = array('warning'=>__('Nothing to update','gust'));
//    } else {
      $return = update_post_meta($post_id, $meta_key, $data['value'], $data['old_value']);
      if ($return) {
        $return = array('success'=>__('Custom field updated.','gust'));
      } else {
        $return = array('error'=>__('Error updating custom field.','gust'));
      }
//    }
    return $return;
  }
  public static function delete_meta($post_id,$data) {
    $meta_key = $data['name'];
    $return = delete_post_meta($post_id, $meta_key, $data['old_value']);
    if ($return) {
      $return = array('success'=>__('Custom field deleted.','gust'));
    } else {
      $return = array('error'=>__('Error deleting custom field.','gust'));
    }
    return $return;
  }
  public static function set_featured($post_id,$data) {
    if (isset($data['featured'])) {
      $return = set_post_thumbnail( $post_id, $data['featured'] );
    }
    if ($return) {
      return array('success'=>__('Featured image selected.','gust') );
    } else {
      return array('error'=>__('Error - featured image could not be set.','gust'));
    }
  }
  public static function get_image_list($post_id) {
    $media = get_attached_media( 'image', $post_id );
    //$return = delete_post_meta($post_id, $meta_key, $data['old_value']);
    if ($media) {
      $post_thumbnail_id = get_post_thumbnail_id( $post_id );
      $result = array();
      foreach ($media as $image_id => $image) {
        $i = wp_get_attachment_image_src($image_id);
        $result[] = array(
          'id' => $image_id,
          'url' => $i[0],
          'featured' => $post_thumbnail_id==$image_id
        );
      }
      $return = array('success'=>__('Post images received.','gust'));
      $return['media'] = $result;
      $return['featured'] = $post_thumbnail_id;
    } else {
      $return = array('error'=>__('Error receiving post images.','gust'));
    }
    return $return;
  }
  public static function get_meta_keys(){
    global $wpdb;
      $keys = $wpdb->get_col( "
           SELECT meta_key
           FROM $wpdb->postmeta
           GROUP BY meta_key
           HAVING meta_key NOT LIKE '\_%'
           ORDER BY meta_key
           " );
    $return = $keys;
    return $return;
  }
  public static function get_tags(){
    $ret = get_terms('post_tag',array('hide_empty'=>'0','orderby'=>'count'));
    return $ret;
  }
  public static function get_categories(){
    $ret = get_terms('category',array('hide_empty'=>'0','orderby'=>'count'));
    return $ret;
  }
  public static function get_post($id) {
    $ret = array();
    $args = array(
      'p'=>$id,
      'post_type'=>'any',
      'post_status'=>get_post_stati('','names')
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) :
      $query->the_post();
      $ret['post'] = Gust::format_post($query->post);
    endwhile;
    if (!isset($ret['post'])) {
      $p = get_post($id,OBJECT);
      if ($p->post_status=='auto-draft'){
        $p->post_status='draft';
      }
      $ret['post'] = Gust::format_post($p);
    }
    return $ret;
  }
  public static function get_posts($page=1,$type='post',$status='any',$limit=15) {
    if (!$status) $status = 'any';
    if (!$limit) $limit = 15;
    if (!$page) $page = 1;
    if (!$type) $type = 'post';
    $args = array(
      'post_type'=>$type,
      'posts_per_page' => $limit,
      'post_status'=>$status,
      'paged'=>$page,
      'ignore_sticky_posts' =>true
    );
    if (!current_user_can('edit_others_posts' )) {
      $args['author']=get_current_user_id();
    }
    $query = new WP_Query($args);
    while ($query->have_posts()) :
      $query->the_post();
      $ret['posts'][] = Gust::format_post($query->post);
    endwhile;
    $ret['total'] = $query->found_posts*1;
    $ret['pages'] = $query->max_num_pages*1;
    if ($ret['pages']>1 && $page<$ret['pages'])
      $ret['next'] = $page+1;
    if (isset($ret['next']) && ($ret['next']>$ret['pages']))
      $ret['next'] = $ret['pages'];
    if ($ret['pages']>1 && $page>1)
      $ret['prev'] = $page-1;
    return $ret;
  }

  public static function paypal_submit() {
//        $address = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        $address = 'https://www.paypal.com/cgi-bin/webscr';

    $data = array(
      'business' => 'arunas@liuiza.lt',
      'cmd' => '_xclick',
      'rm' => '2',
      'amount' => round($_POST['tiny_amount'],2),
      'return' => get_bloginfo( 'url' ).GUST_ROOT.'/coffee/confirm',
      'cancel_return' => get_bloginfo( 'url' ).'',
      'item_name' => $_POST['tiny_text'],
      'custom' => isset($_POST['tiny_text2'])?': '.$_POST['tiny_text2']:'',
      'currency_code' => 'EUR',
      'no_shipping' => '1',
    );
    echo '<html><head><meta charset="utf-8"/></head><body><form id="form" action="'.$address.'" method="post">';
    foreach ($data as $key => $value) {
      echo "<input type='hidden' name='{$key}' value='{$value}'/>";
    }
    echo '</form>';
    echo '<script>document.getElementById("form").submit();</script></body></html>';
    die('PAYPAL');
  }
  public static function get_post_tags($post_id,$taxonomy='post_tag'){
    return wp_get_post_terms($post_id, $taxonomy);
  }




  private static function content_markdown($post_id,$post_content) {
    $md = get_post_meta($post_id,'_md',true);
    if (!$md) {
      //check for „Markdown on Save Improved“
      $mdosi = get_post_meta($post_id,'_sd_is_markdown',true);
      if ($mdosi) {
        $md = get_post_field('post_content_filtered',$post_id,'raw');
        update_post_meta( $post_id, '_md', $md );
      } else {
        $path = plugin_dir_path(__FILE__);
        require_once($path.'/markdown.php');
        if (function_exists('mb_convert_encoding'))
          $post_content = mb_convert_encoding($post_content, 'HTML-ENTITIES', 'UTF-8');
        $markdown = new HTML_To_Markdown($post_content,array('header_style'=>'atx'));
        $md = $markdown->output();
        update_post_meta( $post_id, '_md', $md );
      }
    }
    return $md;
  }
  private static function content_html($post_content) {
    $return = apply_filters( 'the_content', $post_content );
    return $return;
  }
  private static function post_status($status) {
    switch ($status) {
      case 'publish' :
        $return = 'published';
      break;
      case 'future' :
        $return = 'scheduled';
      break;
      default :
        $return = $status;
      break;
    }
    return $return;
  }
  private static function avatar_url($id_or_email, $size=96, $default='', $alt=false){
    $get_avatar = get_avatar( $id_or_email, $size, $default, $alt );
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
  }
  private static function format_user($user_id) {
    $user = get_userdata($user_id);
    $return = array(
      "id"        => $user_id,
      "name"      => $user->data->display_name,
      "slug"      => $user->data->user_login,
      "email"     => $user->data->user_email,
      "image"     => Gust::avatar_url($user_id),
      "bio"       => get_the_author_meta('description'),
      "website"   => $user->data->user_url,
      "last_login"=> null,
      "created_at"=> "2013-11-26T18:44:19.316Z",
      "updated_at"=> "2013-12-01T20:16:54.123Z"
    );
    return $return;
  }
//  private function format_tag($tag_id) {
//    $return = $tag_id
//    return $return;
//  }
  private static function format_post($post) {
    $return = array(
      "id" => $post->ID*1,
      "title" => $post->post_title,
      "type" => $post->post_type,
      "slug" => $post->post_name,
      "markdown" => Gust::content_markdown($post->ID,$post->post_content),
      "html" => Gust::content_html($post->post_content),
      "image" => null,
      "featured" => is_sticky( $post->ID )?1:0,
      "status" => Gust::post_status($post->post_status),
      "updated_at" => $post->post_modified_gmt!='0000-00-00 00:00:00'?date(DATE_W3C,strtotime($post->post_modified_gmt)):'',//date(DATE_W3C,strtotime($post->post_modified_gmt)),
      "published_at" => $post->post_date_gmt!='0000-00-00 00:00:00'?date(DATE_W3C,strtotime($post->post_date_gmt)):'',//"2013-11-26T18:21:23.887Z",
      "created_at" => $post->post_status=='draft'?Gust::unpublished_draft_date($post->ID):($post->post_date_gmt!='0000-00-00 00:00:00'?date(DATE_W3C,strtotime($post->post_date_gmt)):''),//"2013-11-26T18:21:23.887Z",
      "author_id" => $post->post_author,
      "author" => Gust::format_user( $post->post_author ),
      "tags" => Gust::get_post_tags($post->ID*1),
      "categories" => Gust::get_post_tags($post->ID*1,'category'),
      'custom_fields' => post_type_supports( $post->post_type, 'custom-fields' )
    );
    return $return;
  }
  private static function unpublished_draft_date($post_id){
    global $wpdb;
    $draft_date_array = $wpdb->get_results( 'SELECT post_date FROM '.$wpdb->prefix.'posts WHERE ID = '.$post_id );
    $draft_date = $draft_date_array[0]->post_date;
    $draft_date = date(DATE_W3C,strtotime($draft_date));
    return $draft_date;
  }
  private static function get_attachment_id_from_src ($image_src) {

    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
    $id = $wpdb->get_var($query);
    return $id;

  }
  public static function retrieve_password($user_login) {
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

}
