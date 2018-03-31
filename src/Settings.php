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
            
            \wp_register_script( 'AceEditor', \plugin_dir_url(__FILE__)."../public/js/ace-builds/src-min-noconflict/ace.js" );
            
            \wp_localize_script(
                'AceEditor', 
                'AceEditorLocalized', 
                array(
                    'plugin_url' => \plugin_dir_url(__FILE__) . "../",
                )
            );
            
            \wp_enqueue_script(
                "AceEditor",
                \plugin_dir_url(__FILE__)."../public/js/ace-builds/src-min-noconflict/ace.js", 
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
        
        \add_action('init', array(get_called_class(), 'registerSession'));

        \add_action('admin_post_rollbar_wp_restore_defaults', array(get_called_class(), 'restoreDefaultsAction'));
    }
    
    public static function registerSession()
    {
        if( !session_id() ) {
            session_start();
        }
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
        
        $included_errno_options = UI::getOptionOptions('included_errno');
        $human_friendly_errno_options = array();
        foreach ($included_errno_options as $included_errno) {
            $human_friendly_errno_options[$included_errno] = UI::getIncludedErrnoDescriptions($included_errno);
        }
        
        $this->addSetting(
            'logging_level', 
            'rollbar_wp_general', 
            array(
                'type' => UI::SETTING_INPUT_TYPE_SELECTBOX,
                'options' => $human_friendly_errno_options
            )
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
    
    private function addSetting($option, $section, array $overrides = array())
    {
        $type = isset($overrides['type']) ? $overrides['type'] : UI::getOptionType($option);
        $options = isset($overrides['options']) ? $overrides['options'] : UI::getOptionOptions($option);
        
        $display_name = isset($overrides['display_name']) ? $overrides['display_name'] : ucfirst(str_replace("_", " ", $option));
        
        $description = isset($overrides['description']) ? $overrides['description'] : Markdown::defaultTransform($this->parseOptionDetails($option));
        
        $value = (!empty($this->options[$option])) ? 
            \esc_attr(trim($this->options[$option])) : 
            null;
        
        \add_settings_field(
            'rollbar_wp_' . $option,
            __($display_name, 'rollbar'),
            array('Rollbar\Wordpress\UI', 'setting'),
            'rollbar_wp',
            $section,
            array(
                'label_for' => 'rollbar_wp_' . $option,
                'name' => $option,
                'display_name' => $display_name,
                'value' => $value,
                'description' => $description,
                'type' => $type,
                'options' => $options
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
        
        UI::boolean('php_logging_enabled', $php_logging_enabled, null, 'PHP logging enabled');
        ?>&nbsp;<?php
        UI::boolean('js_logging_enabled', $js_logging_enabled, null,'JS logging enabled');
    }

    function accessTokenRender()
    {
        $client_side_access_token = (!empty($this->options['client_side_access_token'])) ? \esc_attr(trim($this->options['client_side_access_token'])) : null;
        $server_side_access_token = (!empty($this->options['server_side_access_token'])) ? \esc_attr(trim($this->options['server_side_access_token'])) : null;

        ?>
        <h4 style="margin: 5px 0;"><?php \_e('Client Side Access Token', 'rollbar-wp'); ?> <small>(post_client_item)</small></h4>
        <?php
        UI::setting(array(
            'name' => 'client_side_access_token', 
            'value' => $client_side_access_token,
            'type' => UI::SETTING_INPUT_TYPE_TEXT
        ));
        ?>
        <h4 style="margin: 15px 0 5px 0;"><?php \_e('Server Side Access Token', 'rollbar-wp'); ?> <small>(post_server_item)</small></h4>
        <?php
        UI::setting(array(
            'name' => 'server_side_access_token', 
            'value' => $server_side_access_token,
            'type' => UI::SETTING_INPUT_TYPE_TEXT
        ));
        ?>     
        <p>
            <small><?php \_e('You can find your access tokens under your project settings: <strong>Project Access Tokens</strong>.', 'rollbar-wp'); ?></small>
        </p>
        <?php
    }

    function optionsPage()
    {
        
        if (isset($_SESSION['rollbar_wp_flash_message'])) {
            ?>
            <div class="<?php echo $_SESSION['rollbar_wp_flash_message']['type']; ?> notice is-dismissable">
                <p><?php echo $_SESSION['rollbar_wp_flash_message']['message']; ?></p>
            </div>
            <?php
            unset($_SESSION['rollbar_wp_flash_message']);
        }
        
        ?>
        <form action='options.php' method='post'>

            <h2>Rollbar for WordPress</h2>

            <?php
            \settings_fields('rollbar_wp');
            \do_settings_sections('rollbar_wp');
            ?>
            </div>
            
            <?php
            \submit_button();
            ?>

        </form>
        
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <input type="hidden" name="action" value="rollbar_wp_restore_defaults" />
            <input 
                type="submit" 
                class="button button-secondary"
                name="restore-defaults"
                id="rollbar_wp_restore_defaults"
                value="Restore defaults"
            />
        </form>
        
        <button
            type="button" 
            class="button button-secondary"
            name="test-logging"
            id="rollbar_wp_test_logging">
            Send test message to Rollbar
        </button>
        <?php
    }
    
    private function parseOptionDetails($option)
    {
        $readme = file_get_contents(__DIR__ . '/../vendor/rollbar/rollbar/README.md');
        
        $option_pos = stripos($readme, '<dt>' . $option);
        
        if ($option_pos !== false) {
        
            $desc_pos = stripos($readme, '<dd>', $option_pos) + strlen('<dd>');
            
            $desc_close = stripos($readme, '</dd>', $desc_pos);
            
            $desc = substr($readme, $desc_pos, $desc_close - $desc_pos);
            
        }
        
        return $desc;
    }
    
    public function getDefaultSetting($setting)
    {
        $defaults = \Rollbar\Defaults::get();
        $method = lcfirst(str_replace('_', '', ucwords($setting, '_')));
        
        if (method_exists($defaults, $method)) {
            return $defaults->$method();
        }
        
        return null;
    }
    
    public static function restoreDefaultsAction()
    {
        \Rollbar\Wordpress\Plugin::instance()->restoreDefaults();
        
        self::flashRedirect(
            "updated", 
            __("Default Rollbar settings restored.", "rollbar")
        );
    }
    
    public static function flashRedirect($type, $message)
    {
        $_SESSION['rollbar_wp_flash_message'] = array(
            "type" => $type,
            "message" => $message
        );
        
        wp_redirect(admin_url('/options-general.php?page=rollbar_wp'));
    }
}

?>