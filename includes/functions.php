<?php
/**
 * Helper Functions
 *
 * @package     RollbarWP\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Libs
 * 
 * The included copy of rollbar-php is only going to be loaded if the it has
 * not been loaded through Composer yet.
 */
if( !class_exists( 'Rollbar\Rollbar' ) ) {
    require_once ROLLBAR_WP_DIR . 'includes/lib/rollbar-php/vendor/autoload.php';
}

function rollbar_wp_get_settings()
{
    $options = get_option( 'rollbar_wp' );
    
    $settings = array(
        'php_logging_enabled' => (!empty($options['php_logging_enabled'])) ? 1 : 0,
        'js_logging_enabled' => (!empty($options['js_logging_enabled'])) ? 1 : 0,
        'server_side_access_token' => (!empty($options['server_side_access_token'])) ? 
            esc_attr(trim($options['server_side_access_token'])) : 
            '',
        'client_side_access_token' => (!empty($options['client_side_access_token'])) ? trim($options['client_side_access_token']) : '',
        'environment' => (!empty($options['environment'])) ? esc_attr(trim($options['environment'])) : '',
        'logging_level' => (!empty($options['logging_level'])) ? esc_attr(trim($options['logging_level'])) : 1024
    );
    
    return $settings;
}

/*
 * PHP logging
 */
function rollbar_wp_initialize_php_logging() {
    
    $settings = rollbar_wp_get_settings();

    // Return if logging is not enabled
    if ( $settings['php_logging_enabled'] === 0 ) {
        return;
    }

    // Return if access token is not set
    if ($settings['server_side_access_token'] == '')
        return;

    // Config
    $config = array(
        // required
        'access_token' => $settings['server_side_access_token'],
        // optional - environment name. any string will do.
        'environment' => $settings['environment'],
        // optional - path to directory your code is in. used for linking stack traces.
        'root' => ABSPATH,
        'max_errno' => $settings['logging_level']
    );

    // installs global error and exception handlers
    \Rollbar\Rollbar::init($config);
}

/*
 * JS Logging
 */
function rollbar_wp_initialize_js_logging () {
    
    $settings = rollbar_wp_get_settings();

    // Return if logging is not enabled
    if ( $settings['js_logging_enabled'] === 0 ) {
        return;
    }

    // Return if access token is not set
    if ($settings['client_side_access_token'] == '')
        return;
    
    $rollbarJs = Rollbar\RollbarJsHelper::buildJs(
        array(
            'accessToken' => $settings['client_side_access_token'],
            "captureUncaught" => true,
            "payload" => array(
                'environment' => $settings['environment']
            ),
        )
    );
    
    echo $rollbarJs;

}