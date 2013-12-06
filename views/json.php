<?php 
  header('Content-Type:application/json');
  echo json_encode($return,JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); 
?>