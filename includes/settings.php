<?php
/**
 * Settings
 *
 * @package     RollbarWP\Settings
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'rollbar_wp_add_admin_menu' );
add_action( 'admin_init', 'rollbar_wp_settings_init' );

function rollbar_wp_add_admin_menu(  ) {

    add_submenu_page(
        'tools.php',
        'Rollbar',
        'Rollbar',
        'manage_options',
        'rollbar_wp',
        'rollbar_wp_options_page'
    );
}


function rollbar_wp_settings_init(  ) {

register_setting( 'rollbar_wp', 'rollbar_wp_settings' );

    // SECTION: General
	add_settings_section(
        'rollbar_wp_general',
        __( 'Your section description', 'rollbar_wp' ),
        'rollbar_wp_general_callback',
        'rollbar_wp'
    );

    // On/off
    add_settings_field(
        'rollbar_wp_logging_enabled',
        __( 'Logging enabled', 'rollbar_wp' ),
        'rollbar_wp_logging_enabled_render',
        'rollbar_wp',
        'rollbar_wp_general'
    );

    // Token
    add_settings_field(
        'rollbar_wp_server_side_access_token',
        __( 'Server Side Access Token', 'rollbar_wp' ),
        'rollbar_wp_server_side_access_token_render',
        'rollbar_wp',
        'rollbar_wp_general'
    );

    add_settings_field(
        'rollbar_wp_client_side_access_token',
        __( 'Client Side Access Token', 'rollbar_wp' ),
        'rollbar_wp_client_side_access_token_render',
        'rollbar_wp',
        'rollbar_wp_general'
    );
    
    // Config
    add_settings_field(
        'rollbar_wp_logging_level',
        __( 'Logging level', 'rollbar_wp' ),
        'rollbar_wp_logging_level_render',
        'rollbar_wp',
        'rollbar_wp_general'
    );

    // SECTION: Filter
    add_settings_section(
        'rollbar_wp_filter',
        __( 'Your section description', 'rollbar_wp' ),
        'rollbar_wp_filter_callback',
        'rollbar_wp'
    );

    add_settings_field(
        'rollbar_wp_filter_plugins',
        __( 'Plugins', 'rollbar_wp' ),
        'rollbar_wp_filter_plugins_render',
        'rollbar_wp',
        'rollbar_wp_filter'
    );


    /*

	add_settings_field(
        'rollbar_wp_radio_field_2',
        __( 'Settings field description', 'rollbar_wp' ),
        'rollbar_wp_radio_field_2_render',
        'rollbar_wp',
        'rollbar_wp_rollbar_wp_section_general'
    );

	add_settings_field(
        'rollbar_wp_textarea_field_3',
        __( 'Settings field description', 'rollbar_wp' ),
        'rollbar_wp_textarea_field_3_render',
        'rollbar_wp',
        'rollbar_wp_rollbar_wp_section_general'
    );
    */
}

function rollbar_wp_logging_enabled_render(  ) {

    $options = get_option( 'rollbar_wp_settings' );
    ?>
    <input type='checkbox' name='rollbar_wp_settings[rollbar_wp_logging_enabled]' <?php checked( $options['rollbar_wp_logging_enabled'], 1 ); ?> value='1'>
    <?php
}

function rollbar_wp_server_side_access_token_render(  ) {
    $options = get_option( 'rollbar_wp_settings' );
        ?>
        <input type='text' name='rollbar_wp_settings[rollbar_wp_server_side_access_token]' value='<?php echo $options['rollbar_wp_server_side_access_token']; ?>'>
    <?php
}

function rollbar_wp_client_side_access_token_render(  ) {
    $options = get_option( 'rollbar_wp_settings' );
    ?>
    <input type='text' name='rollbar_wp_settings[rollbar_wp_client_side_access_token]' value='<?php echo $options['rollbar_wp_client_side_access_token']; ?>'>
    <?php
}

function rollbar_wp_logging_level_render(  ) {

    $options = get_option( 'rollbar_wp_settings' );
    ?>

    <select name="rollbar_wp_settings[rollbar_wp_logging_level]">
        <option value="1" <?php selected( $options['rollbar_wp_logging_level'], 1 ); ?>><?php _e('Fatal run-time errors (E_ERROR) only', 'rollbar-wp'); ?></option>
        <option value="2" <?php selected( $options['rollbar_wp_logging_level'], 1 ); ?>><?php _e('Run-time warnings (E_WARNING) and above', 'rollbar-wp'); ?></option>
        <option value="4" <?php selected( $options['rollbar_wp_logging_level'], 4); ?>><?php _e('Compile-time parse errors (E_PARSE) and above', 'rollbar-wp'); ?></option>
        <option value="8" <?php selected( $options['rollbar_wp_logging_level'], 8); ?>><?php _e('Run-time notices (E_NOTICE) and above', 'rollbar-wp'); ?></option>
        <option value="256" <?php selected( $options['rollbar_wp_logging_level'], 256); ?>><?php _e('User-generated error messages (E_USER_ERROR) and above', 'rollbar-wp'); ?></option>
        <option value="512" <?php selected( $options['rollbar_wp_logging_level'], 512); ?>><?php _e('User-generated warning messages (E_USER_WARNING) and above', 'rollbar-wp'); ?></option>
        <option value="1024" <?php selected( $options['rollbar_wp_logging_level'], 1024); ?>><?php _e('User-generated notice messages (E_USER_NOTICE) and above', 'rollbar-wp'); ?></option>
        <option value="2048" <?php selected( $options['rollbar_wp_logging_level'], 2028); ?>><?php _e('Suggest code changes to ensure forward compatibility (E_STRICT) and above', 'rollbar-wp'); ?></option>
        <option value="8192" <?php selected( $options['rollbar_wp_logging_level'], 8192); ?>><?php _e('Warnings about code that will not work in future versions (E_DEPRECATED) and above', 'rollbar-wp'); ?></option>
        <option value="32767" <?php selected( $options['rollbar_wp_logging_level'], 32767); ?>><?php _e('Absolutely everything (E_ALL)', 'rollbar-wp'); ?></option>
    </select>

    <?php
}

function rollbar_wp_filter_plugins_render(  ) {

    $options = get_option( 'rollbar_wp_settings' );
    $plugins = get_option('active_plugins');

    if ( count($plugins) != 0 ) {

        echo '<select>';

        foreach ($plugins as $plugin) {

            $array = explode("/", $plugin);

            if ( isset($array[0]) && !empty($array[0]) ) {
                echo '<option value="">';
                echo $array[0];
                echo '</option>';
            }
        }

        echo '</select>';
    } else {
        echo '<p>Keine Plugins installiert.</p>';
    }

    ?>

    <?php /*
    <select name="rollbar_wp_settings[rollbar_wp_filter_plugins]" multiple="multiple">
        <option value="1" <?php selected( $options['rollbar_wp_filter_plugins_1'], 1 ); ?>><?php _e('Fatal run-time errors (E_ERROR) only', 'rollbar-wp'); ?></option>
        <option value="2" <?php selected( $options['rollbar_wp_filter_plugins_2'], 1 ); ?>><?php _e('Run-time warnings (E_WARNING) and above', 'rollbar-wp'); ?></option>
        <option value="4" <?php selected( $options['rollbar_wp_filter_plugins_3'], 4); ?>><?php _e('Compile-time parse errors (E_PARSE) and above', 'rollbar-wp'); ?></option>
    </select>
    */ ?>

    <?php
}




/*

function rollbar_wp_radio_field_2_render(  ) {

$options = get_option( 'rollbar_wp_settings' );
	?>
    <input type='radio' name='rollbar_wp_settings[rollbar_wp_radio_field_2]' <?php checked( $options['rollbar_wp_radio_field_2'], 1 ); ?> value='1'>
<?php

}


function rollbar_wp_textarea_field_3_render(  ) {

$options = get_option( 'rollbar_wp_settings' );
	?>
    <textarea cols='40' rows='5' name='rollbar_wp_settings[rollbar_wp_textarea_field_3]'> 
		<?php echo $options['rollbar_wp_textarea_field_3']; ?>
 	</textarea>
<?php

}
*/

function rollbar_wp_general_callback(  ) {

	echo __( 'General...', 'rollbar_wp' );
}

function rollbar_wp_filter_callback(  ) {

    echo __( 'Filter dies und das :)', 'rollbar_wp' );
}


function rollbar_wp_options_page(  ) {

	?>
    <form action='options.php' method='post'>

        <h2>Rollbar for WordPress</h2>

        <?php
        settings_fields( 'rollbar_wp' );
        do_settings_sections( 'rollbar_wp' );
        submit_button();
        ?>

    </form>
<?php

}

?>