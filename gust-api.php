<?php 

class Gust_API {
  static function login() {
    $return = Gust::login($_POST['username'],$_POST['password']);
    D::json($return);      
  }
  static function forgotten() {
    $return = Gust::retrieve_password($_POST['username']);
    D::json($return);      
  }
  static function tax_add($type) {
      if (Gust::auth('edit_posts' )) {
        $return = Gust::new_tax_term($type,$_POST); 
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function tax_update($type,$id) {
      if (Gust::auth('edit_posts' )) {
        $return = Gust::update_tax_term($type,$id,$_POST); 
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function tax_delete($type,$id) {
      if (Gust::auth('edit_posts' )) {
        $return = Gust::delete_tax_term($type,$id); 
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function get_meta_keys() {
      if (Gust::auth('edit_posts' )) {
        $return = Gust::get_meta_keys(); 
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function post_meta_list($id) {
    if (Gust::auth('edit_posts' )) {
      $return = Gust::get_meta_list($id); 
    } else {
      $return = array('error'=>__('You have no permission to edit this post','gust'));
    }
    D::json($return);      
  }
  static function post_image_list($id) {
    if (Gust::auth('edit_posts' )) {
      $return = Gust::get_image_list($id); 
    } else {
      $return = array('error'=>__('You have no permission to edit this post','gust'));
    }
    D::json($return);      
  }
  static function post_set_featured($id) {
    if (Gust::auth('edit_posts' )) {
      $return = Gust::set_featured($id,$_POST); 
    } else {
      $return = array('error'=>__('You have no permission to edit this post','gust'));
    }
    D::json($return);      
  }
  static function post_meta_delete($id) {
    if (Gust::auth('edit_posts' )) {
      if ($_SERVER['REQUEST_METHOD']=='DELETE') {
        $data = file_get_contents('php://input');
        parse_str($data,$_POST);
      }
      $return = Gust::delete_meta($id,$_POST); 
    } else {
      $return = array('error'=>__('You have no permission to edit this post','gust'));
    }
    D::json($return);      
  }
  static function post_meta_update($id) {
    if (Gust::auth('edit_posts' )) {
      $return = Gust::update_meta($id,$_POST); 
    } else {
      $return = array('error'=>__('You have no permission to edit this post','gust'));
    }
    D::json($return);      
  }
  static function tax($type) {
      if (Gust::auth('edit_posts' )) {
        if ($type=='post_tag') {
          $return = Gust::get_tags();
        }
        else if ($type=='category') {
          $return = Gust::get_categories();
        }
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function tax_single($type,$id) {
      if (Gust::auth('edit_posts' )) {
        $return = Gust::get_tax_term($type,$id);
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }
  static function upload($id) {
      if (Gust::auth()) {
        echo Gust::upload($id);
      }          
  }
  static function post_image_add($id) {
      if (Gust::auth()) {
        D::json(Gust::upload($id));
      }          
  }
  static function post_image_delete($id) {
      if (Gust::auth('edit_posts')) {
        if ($_SERVER['REQUEST_METHOD']=='DELETE') {
          $data = file_get_contents('php://input');
          parse_str($data,$_POST);
        }
        $return = Gust::delete_image($id,$_POST);
      } else {
        $return = array('error'=>__('You have no permission to remove this image','gust'));
      }     
      D::json($return);      
  }
  static function upload_delete() {
      if (Gust::auth('edit_posts')) {
        $return = Gust::delete($_GET['url']);
      } else {
        $return = array('error'=>__('You have no permission to remove this image','gust'));
      }     
      D::json($return);      
  }
  static function autosave_get($id) {
      if (Gust::auth('edit_post',$id)) {
        $return = Gust::get_autosave($id);
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);          
  }
  static function autosave($id) {
      if (Gust::auth('edit_post',$id)) {
        $ret = Gust::put_autosave($id,$_POST);
        if (is_wp_error($ret )) {
          $return = array('error'=>$ret->get_error_message());
        } else {
          $return = array('id'=>$id);          
        }
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);          
  }
  static function posts() {
      if (Gust::auth('edit_posts')) {
        $return = Gust::get_posts(
          isset($_GET['page'])?$_GET['page']:1,
          isset($_GET['type'])?$_GET['type']:'post',
          isset($_GET['status'])?$_GET['status']:false,
          isset($_GET['limit'])?$_GET['limit']:false
        );
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
    }
  static function post($id) {
      if (Gust::auth('edit_post', $id )) {
        $return = Gust::get_post($id);
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
    }
  static function post_delete($id) {
// TO DO move actual logic to Gust class
      if (Gust::auth('delete_post',$id )) {
        $ret = wp_delete_post($id);
        if (is_wp_error($ret )) {
          $return = array('error'=>$ret->get_error_message());
        } else {
          $return = array('id'=>$id);          
        }
      } else {
        $return = array('error'=>__('You have no permission to delete this post','gust'));
      }
      D::json($return);      
  }
  static function post_save($id) {
// TO DO move actual logic to Gust class
      if (Gust::auth('edit_post',$id)) {
        if (isset($_POST['featured'])) {
          if($_POST['featured']==1 && !isset($_POST['markdown'])) {
            stick_post($id);
          } else {
            unstick_post($id);
          }
          $return = Gust::get_post($id);
        } else if (isset($_POST['published_at']) && !isset($_POST['markdown'])) {
          if ($_POST['published_at']=='NaN'){
            $date = '0000-00-00 00:00:00';
          } else {
            $date = date('Y-m-d H:i:s',$_POST['published_at']+ get_option( 'gmt_offset' ) * 3600);            
          }
//        }
//          $date = date('Y-m-d H:i:s',$_POST['published_at']);
          $arr = array(
            'ID' => $id,
            'edit_date' => true,
            'post_date' => $date,
            'post_date_gmt' => $date=='0000-00-00 00:00:00'?'0000-00-00 00:00:00':get_gmt_from_date($date)//date('Y-m-d H:i:s',$_POST['published_at']),
          ); 
          $id = wp_update_post($arr);
          $return = Gust::get_post($id);
        } else if (isset($_POST['slug']) && !isset($_POST['markdown']) ) {
          $arr = array(
            'ID' => $id,
            'post_name' => $_POST['slug']
          ); 
          $id = wp_update_post($arr,true);
          $return = Gust::get_post($id);
        } else if (isset($_POST['markdown'])) {
          update_post_meta($id,'_md',$_POST['markdown']);
          if (!current_user_can('publish_post',$id) && $_POST['status']=='publish') {
            $_POST['status']='pending';
          }
          $arr = array(
            'ID' => $id,
            'post_title' => $_POST['title']?$_POST['title']:'',
            'post_content' => $_POST['html']?$_POST['html']:' ',
            'post_status' => $_POST['status']
          ); 
          if(!isset($_POST['type']) || $_POST['type']!='page') {
            $tags=$_POST['tags'];
            $tags=explode(',',$tags);
            foreach ($tags as $key=>$tag) {
              $no = $tag * 1;
              $tags[$key] = $no?$no:$tag;
            }
            wp_set_post_terms($id,$tags,'post_tag');

            $tags=$_POST['categories'];
            $tags=explode(',',$tags);
            foreach ($tags as $key=>$tag) {
              $no = $tag * 1;
              $tags[$key] = $no?$no:$tag;
            }
            wp_set_post_terms($id,$tags,'category');
          }
          wp_update_post($arr,true);
          $return = Gust::get_post($id);
        }
      } else {
        $return = array('error'=>__('You have no permission to edit this post','gust'));
      }
      D::json($return);      
  }

}
?>