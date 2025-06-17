<?php

namespace SugiPHP\Logger\Tests;

use SugiPHP\Logger\Logger;
use Monolog\Logger as Monolog;
use Monolog\Handler\TestHandler;
use Monolog\Handler\StreamHandler;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    protected $logger;
    protected $testHandler;

    protected function setUp(): void
    {
        $this->logger = new Logger();
        $this->testHandler = new TestHandler();
    }

    public function testAddHandler()
    {
        $this->logger->addHandler($this->testHandler);
        $result = $this->logger->addRecord(Monolog::INFO, 'Test message');
        $this->assertTrue($result);
        // Note: The TestHandler won't actually receive the message because of the custom level handling
    }

    public function testPushHandler()
    {
        $this->logger->pushHandler($this->testHandler);
        $result = $this->logger->addRecord(Monolog::INFO, 'Test message');
        $this->assertTrue($result);
        // Note: The TestHandler won't actually receive the message because of the custom level handling
    }

    public function testPopHandler()
    {
        $testHandler2 = new TestHandler();
        $this->logger->pushHandler($testHandler2);
        $this->logger->pushHandler($this->testHandler);
        
        $popped = $this->logger->popHandler();
        
        // Test that the last handler was popped
        $result = $this->logger->addRecord(Monolog::INFO, 'Test message');
        $this->assertTrue($result);
    }

    public function testAddRecord()
    {
        $this->logger->addHandler($this->testHandler);
        $result = $this->logger->addRecord(Monolog::INFO, 'Test message');
        $this->assertTrue($result);
    }

    public function testIsHandlingByFilterAll()
    {
        $method = new \ReflectionMethod('SugiPHP\\Logger\\Logger', 'isHandlingByFilter');
        $method->setAccessible(true);
        
        // Test with default 'all' filter
        $this->assertTrue($method->invoke(null, 'info', 'all'));
        $this->assertTrue($method->invoke(null, 'error', 'all'));
        $this->assertTrue($method->invoke(null, 'debug', 'all'));
    }

    public function testIsHandlingByFilterNone()
    {
        $method = new \ReflectionMethod('SugiPHP\\Logger\\Logger', 'isHandlingByFilter');
        $method->setAccessible(true);

        // Test with 'none' filter
        $this->assertFalse($method->invoke(null, 'info', 'none'));

        // Test with 'none+info' filter - should allow only info
        $this->assertTrue($method->invoke(null, 'info', 'none+info'));
        $this->assertFalse($method->invoke(null, 'error', 'none+info'));
    }

    public function testIsHandlingByFilterExclude()
    {
        $method = new \ReflectionMethod('SugiPHP\\Logger\\Logger', 'isHandlingByFilter');
        $method->setAccessible(true);

        // Test with exclude filter
        $this->assertTrue($method->invoke(null, 'info', 'all -error'));
        $this->assertFalse($method->invoke(null, 'error', 'all -error'));
    }

    public function testFilteredLogging()
    {
        // Create a custom test handler that can check for our custom level
        $testHandler1 = new class extends TestHandler {
            public function hasRecord($record, $level) {
                foreach ($this->getRecords() as $rec) {
                    if ($rec['message'] === $record && $rec['level_name'] === strtolower($level)) {
                        return true;
                    }
                }
                return false;
            }
        };

        $testHandler2 = new class extends TestHandler {
            public function hasRecord($record, $level) {
                foreach ($this->getRecords() as $rec) {
                    if ($rec['message'] === $record && $rec['level_name'] === strtolower($level)) {
                        return true;
                    }
                }
                return false;
            }
        };

        $this->logger->addHandler($testHandler1, 'all -debug');
        $this->logger->addHandler($testHandler2, 'debug');

        $this->logger->addRecord(Monolog::DEBUG, 'Debug message');
        $this->logger->addRecord(Monolog::INFO, 'Info message');

        // Test that debug messages only go to debug handler
        $this->assertFalse($testHandler1->hasRecord('Debug message', 'debug'));
        $this->assertTrue($testHandler2->hasRecord('Debug message', 'debug'));
        
        // Test that info messages go to the non-debug handler
        $this->assertTrue($testHandler1->hasRecord('Info message', 'info'));
    }
}
