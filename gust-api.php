<?php 

    // login
    Flight::route('POST '.GUST_API_ROOT.'/session',function(){
      $return = Gust::login($_POST['username'],$_POST['password']);
      Flight::render('json.php',array('return'=>$return));
    });
    // remind password
    Flight::route('POST '.GUST_API_ROOT.'/password',function(){
      $return = Gust::retrieve_password($_POST['username']);
      Flight::render('json.php',array('return'=>$return));
    });
    // upload
    Flight::route('POST '.GUST_API_ROOT.'/upload/@id:[0-9]+',function($id){
      if (Gust::auth()) {
        echo Gust::upload($id);
      }      
    });
    Flight::route('DELETE '.GUST_API_ROOT.'/upload',function(){
      if (Gust::auth('edit_posts')) {
        $return = Gust::delete($_GET['url']);
      } else {
        $return = array('error'=>'You have no permission to remove this image');
      }     
      Flight::render('json.php',array('return'=>$return));      
    });

    // get post by id
    Flight::route('GET '.GUST_API_ROOT.'/post(/@id:[0-9]+)',function($id){
      if (Gust::auth('edit_post', $id )) {
        $return = Gust::get_post($id);
      } else {
        $return = array('error'=>'You have no permission to edit this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });


    Flight::route('GET '.GUST_API_ROOT.'/tags',function(){
      if (Gust::auth('edit_post', $id )) {
        $return = Gust::get_tags();
      } else {
        $return = array('error'=>'You have no permission to edit this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });

    Flight::route('GET '.GUST_API_ROOT.'/categories',function(){
      if (Gust::auth('edit_post', $id )) {
        $return = Gust::get_categories();
      } else {
        $return = array('error'=>'You have no permission to edit this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });


    // get list of posts
    Flight::route('GET '.GUST_API_ROOT.'/posts',function(){
      if (Gust::auth('edit_post', $id )) {
        $return = Gust::get_posts($_GET['page'],$_GET['type'],$_GET['status'],$_GET['limit']);
      } else {
        $return = array('error'=>'You have no permission to edit this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });

    Flight::route('DELETE '.GUST_API_ROOT.'/post(/@id:[0-9]+)',function($id){
      if (Gust::auth('delete_post',$id )) {
        $ret = wp_delete_post($id);
        if (is_wp_error($ret )) {
          $return = array('error'=>$ret->get_error_message());
        } else {
          $return = array('id'=>$id);          
        }
      } else {
        $return = array('error'=>'You have no permission to delete this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });

    Flight::route('POST '.GUST_API_ROOT.'/post(/@id:[0-9]+)',function($id){
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
          $arr = array(
            'ID' => $id,
            'post_title' => $_POST['title']?$_POST['title']:'',
            'post_content' => $_POST['html']?$_POST['html']:' ',
            'post_status' => $_POST['status']
          ); 
          if($_POST['type']!='page') {
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
//          print_r($id);
          $return = Gust::get_post($id);
        }
      } else {
        $return = array('error'=>'You have no permission to edit this post');
      }
      Flight::render('json.php',array('return'=>$return));      
    });

?>