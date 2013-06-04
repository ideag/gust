<?php 
// ========================================== SETTINGS

class tiny_toc_options {
  private $defaults = array();
  private $options = array();
  public $values = array();
  private $id = '';
  private $menu_title = '';
  private $title = '';
  private $file = '';
  private $role = 'administrator';

  function __construct($str='tiny',$menu_title,$title,$options=false,$defaults=false,$file=false,$role=false) {
    $this->defaults = $defaults;
    $this->options = $options;
    $this->file = $file?$file:__FILE__;
    $this->id = $str.'_options';
    $this->menu_title = $menu_title;
    $this->title = $title;
    if($role) $this->role = $role;
  }
// Define default option settings
function add_defaults() {
    $this->load();
    if(!$this->values['position']) {
      update_option($this->id, $this->defaults);
  }
}

// Register our settings. Add the settings section, and settings fields
function init(){
  register_setting( $this->id, $this->id, array($this,'validate') );
  if (is_array($this->options)) foreach ($this->options as $group_id => $group) {
    add_settings_section( $group_id, $group['title'], $group['callback']?is_array($group['callback'])?$group['callback']:array($this,$group['callback']):'', $this->file );
    if (is_array($group['options'])) foreach ($group['options'] as $option_id => $option) {
      $option['args']['option_id'] = $option_id;
      $option['args']['title'] = $option['title'];
      add_settings_field($option_id, $option['title'], $option['callback']?is_array($option['callback'])?$option['callback']:array($this,$option['callback']):'', $this->file, $group_id,$option['args']);      
    }
  }
}

function load(){
  $this->values = get_option($this->id);
}

// Add sub page to the Settings Menu
function add_page() {
  add_options_page($this->title, $this->menu_title, $this->role, $file, array($this,'page'));
}
// ************************************************************************************************************
// Utilities
function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

// ************************************************************************************************************

// Callback functions

// DROP-DOWN-BOX - Name: select - Argument : values: array()
function select($args) {
  $items = $args['values'];
  echo "<select id='{$this->id}_{$args['option_id']}' name='{$this->id}[{$args['option_id']}]'>";
  if ($this->is_assoc($items)) {
    foreach($items as $key=>$item) {
      $selected = ($this->values[$args['option_id']]==$key) ? 'selected="selected"' : '';
      echo "<option value='$key' $selected>$item</option>";
    }
  } else {
    foreach($items as $item) {
      $selected = ($this->values[$args['option_id']]==$item) ? 'selected="selected"' : '';
      echo "<option value='$item' $selected>$item</option>";
    }
  }
  echo "</select>";
}

// CHECKBOX - Name: checkbox
function checkbox($args) {
  //$options = get_option('tiny_subscribe_options');
  if($this->values[$args['option_id']]) { $checked = ' checked="checked" '; }
  echo "<input ".$checked." id='{$args['option_id']}' name='{$this->id}[{$args['option_id']}]' type='checkbox' value=\"true\"/>";
}

// TEXTAREA - Name: textarea - Arguments: rows:int=4 cols:int=20
function textarea($args) {
//  $options = get_option('tiny_subscribe_options');
  if (!$args['rows']) $args['rows']=4;
  if (!$args['cols']) $args['cols']=20;
  echo "<textarea id='{$args['option_id']}' name='{$this->id}[{$args['option_id']}]' rows='{$args['rows']}' cols='{$args['cols']}' type='textarea'>{$this->values[$args['option_id']]}</textarea>";
}

// TEXTBOX - Name: text - Arguments: size:int=40
function text($args) {
  if (!$args['size']) $args['size']=40;
  echo "<input id='{$args['option_id']}' name='{$this->id}[{$args['option_id']}]' size='{$args['size']}' type='text' value='{$this->values[$args['option_id']]}' />";
}

// PASSWORD-TEXTBOX - Name: password - Arguments: size:int=40
function password($args) {
  if (!$args['size']) $args['size']=40;
  echo "<input id='{$args['option_id']}' name='{$this->id}[{$args['option_id']}]' size='{$args['size']}' type='password' value='{$this->values[$args['option_id']]}' />";
}

// RADIO-BUTTON - Name: plugin_options[option_set1]
function radio($args) {
  $items = $args['values'];//array("Square", "Triangle", "Circle");
  if ($this->is_assoc($items)) {
    foreach($items as $key=>$item) {
      $checked = ($this->values[$args['option_id']]==$key) ? 'checked="checked"' : '';
      echo "<label><input ".$checked." value='$key' name='{$this->id}[{$args['option_id']}]' type='radio' /> $item</label><br />";
    }
  } else {
    foreach($items as $item) {
      $checked = ($this->values[$args['option_id']]==$item) ? 'checked="checked"' : '';
      echo "<label><input ".$checked." value='$item' name='{$this->id}[{$args['option_id']}]' type='radio' /> $item</label><br />";
    }
  }
}

// Display the admin options page
function page() {
?>
  <div class="wrap">
    <div class="icon32" id="icon-page"><br></div>
    <h2><?php echo $this->title; ?></h2>
    <?php $this->description; ?>
    <form action="options.php" method="post">
    <?php settings_fields($this->id); ?>
    <?php do_settings_sections($this->file); ?>
    <p class="submit">
      <input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
    </p>
    </form>
  </div>
<?php
}

// Validate user data for some/all of your input fields
function validate($input) {
  if(!in_array($input['min'],array(2,3,4,5,6,7,8,9,10))) $input['min']=3;
  if(!in_array($input['position'],array('above','below'))) $input['position']='above';
  return $input; // return validated input
}

}