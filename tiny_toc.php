<?php 
/*
Plugin Name: tinyTOC
Plugin URI: http://wp.tribuna.lt/tiny-toc
Description: Automaticly builds a Table of Contents once specific number (eg. 3) of headings (h1-h5) is reached and inserts it before or after post/page content
Version: 0.1
Author: Arūnas
Author URI: http://wp.tribuna.lt/
License: GPLv2 or later
Text Domain: tiny_toc
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
  exit;
}

//==========================================================
// init textdomain
load_plugin_textdomain( 'tiny_toc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
load_muplugin_textdomain( 'tiny_toc', dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 

//==========================================================
// init tinyConfiguration
require_once(plugin_dir_path( __FILE__ ).'tiny_options.php');
$tiny_toc_options = new tiny_toc_options(
  'tiny_toc',
  __('tinyTOC','tiny_toc'),
  __('tinyTOC Options','tiny_toc'),
  array(
    "main" => array(
      'title' => __('Main Settings','tiny_toc'),
      'callback' => '',
      'options' => array(
        'min' => array(
          'title'=>__('Minimum entries for TOC','tiny_toc'),
          'callback' => 'select',
          'args' => array(
            'values' => array(
              2=>2,
              3=>3,
              4=>4,
              5=>5,
              6=>6,
              7=>7,
              8=>8,
              9=>9,
              10=>10,
            )
          )
        ),
        'position' => array(
          'title'=>__('Insert TOC','tiny_toc'),
          'callback' => 'radio',
          'args' => array(
            'values' => array(
              'above' => __('Above the text','tiny_toc'),
              'below' => __('Below the text','tiny_toc'),
//              'custom' =>
            )
          )
        )
      )
    )
  ),
  array( 
    "use_css"=>false,
    "position"=>'above',
    "min"=>3
  ),
  __FILE__
);
$tiny_toc_options->load();
register_activation_hook(__FILE__, array($tiny_toc_options,'add_defaults'));
add_action('admin_init', array($tiny_toc_options,'init') );
add_action('admin_menu', array($tiny_toc_options,'add_page'));

add_filter( 'the_content', 'tiny_toc_filter', 100);
function tiny_toc_filter($content) {
  global $tiny_toc_options;
  preg_match_all('/(<h([12345]{1})[^>]*>(.+?)<\/h[12345]{1}>)/ims', $content, $m);
  $toc = tiny_toc_builder($m);
  if (sizeof($toc)>=$tiny_toc_options->values['min']) {
    $toc_list = tiny_toc_lister($toc);
    $content = tiny_toc_replace($toc,$content);
    if ($tiny_toc_options->values['position']=='above') {
      $content = $toc_list.$content;
    } elseif ($tiny_toc_options->values['position']=='below') {
      $content = $content.$toc_list;
    }
  }
  return $content;
}

function tiny_toc_replace($toc,$content) {
  foreach ($toc as $item) {
    $pat = $item['pattern'];
    $rep = $item['replace'];    
    $count = 1;
    $content = str_replace($pat, $rep, $content,$count);
  }
  return $content;
}

function tiny_toc_lister($toc) {
  $list = "<nav class=\"tiny_toc\"><h1>".__('Table of Contents','tiny_toc')."</h1>\n\n<ol>\n";
  $level = 0;
  $open = false;
  foreach ($toc as $key=>$val) {
    if ($val['level']>$level) {
      while ($val['level']>$level) {
        if (!$open) {
          $list .= '<li>';
          $open = true;
        } 
        $list .= "\n<ol>\n";
        $open = false;
        ++$level;
      }
    } elseif ($val['level']<$level) {
      while ($val['level']<$level) {
        $list .= "</li>\n</ol>\n</li>";
        --$level;
        $open = false;
      }
    }
    // elseif ($val['level']==$level) {
      if (!$open) {
        $list .= '<li>';
        $open = true;
      } else {
        $list .= "</li>\n<li>";
        $open = true;
      }
      $list .="<a href=\"#{$val['id']}\">{$val['title']}</a>";
    //}
  }
  $last = array_pop($toc);
  while ($last['level']<=$level) {
    $list .= "</li>\n</ol>\n</li>";
    --$level;
  //  $open = false;
  }
  $list .= "\n</ol>\n</nav>\n\n";
  return $list;
}

function tiny_toc_builder($m){
  $level = $m[2][0];
  $toc = array();
  $no=-1;
  foreach ($m[2] as $key=>$val) {
    ++$no;
    preg_match('/id="([^"]+)"/',$m[1][$key],$n);
    $id = $n[1];
    if (!$id) $id = 'toc-'.$no;
    $toc[$no] = array(
      'id' => $id,
      'title' => $m[3][$key],
      'level' => $val-$level,
      'pattern' => $m[1][$key],
      'replace' => $n[1]?$m[1][$key]:preg_replace('/^<h([12345]{1})/','<h$1 id="'.$id.'"',$m[1][$key])
    );
  }
  return $toc;
}

?>