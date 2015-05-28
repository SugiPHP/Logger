<?php
/**
 * Tests for SugiPHP Logger Component
 *
 * @package SugiPHP.Logger
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Logger;

use SugiPHP\Logger\Logger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use PHPUnit_Framework_TestCase;

class TestLogger extends Logger
{
    public function getFileHandle()
    {
        return $this->file;
    }
}

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testCreateNoPreferences()
    {
        $logger = new Logger();
    }

    public function testLogLevelsMessage()
    {
        $logger = new Logger();
        $logger->log("debug", "message");
        $logger->log("info", "message");
        $logger->log("notice", "message");
        $logger->log("warning", "message");
        $logger->log("error", "message");
        $logger->log("critical", "message");
        $logger->log("alert", "message");
        $logger->log("emergency", "message");
    }

    public function testCanUseAll7LevelMethods()
    {
        $logger = new Logger();
        $logger->debug("message");
        $logger->info("message");
        $logger->notice("message");
        $logger->warning("message");
        $logger->error("message");
        $logger->critical("message");
        $logger->alert("message");
        $logger->emergency("message");
    }

    public function testUpperCaseLevel()
    {
        $this->assertEquals("debug", Logger::getLevelName("DEBUG"));
        $this->assertEquals("info", Logger::getLevelName("INFO"));
        $this->assertEquals("notice", Logger::getLevelName("NOTICE"));
        $this->assertEquals("warning", Logger::getLevelName("WARNING"));
        $this->assertEquals("error", Logger::getLevelName("ERROR"));
        $this->assertEquals("critical", Logger::getLevelName("CRITICAL"));
        $this->assertEquals("alert", Logger::getLevelName("ALERT"));
        $this->assertEquals("emergency", Logger::getLevelName("EMERGENCY"));
    }

    public function testConstantLevel()
    {
        $this->assertEquals("debug", LogLevel::DEBUG);
        $this->assertEquals("info", LogLevel::INFO);
        $this->assertEquals("notice", LogLevel::NOTICE);
        $this->assertEquals("warning", LogLevel::WARNING);
        $this->assertEquals("error", LogLevel::ERROR);
        $this->assertEquals("critical", LogLevel::CRITICAL);
        $this->assertEquals("alert", LogLevel::ALERT);
        $this->assertEquals("emergency", LogLevel::EMERGENCY);
    }

    public function testNumericLevel()
    {
        $this->assertEquals("debug", Logger::getLevelName(7));
        $this->assertEquals("info", Logger::getLevelName(6));
        $this->assertEquals("notice", Logger::getLevelName(5));
        $this->assertEquals("warning", Logger::getLevelName(4));
        $this->assertEquals("error", Logger::getLevelName(3));
        $this->assertEquals("critical", Logger::getLevelName(2));
        $this->assertEquals("alert", Logger::getLevelName(1));
        $this->assertEquals("emergency", Logger::getLevelName(0));
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testUnknowLogLevelThrowsAnException()
    {
        $logger = new Logger();
        $logger->log("foo", "message");
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testUnknowNumericLogLevelThrowsAnException()
    {
        $logger = new Logger();
        $logger->log(8, "message");
    }

    public function testThresholds()
    {
        $this->assertFalse(Logger::checkThreshold("debug", "info"));
        $this->assertTrue(Logger::checkThreshold("debug", "debug"));
        $this->assertTrue(Logger::checkThreshold("notice", "info"));
        $this->assertTrue(Logger::checkThreshold("warning", "debug"));
        $this->assertFalse(Logger::checkThreshold("warning", "error"));
        $this->assertFalse(Logger::checkThreshold("notice", "error"));
        $this->assertTrue(Logger::checkThreshold("emergency", "error"));
    }

    public function testSetLevel()
    {
        $logger = new Logger();
        // Initial threshold level is "debug"
        $this->assertSame("debug", $logger->getLevel());
        // Change it
        $logger->setLevel("info");
        $this->assertSame("info", $logger->getLevel());
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testEmptyLogLevelThrowsAnException()
    {
        $logger = new Logger();
        // empty format
        $logger->setLevel("");
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testEmptyLogLevelOnCreateThrowsAnException()
    {
        $logger = new Logger(["level" => ""]);
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testUnknownLogLevelThrowsAnException()
    {
        $logger = new Logger();
        // empty format
        $logger->setLevel("foo");
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testUnknownLogLevelOnCreateThrowsAnException()
    {
        $logger = new Logger(["level" => ""]);
    }

    public function testSetDateFormat()
    {
        $logger = new Logger();
        // Initial Date Format
        $this->assertSame("Y-m-d H:i:s", $logger->getDateFormat());
        // Change it
        $logger->setDateFormat("H:i:s");
        $this->assertSame("H:i:s", $logger->getDateFormat());
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testEmptyDateFormatThrowsAnException()
    {
        $logger = new Logger();
        // empty format
        $logger->setDateFormat("");
    }

    public function testSetDateFormatOnCreate()
    {
        $logger = new Logger(["dateFormat" => "H:i:s"]);
        $this->assertSame("H:i:s", $logger->getDateFormat());
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testSetEmptyDateFormatOnCreate()
    {
        $logger = new Logger(["dateFormat" => ""]);
    }

    public function testSetFormat()
    {
        $logger = new Logger();
        // Initial Log Format
        $this->assertSame("[{datetime}] [{level}] {message} {context}", $logger->getFormat());
        // Change it
        $logger->setFormat("[{datetime}] [{LEVEL}] {message}");
        $this->assertSame("[{datetime}] [{LEVEL}] {message}", $logger->getFormat());
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testEmptyFormatThrowsAnException()
    {
        $logger = new Logger();
        // empty format
        $logger->setFormat("");
    }

    public function testSetFormatOnCreate()
    {
        $logger = new Logger(["logFormat" => "[{level}] {message}"]);
        $this->assertSame("[{level}] {message}", $logger->getFormat());
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testSetEmptyFormatOnCreate()
    {
        $logger = new Logger(["logFormat" => ""]);
    }

    public function testLineEndings()
    {
        $logger = new Logger();
        // check the default line ending
        $this->assertSame("\n", $logger->getEol());
        $logger->setEol("\r\n");
        $this->assertSame("\r\n", $logger->getEol());
        $logger->setEol("");
        $this->assertSame("", $logger->getEol());
        // Change EOL on creation
        $logger = new Logger(["eol" => "\r"]);
        $this->assertSame("\r", $logger->getEol());
    }

    public function testFinalExample()
    {
        $config = array(
            //"filename"   => "php://stdout",
            "filename"   => "php://memory",
            "level"      => "info",  // default threshold level is "debug"
            "logFormat"  => "{datetime} {LEVEL}: {message} {context}",
            "dateFormat" => "m/d/y",
            "eol"        => "|",     // All messages will be saved on 1 line separated with |
        );
        $logger = new TestLogger($config);
        $logger->debug("hello world");
        $logger->info("context will be saved as JSON string", array("foo" => "bar"));
        $logger->error("it happens");

        $fp = $logger->getFileHandle();
        rewind($fp);
        $msg = stream_get_contents($logger->getFileHandle());
        $dt = date("m/d/y");
        $this->assertSame(
            $dt.' INFO: context will be saved as JSON string {"foo":"bar"}|'.$dt.' ERROR: it happens |',
            $msg
        );
    }
}
