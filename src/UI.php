<?php namespace Rollbar\Wordpress;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class UI
{
    public static function setting($args)
    {
        extract($args);
        
        switch ($type) {
            case self::SETTING_INPUT_TYPE_TEXT:
                self::textInput($name, $value, $description);
                break;
            case self::SETTING_INPUT_TYPE_BOOLEAN:
                self::boolean($name, $value, $description, $display_name);
                break;
            case self::SETTING_INPUT_TYPE_PHP:
                self::phpEditor($name, $value, $description);
                break;
            case self::SETTING_INPUT_TYPE_SELECTBOX:
                self::select($name, $options, $value, $description);
                break;
        }
    }
    
    public static function select($name, $options, $selected, $description)
    {
        if (!empty($description)) {
            ?>
            <p>
                <?php _e($description, 'rollbar-wp'); ?>
            </p>
            <?php
        }
        
        ?>
        <select name="rollbar_wp[<?php echo $name; ?>]" id="rollbar_wp_<?php echo $name; ?>">
            <?php
            foreach ($options as $option_value => $option_name) {
                ?>
                <option
                    value="<?php echo $option_value ?>"
                    <?php \selected($selected, $option_value); ?>
                ><?php \_e($option_name, 'rollbar-wp'); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }
    
    public static function textInput($name, $value, $description)
    {
        if (!empty($description)) {
            ?>
            <p>
                <?php _e($description, 'rollbar-wp'); ?>
            </p>
            <?php
        }
        ?>
        <input type='text' name='rollbar_wp[<?php echo $name; ?>]' id="rollbar_wp_<?php echo $name; ?>"
               value='<?php echo \esc_attr(trim($value)); ?>' style="width: 300px;">
        
        <?php
    }
    
    public static function phpEditor($name, $value, $description)
    {
        if (!empty($description)) {
            ?>
            <p>
                <?php _e($description, 'rollbar-wp'); ?>
            </p>
            <?php
        }
        ?>
        <div 
            id="rollbar_wp_<?php echo $name; ?>_editor"
            style="height: 300px;"><?php echo \esc_attr(trim($value)); ?></div>
        <script>
            var editor_<?php echo $name; ?> = ace.edit("rollbar_wp_<?php echo $name; ?>_editor");
            editor_<?php echo $name; ?>.setTheme("ace/theme/chrome");
            editor_<?php echo $name; ?>.session.setMode({path:"ace/mode/php", inline:true});
        </script>
        <?php
    }
    
    public static function boolean($name, $value, $description = '', $display_name = '')
    {
        if (!empty($description)) {
            ?>
            <p>
                <?php _e($description, 'rollbar-wp'); ?>
            </p>
            <?php
        }
        $display_name = $display_name ? $display_name : ucfirst(str_replace("_", " ", $name));
        ?>
        <input type='checkbox' name='rollbar_wp[<?php echo $name; ?>]'
               id="rollbar_wp_<?php echo $name; ?>" <?php \checked($value, 1); ?> value='1'/>
        <label for="rollbar_wp_<?php echo $name; ?>">
            <?php \_e($display_name, 'rollbar-wp'); ?>
        </label>
        <?php
    }
    
    public static function getOptionType($option)
    {
        if (!isset(self::$option_value_types[$option])) {
            throw new \Exception(
                'Configuration option ' . 
                $option . ' doesn\'t exist in Rollbar.'
            );
        }
        
        if (is_array(self::$option_value_types[$option])) {
            return self::$option_value_types[$option]['type'];
        } else {
            return self::$option_value_types[$option];   
        }
    }
    
    public static function getOptionOptions($option)
    {
        if (!isset(self::$option_value_types[$option])) {
            throw new \Exception(
                'Configuration option ' . 
                $option . ' doesn\'t exist in Rollbar.'
            );
        }
        
        if (is_array(self::$option_value_types[$option])) {
            return self::$option_value_types[$option]['options'];
        }
        
        return array();
    }
    
    public function getIncludedErrnoDescriptions($value)
    {
        switch ($value) {
            case E_ERROR:
                return \__('Fatal run-time errors (E_ERROR) only', 'rollbar-wp');
                break;
            case E_WARNING:
                return \__('Run-time warnings (E_WARNING) and above', 'rollbar-wp');
                break;
            case E_PARSE:
                return \__('Compile-time parse errors (E_PARSE) and above', 'rollbar-wp');
                break;
            case E_NOTICE:
                return \__('Run-time notices (E_NOTICE) and above', 'rollbar-wp');
                break;
            case E_USER_ERROR:
                return \__('User-generated error messages (E_USER_ERROR) and above', 'rollbar-wp');
                break;
            case E_USER_WARNING:
                return \__('User-generated warning messages (E_USER_WARNING) and above', 'rollbar-wp');
                break;
            case E_USER_NOTICE:
                return \__('User-generated notice messages (E_USER_NOTICE) and above', 'rollbar-wp');
                break;
            case E_STRICT:
                return \__('Suggest code changes to ensure forward compatibility (E_STRICT) and above', 'rollbar-wp');
                break;
            case E_DEPRECATED:
                return \__('Warnings about code that will not work in future versions (E_DEPRECATED) and above', 'rollbar-wp');
                break;
            case E_ALL:
                return \__('Absolutely everything (E_ALL)', 'rollbar-wp');
                break;
        }
        
        return null;
    }
    
    const SETTING_INPUT_TYPE_TEXT = 'SETTING_INPUT_TYPE_TEXT';
    const SETTING_INPUT_TYPE_TEXTAREA = 'SETTING_INPUT_TYPE_TEXTAREA';
    const SETTING_INPUT_TYPE_PHP = 'SETTING_INPUT_TYPE_PHP';
    const SETTING_INPUT_TYPE_BOOLEAN = 'SETTING_INPUT_TYPE_BOOLEAN';
    const SETTING_INPUT_TYPE_SKIP = 'SETTING_INPUT_TYPE_SKIP';
    const SETTING_INPUT_TYPE_SELECTBOX = 'SETTING_INPUT_TYPE_SELECTBOX';
    const SETTING_INPUT_TYPE_CHECKBOXES = 'SETTING_INPUT_TYPE_CHECKBOXES';
    
    private static $option_value_types = array(
        'access_token' => self::SETTING_INPUT_TYPE_TEXT,
        'agent_log_location' => self::SETTING_INPUT_TYPE_TEXT,
        'allow_exec' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'endpoint' => self::SETTING_INPUT_TYPE_TEXT,
        'base_api_url' => self::SETTING_INPUT_TYPE_SKIP,
        'branch' => self::SETTING_INPUT_TYPE_TEXT,
        'capture_error_stacktraces' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'check_ignore' => self::SETTING_INPUT_TYPE_PHP,
        'code_version' => self::SETTING_INPUT_TYPE_TEXT,
        'custom' => self::SETTING_INPUT_TYPE_PHP,
        'enable_utf8_sanitization' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'enabled' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'environment' => self::SETTING_INPUT_TYPE_TEXT,
        'error_sample_rates' => self::SETTING_INPUT_TYPE_PHP,
        'exception_sample_rates' => self::SETTING_INPUT_TYPE_PHP,
        'fluent_host' => self::SETTING_INPUT_TYPE_TEXT,
        'fluent_port' => self::SETTING_INPUT_TYPE_TEXT,
        'fluent_tag' => self::SETTING_INPUT_TYPE_TEXT,
        'handler' => array(
            'type' => self::SETTING_INPUT_TYPE_SELECTBOX,
            'options' => array('blocking', 'agent', 'fluent')
        ),
        'host' => self::SETTING_INPUT_TYPE_TEXT,
        'include_error_code_context' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'include_exception_code_context' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'included_errno' => array(
            'type' => self::SETTING_INPUT_TYPE_SELECTBOX,
            'options' => array(
                E_ERROR, 
                E_WARNING, 
                E_PARSE,
                E_NOTICE,
                E_USER_ERROR,
                E_USER_WARNING,
                E_USER_NOTICE,
                E_STRICT,
                E_DEPRECATED,
                E_ALL
            )
        ),
        'logger' => self::SETTING_INPUT_TYPE_PHP,
        'person' => self::SETTING_INPUT_TYPE_PHP,
        'person_fn' => self::SETTING_INPUT_TYPE_PHP,
        'root' => self::SETTING_INPUT_TYPE_TEXT,
        'scrub_fields' => self::SETTING_INPUT_TYPE_PHP,
        'scrub_whitelist' => self::SETTING_INPUT_TYPE_PHP,
        'timeout' => self::SETTING_INPUT_TYPE_TEXT,
        'report_suppressed' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'use_error_reporting' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'proxy' => self::SETTING_INPUT_TYPE_TEXT,
        'send_message_trace' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'include_raw_request_body' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'local_vars_dump' => self::SETTING_INPUT_TYPE_BOOLEAN,
        'verbosity' => array(
            'type' => self::SETTING_INPUT_TYPE_SELECTBOX,
            'options' => array(
                \Psr\Log\LogLevel::EMERGENCY => '\Psr\Log\LogLevel::EMERGENCY',
                \Psr\Log\LogLevel::ALERT => '\Psr\Log\LogLevel::ALERT',
                \Psr\Log\LogLevel::CRITICAL => '\Psr\Log\LogLevel::CRITICAL',
                \Psr\Log\LogLevel::ERROR => '\Psr\Log\LogLevel::ERROR',
                \Psr\Log\LogLevel::WARNING => '\Psr\Log\LogLevel::WARNING',
                \Psr\Log\LogLevel::NOTICE => '\Psr\Log\LogLevel::NOTICE',
                \Psr\Log\LogLevel::INFO => '\Psr\Log\LogLevel::INFO',
                \Psr\Log\LogLevel::DEBUG => '\Psr\Log\LogLevel::DEBUG'
            )
        ),
    );
}