<?php
namespace Rollbar\Wordpress;

use Michelf\Markdown;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Settings
{
    const DEFAULT_LOGGING_LEVEL = E_ERROR;
    
    private static $instance;
    
    private $documentation = null;
    
    private $options;

    private function __construct() {
        $this->options = \get_option('rollbar_wp');
    }
    
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public static function init() {
        $instance = self::instance();
        \add_action('admin_menu', array(&$instance, 'addAdminMenu'));
        \add_action('admin_init', array(&$instance, 'addSettings'));
        
        \add_action('admin_enqueue_scripts', function($hook) {
            
            if ($hook != 'settings_page_rollbar_wp') {
                return;
            }
            
            \wp_register_script( 'RollbarWordpressSettings.js', \plugin_dir_url(__FILE__)."../public/js/RollbarWordpressSettings.js" );
            
            \wp_localize_script(
                'RollbarWordpressSettings.js', 
                'RollbarWordpress', 
                array(
                    'plugin_url' => \plugin_dir_url(__FILE__) . "../",
                )
            );
            
            \wp_enqueue_script(
                "RollbarWordpressSettings.js",
                \plugin_dir_url(__FILE__)."../public/js/RollbarWordpressSettings.js", 
                array("jquery"),
                "2.0.1"
            );  
            
            \wp_register_style(
                'RollbarWordpressSettings',
                \plugin_dir_url(__FILE__)."../public/css/RollbarWordpressSettings.css",
                false, 
                '1.0.0'
            );
            \wp_enqueue_style('RollbarWordpressSettings');
        });

    }

    function addAdminMenu()
    {
        add_submenu_page(
            'options-general.php',
            'Rollbar',
            'Rollbar',
            'manage_options',
            'rollbar_wp',
            array(&$this, 'optionsPage')
        );
    }

    function addSettings()
    {
        \register_setting('rollbar_wp', 'rollbar_wp');

        // SECTION: General
        \add_settings_section(
            'rollbar_wp_general',
            false,
            false,
            'rollbar_wp'
        );

        // On/off
        \add_settings_field(
            'rollbar_wp_status',
            __('Status', 'rollbar'),
            array(&$this, 'statusRender'),
            'rollbar_wp',
            'rollbar_wp_general'
        );

        // Token
        \add_settings_field(
            'rollbar_wp_access_token',
            __('Access Token', 'rollbar'),
            array(&$this, 'accessTokenRender'),
            'rollbar_wp',
            'rollbar_wp_general'
        );

        $this->addSetting('environment', 'rollbar_wp_general');

        \add_settings_field(
            'rollbar_wp_logging_level',
            __('Logging level', 'rollbar'),
            array(&$this, 'loggingLevelRender'),
            'rollbar_wp',
            'rollbar_wp_general',
            array( 'label_for' => 'rollbar_wp_logging_level' )
        );
        
        // SECTION: Advanced
        \add_settings_section(
            'rollbar_wp_advanced',
            null,
            array(&$this, 'advancedSectionHeader'),
            'rollbar_wp'
        );
        
        $options = \Rollbar\Config::listOptions();
        $skip = array(
            'access_token', 'environment', 'enabled', 'included_errno',
            'base_api_url'
        );
        
        foreach ($options as $option) {
            if (in_array($option, $skip)) {
                continue;
            }
            
            $this->addSetting($option, 'rollbar_wp_advanced');
        }
    }
    
    private function addSetting($option, $section)
    {
        $option_type = UI::getOptionType($option);
        
        $display_name = ucfirst(str_replace("_", " ", $option));
        
        $details = Markdown::defaultTransform($this->parseOptionDetails($option));
        
        $option_value = (!empty($this->options[$option])) ? 
            \esc_attr(trim($this->options[$option])) : 
            null;
        
        \add_settings_field(
            'rollbar_wp_' . $option,
            __($display_name, 'rollbar'),
            array('Rollbar\Wordpress\UI', 'option'),
            'rollbar_wp',
            $section,
            array(
                'label_for' => 'rollbar_wp_' . $option,
                'option_name' => $option,
                'option_value' => $option_value,
                'details' => $details,
                'value_type' => $option_type
            )
        );
    }
    
    public function advancedSectionHeader()
    {
        $output = '';
        
        $output .=  "<h3 class='hover-pointer' id='rollbar_settings_advanced_header'>" .
                    "   <span id='rollbar_settings_advanced_toggle'>►</span> " .
                    "   Advanced" .
                    "</h3>";
        
        $output .=  "<div id='rollbar_settings_advanced' style='display:none;'>";
        
        echo $output;
    }

    public function statusRender()
    {
        $php_logging_enabled = (!empty($this->options['php_logging_enabled'])) ? 1 : 0;
        $js_logging_enabled = (!empty($this->options['js_logging_enabled'])) ? 1 : 0;
        
        UI::booleanOption('php_logging_enabled', $php_logging_enabled, null, 'PHP logging enabled');
        ?>&nbsp;<?php
        UI::booleanOption('js_logging_enabled', $js_logging_enabled, null,'JS logging enabled');
    }

    function accessTokenRender()
    {
        $client_side_access_token = (!empty($this->options['client_side_access_token'])) ? \esc_attr(trim($this->options['client_side_access_token'])) : null;
        $server_side_access_token = (!empty($this->options['server_side_access_token'])) ? \esc_attr(trim($this->options['server_side_access_token'])) : null;

        ?>
        <h4 style="margin: 5px 0;"><?php \_e('Client Side Access Token', 'rollbar-wp'); ?> <small>(post_client_item)</small></h4>
        <?php
        UI::option(array(
            'option_name' => 'client_side_access_token', 
            'option_value' => $client_side_access_token,
            'value_type' => UI::OPTION_INPUT_TYPE_TEXT
        ));
        ?>
        <h4 style="margin: 15px 0 5px 0;"><?php \_e('Server Side Access Token', 'rollbar-wp'); ?> <small>(post_server_item)</small></h4>
        <?php
        UI::option(array(
            'option_name' => 'server_side_access_token', 
            'option_value' => $server_side_access_token,
            'value_type' => UI::OPTION_INPUT_TYPE_TEXT
        ));
        ?>     
        <p>
            <small><?php \_e('You can find your access tokens under your project settings: <strong>Project Access Tokens</strong>.', 'rollbar-wp'); ?></small>
        </p>
        <?php
    }

    function loggingLevelRender()
    {
        $logging_level = (!empty($this->options['logging_level'])) ? \esc_attr(trim($this->options['logging_level'])) : self::DEFAULT_LOGGING_LEVEL;

        ?>

        <select name="rollbar_wp[logging_level]" id="rollbar_wp_logging_level">
            <option
                value="1" <?php \selected($logging_level, 1); ?>><?php \_e('Fatal run-time errors (E_ERROR) only', 'rollbar-wp'); ?></option>
            <option
                value="2" <?php \selected($logging_level, 2); ?>><?php \_e('Run-time warnings (E_WARNING) and above', 'rollbar-wp'); ?></option>
            <option
                value="4" <?php \selected($logging_level, 4); ?>><?php \_e('Compile-time parse errors (E_PARSE) and above', 'rollbar-wp'); ?></option>
            <option
                value="8" <?php \selected($logging_level, 8); ?>><?php \_e('Run-time notices (E_NOTICE) and above', 'rollbar-wp'); ?></option>
            <option
                value="256" <?php \selected($logging_level, 256); ?>><?php \_e('User-generated error messages (E_USER_ERROR) and above', 'rollbar-wp'); ?></option>
            <option
                value="512" <?php \selected($logging_level, 512); ?>><?php \_e('User-generated warning messages (E_USER_WARNING) and above', 'rollbar-wp'); ?></option>
            <option
                value="1024" <?php \selected($logging_level, 1024); ?>><?php \_e('User-generated notice messages (E_USER_NOTICE) and above', 'rollbar-wp'); ?></option>
            <option
                value="2048" <?php \selected($logging_level, 2028); ?>><?php \_e('Suggest code changes to ensure forward compatibility (E_STRICT) and above', 'rollbar-wp'); ?></option>
            <option
                value="8192" <?php \selected($logging_level, 8192); ?>><?php \_e('Warnings about code that will not work in future versions (E_DEPRECATED) and above', 'rollbar-wp'); ?></option>
            <option
                value="32767" <?php \selected($logging_level, 32767); ?>><?php \_e('Absolutely everything (E_ALL)', 'rollbar-wp'); ?></option>
        </select>

        <?php
    }

    function optionsPage()
    {

        ?>
        <form action='options.php' method='post'>

            <h2>Rollbar for WordPress</h2>

            <?php
            \settings_fields('rollbar_wp');
            \do_settings_sections('rollbar_wp');
            ?>
            </div>
            
            <button 
                type="button" 
                class="button button-secondary"
                name="test-logging"
                id="rollbar_wp_test_logging">
                Send test message to Rollbar
            </button>

        </form>
        <?php
    }
    
    private function parseOptionDetails($option)
    {
        $readme = file_get_contents(__DIR__ . '/../vendor/rollbar/rollbar/README.md');
        
        $option_pos = stripos($readme, '<dt>' . $option);
        
        if ($optionPos !== false) {
        
            $desc_pos = stripos($readme, '<dd>', $option_pos) + strlen('<dd>');
            
            $desc_close = stripos($readme, '</dd>', $desc_pos);
            
            $desc = substr($readme, $desc_pos, $desc_close - $desc_pos);
            
        }
        
        return $desc;
    }
}

?>