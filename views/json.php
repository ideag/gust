<?php 
  header('Content-Type:application/json; charset='.get_bloginfo('charset'));
  echo json_encode($return); 
?>