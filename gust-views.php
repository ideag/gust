<?php
    Flight::route('GET '.GUST_ROOT,function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT.'/post');
      } else {
        Flight::redirect(GUST_ROOT.'/login');
      }
    });
    Flight::route(''.GUST_ROOT.'/coffee/confirm',function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT.'/post?coffee=confirm');
      } else {
        Flight::redirect(GUST_ROOT.'/login?coffee=confirm');
      }
    });

    Flight::route('GET '.GUST_ROOT.'/signout',function(){
      wp_logout();
      Flight::redirect(GUST_ROOT);
    });
    Flight::route('GET '.GUST_ROOT.'/@type:post|page',function($type){
      if (Gust::auth()) {
        Flight::render('list.php',array('body_class'=>'manage','type'=>$type));
      } else {
        Flight::redirect(GUST_ROOT.'/login');
      }
    });
    Flight::route('GET '.GUST_ROOT.'/login',function(){
      if (Gust::auth()) {
        Flight::redirect(GUST_ROOT);
      } else {
        Flight::render('login.php',array('body_class'=>'ghost-login'));
      }
    });
    Flight::route('GET '.GUST_ROOT.'/forgotten',function(){
      if (Gust::auth()) {
        Flight::redirect('.GUST_ROOT.');
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('POST '.GUST_ROOT.'/coffee',function(){
      Gust::paypal_submit();
    });      

    Flight::route('GET '.GUST_ROOT.'/editor/',function(){
      Flight::redirect(GUST_ROOT.'/editor/post');
    });
    Flight::route('GET '.GUST_ROOT.'/editor/@type:post|page',function($type){
      if (Gust::auth()) {
        $id = Gust::new_post($type);
        Flight::redirect(GUST_ROOT.'/editor/'.$id);
      } else {
        Flight::render('forgotten.php',array('body_class'=>'ghost-forgotten'));
      }
    });
    Flight::route('GET '.GUST_ROOT.'/editor/@id:[0-9]+',function($id){
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