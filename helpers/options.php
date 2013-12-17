<?php
/**
 * @package Book_It
 * @version 2.0
 */

function bookit_render_license() {
  $options = bookit_get_options();
  ?>
  <label for="license">
      <input type="text" name="bookit_plugin_options[license]" id="license" value="<?php echo bookit_get_options('license'); ?>" class="regular-text" > <?php if ( _bookit() ): ?><span class="bookit-green"><b><i class="icon-thumbs-up bookit-link-icon"></i> <?php echo __('Thanks for choosing Book It! to manage your reservations!', 'bookit') ?></b></span><?php else: ?><span class="bookit-red"><b><i class="icon-thumbs-down bookit-link-icon"></i> <?php echo __('Invalid license.', 'bookit') ?></b></span><?php endif; ?>
    </label>
    <p class="description"><?php echo __('If you\'ve purchased the premium version of Book It!, enter your license key here.', 'bookit') ?></p>
  <?php
}

function bookit_render_code_length() {
  $options = bookit_get_options();
  ?>
  <p><label for="code-length">
      <input type="number" min="5" name="bookit_plugin_options[code_length]" id="code-length" value="<?php if( $options['code_length'] ): echo esc_attr($options['code_length']); else: echo 10; endif; ?>" class="small-text" > <?php echo __('characters') ?>
    </label></p>
  <?php
}

function bookit_form_css() {
  $options = bookit_get_options();
  ?>
  <p><label for="form-css">
    <select name="bookit_plugin_options[form_css]" id="form-css">
      <option value="default"<?php if( $options['form_css'] == "default" ): echo ' selected="selected"'; endif; ?>><?php echo __('Default', 'bookit') ?></option>
      <option value=""<?php if( !$options['form_css'] ): echo ' selected="selected"'; endif; ?>><?php echo __('None', 'bookit') ?></option>
    </select>
  </label></p>
  <?php
}

add_action( 'admin_init', 'bookit_options_init' );
function bookit_options_init() {
  register_setting( 'bookit_options', 'bookit_plugin_options', 'bookit_plugin_options_validate' );

  add_settings_section( 'general_settings', __('General Settings', 'bookit'), '__return_false', 'bookit_settings' );
  add_settings_section( 'email_templates', __('Email Templates', 'bookit'), '__return_false', 'bookit_settings' );

  add_settings_field( 'license', __( 'License', 'bookit' ), 'bookit_render_license', 'bookit_settings', 'general_settings' );
  add_settings_field( 'code_length', __( 'Confirmation Code Length', 'bookit' ), 'bookit_render_code_length', 'bookit_settings', 'general_settings' );
  add_settings_field( 'form_css', __( 'Reservation Form CSS', 'bookit' ), 'bookit_form_css', 'bookit_settings', 'general_settings' );

  add_settings_field( 'email_templates', __( 'Email Templates', 'bookit' ), 'bookit_render_email_templates', 'bookit_settings', 'email_templates' );
}

function bookit_plugin_options_validate( $input ) {

  if ( isset($input['email_templates']) ) $input['email_templates'] = serialize($input['email_templates']);
  if ( isset($input['license']) ) $input['license'] = htmlentities($input['license']);

  return $input;
}

function bookit_get_options($key = false) {
  $saved = (array) get_option( 'bookit_plugin_options' );
  $defaults = array();
  $defaults['date_format'] = 'F j, Y';
  $defaults['full_date_format'] = 'F j, Y g:i a';
  $defaults['code_length'] = 10;
  $defaults['license'] = '';
  $defaults['form_css'] = 'default';
  $defaults['email_templates'] = array(
    0 => array(
      'name' => __('Reservation Received', 'bookit'),
      'subject' => __('[[[SITE_NAME]]] Reservation Received', 'bookit'),
      'html' => '<h1>Reservation Details ([[CONFIRMATION_CODE]])</h1>
<table>
  <tr>
    <td><b>Date Reserved:</b></td>
    <td>[[DATE_RESERVED]]</td>
  </tr>
  <tr>
    <td><b>Reservation Date:</b></td>
    <td>[[RESERVATION_DATE]]</td>
  </tr>
  <tr>
    <td><b>Status:</b></td>
    <td>[[STATUS]]</td>
  </tr>
  <tr>
    <td><b>Primary Passenger:</b></td>
    <td>[[PRIMARY_PASSENGER]]</td>
  </tr>
</table>
      '
    ),
    1 => array(
      'name' => __('Reservation Confirmed', 'bookit'),
      'subject' => __('[[[SITE_NAME]]] Reservation Confirmed', 'bookit'),
      'html' => '<h1>Reservation Details ([[CONFIRMATION_CODE]])</h1>
<p>Your reservation has been successfully confirmed.</p>
<table>
  <tr>
    <td><b>Date Reserved:</b></td>
    <td>[[DATE_RESERVED]]</td>
  </tr>
  <tr>
    <td><b>Reservation Date:</b></td>
    <td>[[RESERVATION_DATE]]</td>
  </tr>
  <tr>
    <td><b>Status:</b></td>
    <td>[[STATUS]]</td>
  </tr>
  <tr>
    <td><b>Primary Passenger:</b></td>
    <td>[[PRIMARY_PASSENGER]]</td>
  </tr>
</table>
      '
    )
  );

  $defaults = apply_filters( 'bookit_default_theme_options', $defaults );

  $options = wp_parse_args( $saved, $defaults );
  $options = array_intersect_key( $options, $defaults );

  if ( $key ) {
    if ( $key == 'license' ) {
      $options[$key] = html_entity_decode($options[$key]);
    }

    return $options[$key];
  } else {
    return $options;
  }
}

add_action( 'admin_menu', 'bookit_plugin_options_add_page' );
function bookit_plugin_options_add_page() {
  add_plugins_page( __( 'Book It! Options', 'bookit' ), __( 'Book It! Options', 'bookit' ), 'edit_theme_options', 'bookit_options', 'bookit_plugin_options_render_page' );

  remove_meta_box('tagsdiv-bookit_event_type', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_outsource_company', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_vehicle', 'bookit_reservation', 'side');
  remove_meta_box('submitdiv', 'bookit_reservation', 'side');
  remove_meta_box('commentstatusdiv', 'bookit_reservation', 'normal');
  remove_meta_box('tagsdiv-bookit_reservation_status', 'bookit_reservation', 'side');
  remove_meta_box('tagsdiv-bookit_company', 'bookit_reservation', 'side');
}

function bookit_plugin_options_render_page() {
  ?>
  <div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php echo __('Book It! Transportation Options', 'bookit'); ?></h2>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
      <?php
        settings_fields( 'bookit_options' );
        do_settings_sections( 'bookit_settings' );
        submit_button();
      ?>
    </form>
  </div>
  <?php
}