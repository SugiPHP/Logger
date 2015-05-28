<?php
/**
 * @package SugiPHP.Logger
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;
use DateTime;

class Logger extends AbstractLogger
{
    protected static $levels = array(
        LogLevel::EMERGENCY, // 0
        LogLevel::ALERT,     // 1
        LogLevel::CRITICAL,  // 2
        LogLevel::ERROR,     // 3
        LogLevel::WARNING,   // 4
        LogLevel::NOTICE,    // 5
        LogLevel::INFO,      // 6
        LogLevel::DEBUG      // 7
    );

    private $filename = ""; // default is standard error log
    private $logFormat = "[{datetime}] [{level}] {message} {context}";
    private $logLevel = "debug";
    private $dateFormat = "Y-m-d H:i:s";
    private $eol = "\n";
    protected $file;

    public function __construct(array $config = [])
    {
        if (isset($config["filename"])) {
            $this->setFileName($config["filename"]);
        }
        if (isset($config["logFormat"])) {
            $this->setFormat($config["logFormat"]);
        }
        if (isset($config["dateFormat"])) {
            $this->setDateFormat($config["dateFormat"]);
        }
        if (isset($config["level"])) {
            $this->setLevel($config["level"]);
        }
        if (isset($config["eol"])) {
            $this->setEol($config["eol"]);
        }
    }

    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        if (!$level = $this->getLevelName($level)) {
            throw new InvalidArgumentException("Log level '" . $level . "' is invalid!");
        }

        if (!static::checkThreshold($level, $this->logLevel)) {
            return null;
        }

        $formattedMessage = $this->formatMessage($level, $message, $context);

        if ($filename = $this->getFileName()) {
            if (!$this->file) {
                $this->file = fopen($filename, "w+");
            }
            fwrite($this->file, $formattedMessage);

            return null;
        }

        error_log($formattedMessage);
    }

    /**
     * Returns the
     * @return string when the
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * Sets the name of the file optionally with the full path and the extension.
     * When the parameter is empty or missing the default error log will be used.
     *
     * @param string $filename
     */
    public function setFileName($filename = "")
    {
        if ($this->file) {
            fclose($this->file);
            unset($this->file);
        }

        $this->filename = (string) $filename;
    }

    /**
     * Set Log Level Threshold.
     *
     * @param mixed $level
     *
     * @throws InvalidArgumentException
     */
    public function setLevel($level)
    {
        if (!$level = $this->getLevelName($level)) {
            throw new InvalidArgumentException("Log level '" . $level . "' is invalid!");
        }

        $this->logLevel = $level;
    }

    /**
     * Returns PSR-3 log level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->logLevel;
    }

    /**
     * Sets Log Format
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        if (!$format) {
            throw new InvalidArgumentException("Log format cannot be empty!");
        }

        $this->logFormat = $format;
    }

    /**
     * Returns Log Format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->logFormat;
    }

    /**
     * Sets date format that will be used when formating message ({datetime} parameter)
     *
     * @param string $format
     *
     * @throws InvalidArgumentException On empty format
     */
    public function setDateFormat($format)
    {
        if (!$format) {
            throw new InvalidArgumentException("Date format cannot be empty");
        }

        $this->dateFormat = $format;
    }

    /**
     * Returns the date format that will be used when formating message ({datetime} parameter)
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Sets the line endings when writing message.
     *
     * @param string $eol
     */
    public function setEol($eol)
    {
        $this->eol = $eol;
    }

    /**
     * Returns line ending.
     *
     * @return string
     */
    public function getEol()
    {
        return $this->eol;
    }

    /**
     * Returns the PSR-3 logging level name.
     *
     * @param string $level
     *
     * @return string
     */
    public static function getLevelName($level)
    {
        if (is_integer($level) && array_key_exists($level, static::$levels)) {
            return static::$levels[$level];
        }

        $level = strtolower($level);

        return in_array($level, static::$levels) ? $level : false;
    }

    /**
     * Checks level is above log level threshold
     *
     * @param string $level
     * @param string $threshold
     *
     * @return boolean
     */
    public static function checkThreshold($level, $threshold)
    {
        return array_search($threshold, static::$levels) >= array_search($level, static::$levels);
    }

    private function formatMessage($level, $message, $context)
    {
        $msg = $this->logFormat;
        $msg = str_replace(
            array(
                "{datetime}",
                "{level}",
                "{LEVEL}",
                "{message}",
                "{context}"
            ),
            array(
                date($this->getDateFormat()),
                $level,
                strtoupper($level),
                (string) $message,
                ($context) ? json_encode($context) : "",
            ),
            $msg
        );

        return $msg . $this->getEol();
    }
}
