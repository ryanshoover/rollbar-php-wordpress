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

/*
 * PHP logging
 */
function rollbar_wp_initialize_php_logging() {

    $options = get_option( 'rollbar_wp' );

    // Return if logging is not enabled
    $php_logging_enabled = (!empty($options['php_logging_enabled'])) ? 1 : 0;

    if ( $php_logging_enabled === 0 ) {
        return;
    }

    // Return if access token is not set
    $server_side_access_token = (!empty($options['server_side_access_token'])) ? esc_attr(trim($options['server_side_access_token'])) : '';

    if ($server_side_access_token == '')
        return;

    // Finish config parameters
    $environment = (!empty($options['environment'])) ? esc_attr(trim($options['environment'])) : '';
    $logging_level = (!empty($options['logging_level'])) ? esc_attr(trim($options['logging_level'])) : 1024;

    // Config
    $config = array(
        // required
        'access_token' => esc_attr(trim($server_side_access_token)),
        // optional - environment name. any string will do.
        'environment' => esc_attr(trim($environment)),
        // optional - path to directory your code is in. used for linking stack traces.
        'root' => ABSPATH,
        'max_errno' => esc_attr(trim($logging_level))
    );

    // installs global error and exception handlers
    \Rollbar\Rollbar::init($config);
}

/*
 * JS Logging
 */
function rollbar_wp_initialize_js_logging () {

    $options = get_option( 'rollbar_wp' );

    // Return if logging is not enabled
    $js_logging_enabled = (!empty($options['js_logging_enabled'])) ? 1 : 0;

    if ( $js_logging_enabled === 0 ) {
        return;
    }

    // Return if access token is not set
    $client_side_access_token = (!empty($options['client_side_access_token'])) ? trim($options['client_side_access_token']) : '';

    if ($client_side_access_token == '')
        return;

    $environment = (!empty($options['environment'])) ? wp_json_encode(trim($options['environment'])) : '';
    
    $rollbarJs = Rollbar\RollbarJsHelper::buildJs(
        array(
            "accessToken" => $client_side_access_token,
            "captureUncaught" => true,
            "payload" => array(
                "environment" => $environment
            ),
        )
    );
    
    echo $rollbarJs;

}