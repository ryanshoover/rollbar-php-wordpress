<?php namespace Rollbar\Wordpress;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class UI
{
    public static function option($args)
    {
        extract($args);
        
        switch ($value_type) {
            case self::OPTION_INPUT_TYPE_TEXT:
                self::textOption($option_name, $option_value, $details);
                break;
            case self::OPTION_INPUT_TYPE_BOOLEAN:
                self::booleanOption($option_name, $option_value, $details, $display_name);
                break;
        }
    }
    
    public static function textOption($option_name, $option_value, $details)
    {
        ?>
        <input type='text' name='rollbar_wp[<?php echo $option_name; ?>]' id="rollbar_wp_<?php echo $option_name; ?>"
               value='<?php echo \esc_attr(trim($option_value)); ?>' style="width: 300px;">
        
        <?php
        if (!empty($details)) {
            ?>
            <p>
                <small><?php _e($details, 'rollbar-wp'); ?></small>
            </p>
            <?php
        }
    }
    
    public static function booleanOption($option_name, $option_value, $details = '', $display_name = '')
    {
        $display_name = $display_name ? $display_name : ucfirst(str_replace("_", " ", $option_name));
        ?>
        <input type='checkbox' name='rollbar_wp[<?php echo $option_name; ?>]'
               id="rollbar_wp_<?php echo $option_name; ?>" <?php \checked($option_value, 1); ?> value='1'/>
        <label for="rollbar_wp<?php echo $option_name; ?>">
            <?php \_e($display_name, 'rollbar-wp'); ?>
        </label>
        <?php
        if (!empty($details)) {
            ?>
            <p>
                <small><?php _e($details, 'rollbar-wp'); ?></small>
            </p>
            <?php
        }
    }
    
    public static function getOptionType($option_name)
    {
        if (!isset(self::$option_value_types[$option_name])) {
            throw new \Exception(
                'Configuration option ' . 
                $option_name . ' doesn\'t exist in Rollbar.'
            );
        }
        
        if (is_array(self::$option_value_types[$option_name])) {
            return self::$option_value_types[$option_name]['type'];
        } else {
            return self::$option_value_types[$option_name];   
        }
    }
    
    const OPTION_INPUT_TYPE_TEXT = 'OPTION_INPUT_TYPE_TEXT';
    const OPTION_INPUT_TYPE_TEXTAREA = 'OPTION_INPUT_TYPE_TEXTAREA';
    const OPTION_INPUT_TYPE_PHP = 'OPTION_INPUT_TYPE_PHP';
    const OPTION_INPUT_TYPE_BOOLEAN = 'OPTION_INPUT_TYPE_BOOLEAN';
    const OPTION_INPUT_TYPE_SKIP = 'OPTION_INPUT_TYPE_SKIP';
    const OPTION_INPUT_TYPE_HASH = 'OPTION_INPUT_TYPE_HASH';
    const OPTION_INPUT_TYPE_CUSTOM = 'OPTION_INPUT_TYPE_CUSTOM';
    const OPTION_INPUT_TYPE_SELECTBOX = 'OPTION_INPUT_TYPE_SELECTBOX';
    const OPTION_INPUT_TYPE_CHECKBOXES = 'OPTION_INPUT_TYPE_CHECKBOXES';
    const OPTION_INPUT_TYPE_ARRAY = 'OPTION_INPUT_TYPE_ARRAY';
    
    private static $option_value_types = array(
        'access_token' => self::OPTION_INPUT_TYPE_TEXT,
        'agent_log_location' => self::OPTION_INPUT_TYPE_TEXT,
        'allow_exec' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'endpoint' => self::OPTION_INPUT_TYPE_TEXT,
        'base_api_url' => self::OPTION_INPUT_TYPE_SKIP,
        'branch' => self::OPTION_INPUT_TYPE_TEXT,
        'capture_error_stacktraces' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'checkIgnore' => self::OPTION_INPUT_TYPE_PHP,
        'code_version' => self::OPTION_INPUT_TYPE_TEXT,
        'custom' => self::OPTION_INPUT_TYPE_PHP,
        'enable_utf8_sanitization' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'enabled' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'environment' => self::OPTION_INPUT_TYPE_TEXT,
        'error_sample_rates' => self::OPTION_INPUT_TYPE_CUSTOM,
        'exception_sample_rates' => self::OPTION_INPUT_TYPE_HASH,
        'fluent_host' => self::OPTION_INPUT_TYPE_TEXT,
        'fluent_port' => self::OPTION_INPUT_TYPE_TEXT,
        'fluent_tag' => self::OPTION_INPUT_TYPE_TEXT,
        'handler' => array(
            'type' => self::OPTION_INPUT_TYPE_SELECTBOX,
            'options' => array('blocking', 'agent', 'fluent')
        ),
        'host' => self::OPTION_INPUT_TYPE_TEXT,
        'include_error_code_context' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'include_exception_code_context' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'included_errno' => array(
            'type' => self::OPTION_INPUT_TYPE_CHECKBOXES,
            'options' => array(
                E_ERROR, 
                E_WARNING, 
                E_PARSE,
                E_NOTICE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING,
                E_USER_ERROR,
                E_USER_WARNING,
                E_USER_NOTICE,
                E_STRICT,
                E_RECOVERABLE_ERROR,
                E_DEPRECATED,
                E_USER_DEPRECATED,
                E_ALL
            )
        ),
        'logger' => self::OPTION_INPUT_TYPE_PHP,
        'person' => self::OPTION_INPUT_TYPE_PHP,
        'person_fn' => self::OPTION_INPUT_TYPE_PHP,
        'root' => self::OPTION_INPUT_TYPE_TEXT,
        'scrub_fields' => self::OPTION_INPUT_TYPE_ARRAY,
        'scrub_whitelist' => self::OPTION_INPUT_TYPE_ARRAY,
        'timeout' => self::OPTION_INPUT_TYPE_TEXT,
        'report_suppressed' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'use_error_reporting' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'proxy' => self::OPTION_INPUT_TYPE_TEXT,
        'send_message_trace' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'include_raw_request_body' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'local_vars_dump' => self::OPTION_INPUT_TYPE_BOOLEAN,
        'verbosity' => array(
            'type' => self::OPTION_INPUT_TYPE_SELECTBOX,
            'options' => array(
                \Psr\Log\LogLevel::EMERGENCY,
                \Psr\Log\LogLevel::ALERT,
                \Psr\Log\LogLevel::CRITICAL,
                \Psr\Log\LogLevel::ERROR,
                \Psr\Log\LogLevel::WARNING,
                \Psr\Log\LogLevel::NOTICE,
                \Psr\Log\LogLevel::INFO,
                \Psr\Log\LogLevel::DEBUG
            )
        ),
    );
}