<?php
namespace Rollbar\Wordpress\Tests;

use Rollbar\Payload\Level;

/**
 * Class SettingsTest
 *
 * @package Rollbar\Wordpress\Tests
 */
class SettingsTest extends BaseTestCase {
    
    private $subject;
    
    public function setUp()
    {
        $this->subject = \Rollbar\Wordpress\Settings::instance();
    }
    
    public function testGetDefaultSetting()
    {
        $this->assertEquals('production', $this->subject->getDefaultSetting('environment'));
        $this->assertTrue($this->subject->getDefaultSetting('capture_error_stacktraces'));
    }
}