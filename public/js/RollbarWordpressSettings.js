// RollbarWordpressSettings.js
(function() {
    
    var rollbar_wp = window['rollbar_wp'] = {
        'settings_page': {
            'php_logging_enabled': {},
            'server_side_access_token_container': {},
            'js_logging_enabled': {},
            'client_side_access_token_container': {},
        }
    };
    
    rollbar_wp.settings_page.server_side_access_token_container.updateUI = function() {
        
        var $php_logging_enabled = rollbar_wp.settings_page.php_logging_enabled.element,
            $server_side_access_token_container = rollbar_wp.settings_page.server_side_access_token_container.element;
        
        if ($php_logging_enabled.prop('checked')) {
            $server_side_access_token_container.show();
        } else {
            $server_side_access_token_container.hide();
        }
        
    };
    
    rollbar_wp.settings_page.client_side_access_token_container.updateUI = function() {
        
        var $js_logging_enabled = rollbar_wp.settings_page.js_logging_enabled.element,
            $client_side_access_token = rollbar_wp.settings_page.client_side_access_token_container.element;
        
        if ($js_logging_enabled.prop('checked')) {
            $client_side_access_token.show();
        } else {
            $client_side_access_token.hide();
        }
        
    };
    
    rollbar_wp.initUI = function() { 
        jQuery(function() {
            
            var clearNotices = function() {
                    jQuery(".rollbar_wp_test_logging_notice").remove();
                },
                successNotice = function(message) {
                    
                    jQuery(
                        '<div class="notice updated rollbar_wp_test_logging_notice is-dismissible">'+
                        message +
                        '</div>'
                    )
                    .hide()
                    .insertAfter("#rollbar_wp_test_logging")
                    .show(400);
                    
                },
                failNotice = function(message) {
                    
                    jQuery(
                        '<div class="notice error rollbar_wp_test_logging_notice is-dismissible">'+
                        message +
                        '</div>'
                    )
                    .hide()
                    .insertAfter("#rollbar_wp_test_logging")
                    .show(400);
                    
                },
                phpSuccessNotice = function() {
                    successNotice(
                        'Test message sent to Rollbar using PHP. Please, check your Rollbar '+
                        'dashboard to see if you received it. Save your changes and '+
                        'you\'re ready to go.'
                    );
                },
                phpFailNotice = function(response) {
                    failNotice(
                        'There was a problem accessing Rollbar service.' +
                        '<p><code>' +
                        'Code: ' + response.code + '<br />' +
                        'Message: ' + response.message +
                        '</pre></p>'
                    )
                },
                jsSuccessNotice = function() {
                    successNotice(
                        'Test message sent to Rollbar using JS. Please, check your Rollbar '+
                        'dashboard to see if you received it. Save your changes and '+
                        'you\'re ready to go.'
                    );
                },
                jsFailNotice = function() {
                    failNotice(
                        'There was a problem accessing Rollbar service using provided credentials '+
                        'for JS logging. Check your client side token.'
                    )
                },
                logThroughPhp = function(server_side_access_token, environment, logging_level) {
                    jQuery.post(
                        "/index.php?rest_route=/rollbar/v1/test-php-logging",
                        {
                            "server_side_access_token": server_side_access_token,
                            "environment": environment,
                            "logging_level": logging_level
                        },
                        function(response) {
                            
                            if (response.err || response.code == 0) {
                                phpFailNotice(response);
                            } else {
                                phpSuccessNotice();
                            }
                            
                        }
                    ).fail(function(response) {
                        
                        phpFailNotice(response);
                        
                    });    
                },
                logThroughJs = function(client_side_access_token, environment, logging_level) {
                    
                    var _rollbarConfig = {
                            accessToken: client_side_access_token,
                            captureUncaught: true,
                            captureUnhandledRejections: true,
                            payload: {
                                environment: environment
                            }
                        }
                        sendRollbarRequest = function() {
                            
                        };
                    
                    if (window.Rollbar == undefined) {
                        
                        jQuery.ajax({
                            url: RollbarWordpress.plugin_url + "vendor/rollbar/rollbar/data/rollbar.snippet.js",
                            success: function(data){
                                eval(data);
                            },
                            dataType: "text",
                            async: false
                            
                        }).fail(function() {
                            jsFailNotice();
                        });
                            
                    }
                    
                    Rollbar.configure(_rollbarConfig);
                                
                    Rollbar.info(
                        "Test message from Rollbar Wordpress plugin using JS: "+
                        "integration with Wordpress successful",
                        function(error, data) {
                            if (error) {
                                jsFailNotice();
                            } else {
                                jsSuccessNotice();
                            }
                        }
                    );
                    
                };
               
            // Save references to jQuery elements for ease of use
            jQuery.each(rollbar_wp.settings_page, function(setting_name, setting) {
                rollbar_wp.settings_page[setting_name]['element'] = jQuery('#rollbar_wp_'+setting_name);
            });
                
            // Set event handlers
            jQuery("#rollbar_wp_test_logging").click(function() {
                
                var server_side_access_token = jQuery("#rollbar_wp_server_side_access_token").val(),
                    client_side_access_token = jQuery("#rollbar_wp_client_side_access_token").val(),
                    environment = jQuery("#rollbar_wp_environment").val(),
                    logging_level = jQuery("#rollbar_wp_logging_level").val(),
                    php_logging_enabled = jQuery('#rollbar_wp_php_logging_enabled').prop('checked'),
                    js_logging_enabled = jQuery('#rollbar_wp_js_logging_enabled').prop('checked');
                    
                clearNotices();
                
                if (php_logging_enabled) {
                    logThroughPhp(server_side_access_token, environment, logging_level);
                } else {
                    failNotice("Skipped testing PHP logging since it is disabled.");
                }
                
                if (js_logging_enabled) {
                    logThroughJs(client_side_access_token, environment, logging_level);
                } else {
                    failNotice("Skipped testing JS logging since it is disabled.");
                }
                
            });
            
            jQuery('#rollbar_settings_advanced_header').click(function(target) {
                
                var $section_advanced_fields = jQuery('#rollbar_settings_advanced'),
                    $section_advanced_toggle = jQuery('#rollbar_settings_advanced_toggle');
                
                $section_advanced_fields.toggle();
                
                if ($section_advanced_fields.is(':visible')) {
                    $section_advanced_toggle.text('▼');
                } else {
                    $section_advanced_toggle.text('►');
                }
                
            });
            
            jQuery(".rollbar_wp_restore_default").click(function(event) {
                
                var $button = jQuery(event.target),
                    setting = $button.attr('data-setting'),
                    defaultValue = $button.find(".default_value").val(),
                    type = $button.attr("data-setting-input-type");
                
                switch (type) {
                    case "SETTING_INPUT_TYPE_PHP":
                        rollbar_wp['settings_page'][setting]['editor'].setValue(defaultValue);
                        break;
                    case "SETTING_INPUT_TYPE_BOOLEAN":
                        $settingInput = jQuery('#rollbar_wp_' + setting);
                        $settingInput.prop("checked", defaultValue);
                        break;
                    default:
                        $settingInput = jQuery('#rollbar_wp_' + setting);
                        $settingInput.val(defaultValue);
                }
                
            });
            
            jQuery.each(rollbar_wp.settings_page, function(i, setting) {
                setting.element.change(function() {
                    rollbar_wp.updateUI();
                });
            });
            
            rollbar_wp.updateUI();
        });
        
    };
    
    rollbar_wp.updateUI = function() {
        jQuery.each(rollbar_wp.settings_page, function(i, setting) {
            if (setting.updateUI) {
                setting.updateUI();
            }
        });
    };
    
    rollbar_wp.initUI();
})();