<?php
    Flight::route('GET '.WP_GHOST_ROOT,function(){
      if (wpGhost::auth()) {
        Flight::redirect(WP_GHOST_ROOT.'/post');
      } else {
        Flight::redirect(WP_GHOST_ROOT.'/login');
      }
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/signout',function(){
      wp_logout();
      Flight::redirect(WP_GHOST_ROOT);
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/@type:post|page',function($type){
      if (wpGhost::auth()) {
        Flight::render('list.php',array('body_class'=>'manage','type'=>$type));
      } else {
        Flight::redirect(WP_GHOST_ROOT.'/login');
      }
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/login',function(){
      if (wpGhost::auth()) {
        Flight::redirect(WP_GHOST_ROOT);
      } else {
        Flight::render('login.php',array('body_class'=>'ghost-login'));
      }
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/forgotten',function(){
      if (wpGhost::auth()) {
        Flight::redirect('.WP_GHOST_ROOT.');
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('POST '.WP_GHOST_ROOT.'/upload/@id:[0-9]+',function(){
      if (wpGhost::auth()) {
        echo wpGhost::upload($id);
      }      
    });

    Flight::route('GET '.WP_GHOST_ROOT.'/editor/',function(){
      Flight::redirect(WP_GHOST_ROOT.'/editor/post');
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/editor/@type:post|page',function($type){
      if (wpGhost::auth()) {
        $id = wpGhost::new_post($type);
        Flight::redirect(WP_GHOST_ROOT.'/editor/'.$id);
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('GET '.WP_GHOST_ROOT.'/editor/@id:[0-9]+',function($id){
      if (wpGhost::auth()) {
        Flight::render('editor.php',array('body_class'=>'ghost-login'));
      } else {
        Flight::render('login.php',array('body_class'=>'ghost-login'));
      }
    });
?>