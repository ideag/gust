<?php
class Gust_views {
  static function root(){
      if (Gust::auth()) {
        D::redirect(GUST_ROOT.'/post');
      } else if (Gust::auth('read')) {
        D::redirect(GUST_ROOT.'/login?message=subscriber');
      } else {
        D::redirect(GUST_ROOT.'/login');
      }
    }
  static function login(){
      if (Gust::auth()) {
        D::redirect(GUST_ROOT);
      } else {
        D::render('login',array('body_class'=>'ghost-login'));
      }
    }
  static function post_type($type){
      if (Gust::auth()) {
        D::render('list',array('body_class'=>'manage','type'=>$type),false);
      } else {
        D::redirect(GUST_ROOT.'/login');
      }
    }
  static function signout(){
      wp_logout();
      D::redirect(GUST_ROOT);
    }
  static function forgotten(){
      if (Gust::auth()) {
        D::redirect(GUST_ROOT);
      } else {
        D::render('forgotten',array('body_class'=>'ghost-forgotten'),false);
      }
    }
    static function coffee_confirm(){
      if (Gust::auth()) {
        D::redirect(GUST_ROOT.'/post?coffee=confirm');
      } else {
        D::redirect(GUST_ROOT.'/login?coffee=confirm');
      }
    }
    static function editor_default(){
      D::redirect(GUST_ROOT.'/editor/post');
    }
    static function editor_new($type){
      if (Gust::auth()) {
        $id = Gust::new_post($type);
        D::redirect(GUST_ROOT.'/editor/'.$id);
      } else {
        D::render('login',array('body_class'=>'ghost-login'));
      }
    }
    static function editor($id){
      if (Gust::auth()) {
        D::render('editor',array('body_class'=>'ghost-login'));
      } else {
        D::render('login',array('body_class'=>'ghost-login'));
      }
    }
    static function ghost($q) {
      D::redirect(GUST_ROOT.'/'.$q);      
    }
}
?>