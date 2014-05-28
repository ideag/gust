<?php 

/**
 * Gust Options
 */
class Gust_Options {

  protected $data = array();

  protected $o = array();


  public function __construct( $data = array() ) {
    $this->data = $data;
    $this->load();

    add_action( 'admin_init', array( $this,'options' ) );
    add_action( 'admin_menu', array( $this,'pages' ) );
  }


  /**
   * Register menu
   */
  public function pages() {
    add_options_page(
      $this->data['page_title'],
      $this->data['menu_title'],
      $this->data['permission'],
      $this->data['menu_slug'],
      array( $this,'page' )
    );
  }


  public function page(){
    ?>
      <div class="wrap">
        <h2><?php echo esc_html( $this->data['page_title'] ); ?></h2>
        <?php if ( ! empty( $this->data['page_description'] ) ) : ?>
          <?php echo wpautop( $this->data['page_description'] ); ?>
        <?php endif; ?>

        <form method="post" action="options.php">
          <?php settings_fields( $this->data['option_name'] ); ?>
          <?php do_settings_sections( $this->data['plugin_path'] ); ?>
          <?php submit_button() ?>
        </form>
      </div>
    <?php
  }


  /**
   * Register option
   */
  public function options() {
    register_setting(
      $this->data['option_name'],
      $this->data['option_name'],
      array( $this,'validate' )
    );

    foreach ( $this->data['section'] as $section ) {
      add_settings_section( $section['slug'], $section['title'], '', $this->data['plugin_path'] );

      foreach ( $section['field'] as $field ) {
        $option_id = $this->get_field_id( $section['slug'], $field['slug'] );

        add_settings_field(
          $option_id,
          sprintf(
            '<label for="%s">%s</label>',
            esc_attr( $option_id ),
            esc_html( $field['title'] )
          ),
          array( $this, 'the_field' ),
          $this->data['plugin_path'],
          $section['slug'],
          array(
            'option_type' => $field['type'],
            'option_id'   => $option_id,
            'description' => ! empty( $field['description'] ) ? $field['description'] : '',
            'label'       => ! empty( $field['label'] ) ? $field['label'] : '',
            'options'     => isset( $field['options'] ) ? $field['options'] : false,
            'value'       => isset( $this->o[ "{$section['slug']}_{$field['slug']}" ] ) ? $this->o[ "{$section['slug']}_{$field['slug']}" ] : false,
          )
        );
      }
    }
  }


  public function get() {
    return get_option( $this->data['option_name'] );
  }


  public function load() {
    $this->o = get_option( $this->data['option_name'] );
    // First time, save defaults
    if ( false === $this->o ) {
      $this->defaults();
    }
  }


  public function defaults() {
    update_option( $this->data['option_name'], $this->data['defaults'] );
    $this->o = $this->data['defaults'];
  }


  public function get_field_id( $section_slug, $field_slug ) {
    return sprintf(
      '%s[%s_%s]',
      $this->data['option_name'],
      $section_slug,
      $field_slug
    );
  }


  public function validate( $values ) {
    foreach ( $this->data['section'] as $section ) {
      foreach ( $section['field'] as $field ) {
        $name = "{$section['slug']}_{$field['slug']}";
        $type = $field['type'];

        if ( ! isset( $values[ $name ] ) ) {
          $values[ $name ] = false;
        }
        // TODO: sanitize for every type;
      }
    }

    return $values;
  }


  public function the_field( $args ) {
    call_user_func( array( $this, $args['option_type'] ), $args );
    if ( ! empty( $args['description'] ) ) :
    ?>
      <p class="description"><?php echo $args['description'] ?></p>
    <?php endif;
  }


  public function text( $args ) {
    printf(
      '<input class="regular-text" id="%1$s" name="%1$s" type="text" value="%2$s" />',
      esc_attr( $args['option_id'] ),
      esc_attr( $args['value'] )
    );
  }


  public function textarea( $args ) {
    printf(
      '<textarea class="all-options" id="%1$s" name="%1$s" rows="5">%2$s</textarea>',
      esc_attr( $args['option_id'] ),
      esc_html( $args['value'] )
    );
  }


  public function select( $args ) {
    printf( '<select id="%1$s" name="%1$s">', esc_attr( $args['option_id'] ) );
    foreach( $args['options']['list'] as $value => $label ) {
      printf(
        '<option value="%s"%s>%s</option>',
        esc_attr( $value ),
        selected( $args['value'], $value, false ),
        esc_attr( $label )
      );
    }
    printf( '</select>' );
  }


  public function checkbox( $args ) {
    if ( ! empty( $args['label'] ) ) :
      printf(
        '<label><input id="%1$s" name="%1$s" type="checkbox" value="true"%2$s /> %3$s</label>',
        esc_attr( $args['option_id'] ),
        checked( $args['value'], 'true', false ),
        esc_html( $args['label'] )
      );
    else :
      printf(
        '<input id="%1$s" name="%1$s" type="checkbox" value="true"%2$s />',
        esc_attr( $args['option_id'] ),
        checked( $args['value'], 'true', false )
      );
    endif;
  }


  public function checkbox_list( $args ) {
    foreach( $args['options']['list'] as $value => $label ) {
      $is_checked = ( isset( $args['value'][ $value ] ) && $args['value'][ $value ] );
      printf(
        '<label for="%1$s"><input id="%1$s" name="%1$s" type="checkbox" value="true"%2$s /> %3$s</label><br />',
        esc_attr( "{$args['option_id']}[{$value}]" ),
        checked( $is_checked, true, false ),
        esc_html( $label )
      );
    }
  }
}
