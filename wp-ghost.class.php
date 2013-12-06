<?php
class wpGhost {
  public function auth($cap='edit_posts',$id=false) {
    if(is_user_logged_in() && current_user_can($cap,$id)) {
      return true;
    } else {
      return false;
    }
  }

  // login
  public function login($user,$pass) {
    $creds['user_login']    = $user;
    $creds['user_password'] = $pass;
    $creds['remember'] = true;
    $user = wp_signon( $creds, false );     
    if (is_wp_error($user)) {
      $return = array(
        'error' => $user->get_error_message()
      );
      $return['error'] = preg_replace('(href="[^"]*lostpassword")', 'href="/ghost/forgotten"', $return['error']);
    } else {
      $return = array('success'=>true);
      $return['user'] = wp_get_current_user();
    }
    return $return;
  }
  public function upload($id) {
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
          $file = array(
            'name'  => basename($filename),
            'size'  => filesize($filename),
            'url'   => $attachment['guid'],
            'thumbnailUrl' => $attachment['guid'],// TO DO
            'deleteUrl' => $attachment['guid'], // TO DO
            'deleteType' => 'DELETE'
          );
          $ret['files'][] = $file;
          return  $attachment['guid'];
        }
  }
  public function new_post($type) {
    $arr = array('post_type'=>$type,'post_status'=>'draft','post_content'=>' ','post_author'=>get_current_user_id());
    $id = wp_insert_post($arr,true);
    return $id;
  }
  public function get_post($id) {
    $ret = array();
    $args = array(
      'p'=>$id,
      'post_type'=>'any'
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) : 
      $query->the_post();
      $ret['post'] = wpGhost::format_post($query->post);
    endwhile;
    return $ret;
  }
  public function get_posts($page=1,$type='post',$status='any',$limit=15) {
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
    $query = new WP_Query($args);
    while ($query->have_posts()) : 
      $query->the_post();
      $ret['posts'][] = wpGhost::format_post($query->post);
    endwhile;
    $ret['total'] = $query->found_posts*1;
    $ret['pages'] = $query->max_num_pages*1;
    if ($ret['pages']>1 && $page<$ret['pages'])
      $ret['next'] = $page+1;
    if ($ret['next']>$ret['pages'])
      $ret['next'] = $ret['pages'];
    if ($ret['pages']>1 && $page>1)
      $ret['prev'] = $page-1;
    return $ret;
  }


  private function content_markdown($post_id,$post_content) {
    $md = get_post_meta($post_id,'_md',true);
    if (!$md) {
      $path = plugin_dir_path(__FILE__);
      require_once($path.'/markdown.php');
      $markdown = new HTML_To_Markdown($post_content);
      $md = $markdown->output();
      update_post_meta( $post_id, '_md', $md );
    }
    return $md;
  }
  private function content_html($post_content) {
    $return = apply_filters( 'the_content', $post_content );
    return $return;
  }
  private function post_status($status) {
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
  private function avatar_url($id_or_email, $size=96, $default='', $alt=false){
    $get_avatar = get_avatar( $id_or_email, $size, $default, $alt );
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
  }
  private function format_user($user_id) {
    $user = get_userdata($user_id);
    $return = array(
      "id"        => $user_id,
      "name"      => $user->data->display_name,
      "slug"      => $user->data->user_login,
      "email"     => $user->data->user_email,
      "image"     => wpGhost::avatar_url($user_id),
      "bio"       => get_the_author_meta('description'),
      "website"   => $user->data->user_url,
      "last_login"=> null,
      "created_at"=> "2013-11-26T18:44:19.316Z",
      "updated_at"=> "2013-12-01T20:16:54.123Z"
    );
    return $return;
  }
  private function format_post($post) {
    $return = array(
      "id" => $post->ID*1,
      "title" => $post->post_title,
      "slug" => $post->post_name,
      "markdown" => wpGhost::content_markdown($post->ID,$post->post_content),
      "html" => wpGhost::content_html($post->post_content),
      "image" => null,
      "featured" => is_sticky( $post->ID )?1:0,
      "status" => wpGhost::post_status($post->post_status),
      "author_id" => $post->post_author,
      "author" => wpGhost::format_user( $post->post_author ),
      "updated_at" => date(DATE_W3C,strtotime($post->post_modified_gmt)),
      "published_at" => $post->post_date_gmt!='0000-00-00 00:00:00'?date(DATE_W3C,strtotime($post->post_date_gmt)):'',//"2013-11-26T18:21:23.887Z",
      "tags" => array (
        array(
          "id"=> 1,
          "uuid"=> "c1761690-14e3-463d-9348-a90401b9a29e",
          "name"=> "Getting Started",
          "slug"=> "getting-started",
          "description"=> null,
          "parent_id"=> null,
          "meta_title"=> null,
          "meta_description"=> null,
          "created_at"=> "2013-11-26T18:21:23.945Z",
          "created_by"=> 1,
          "updated_at"=> "2013-11-26T18:21:23.945Z",
          "updated_by"=> 1
        )
      )
    );
    return $return;
  }
}


?>