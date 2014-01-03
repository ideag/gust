<?php
    Flight::route('GET /'.GUST_NAME,function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT.'/post');
      } else {
        Flight::redirect(GUST_ROOT.'/login');
      }
    });
    Flight::route('/'.GUST_NAME.'/coffee/confirm',function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT.'/post?coffee=confirm');
      } else {
        Flight::redirect(GUST_ROOT.'/login?coffee=confirm');
      }
    });

    Flight::route('GET /'.GUST_NAME.'/signout',function(){
      wp_logout();
      Flight::redirect(GUST_ROOT);
    });
    Flight::route('GET /'.GUST_NAME.'/@type:post|page',function($type){
      if (Gust::auth()) {
        Flight::render('list.php',array('body_class'=>'manage','type'=>$type));
      } else {
        Flight::redirect(GUST_ROOT.'/login');
      }
    });
    Flight::route('GET /'.GUST_NAME.'/login',function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT);
      } else {
        Flight::render('login.php',array('body_class'=>'ghost-login'));
      }
    });
    Flight::route('GET /'.GUST_NAME.'/forgotten',function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT);
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('POST /'.GUST_NAME.'/coffee',function(){
      Gust::paypal_submit();
    });      

    Flight::route('GET /'.GUST_NAME.'/editor/',function(){
      Flight::redirect(GUST_ROOT.'/editor/post');
    });
    Flight::route('GET /'.GUST_NAME.'/editor/@type:post|page',function($type){
      if (Gust::auth()) {
        $id = Gust::new_post($type);
        Flight::redirect(GUST_ROOT.'/editor/'.$id);
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('GET /'.GUST_NAME.'/editor/@id:[0-9]+',function($id){
      if (Gust::auth()) {
        Flight::render('editor.php',array('body_class'=>'ghost-login'));
      } else {
        Flight::render('login.php',array('body_class'=>'ghost-login'));
      }
    });

    // redirect from /ghost
    Flight::route('/ghost(/@q:.*)',function($q){
        Flight::redirect(GUST_ROOT.'/'.$q);
    });

?>