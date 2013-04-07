<?php
/**
 * @package    SugiPHP
 * @subpackage Logger
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Logger;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\HandlerInterface;

class Logger extends Monolog
{
	const MONOLOG_LEVEL = 2147483647;

	/**
	 * Filters for every handler
	 */
	protected $filters = array();

	/**
	 * Overriding Monolog constructor.
	 */
	public function __construct()
	{
		parent::__construct("");
	}

	/**
	 * Adds a handler to the stack.
	 *
	 * @param HandlerInterface $handler
	 * @param string $filter
	 */
	public function addHandler(HandlerInterface $handler, $filter = "all")
	{
		$this->handlers[] = $handler;
		$this->filters[] = $filter;
	}

	/**
	 * Pushes a handler on to the stack.
	 *
	 * @param HandlerInterface $handler
	 * @param string $filter
	 */
	public function pushHandler(HandlerInterface $handler, $filter = "all")
	{
		array_unshift($this->filters, $filter);
		parent::pushHandler($handler);
	}

	/**
	 * Pops a handler from the stack
	 *
	 * @return HandlerInterface
	 */
	public function popHandler()
	{
		$handler = parent::popHandler();
		array_shift($this->filters);

		return $handler;
	}

	/**
	 * Adds a log record.
	 *
	 * @param  mixed   $level   The logging level
	 * @param  string  $message The log message
	 * @param  array   $context The log context
	 * @return boolean Whether the record has been processed
	 */
	public function addRecord($level, $message, array $context = array())
	{
		if (!$this->handlers) {
			return false;
		}

		if (!static::$timezone) {
			static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: "UTC");
		}

		try {
			$level_name = static::getLevelName($level);
		} catch (\Exception $e) {
			$level_name = $level;
		}

		$level_name = strtolower($level_name);

		$record = array(
			"message"    => (string) $message,
			"context"    => $context,
			"level_name" => $level_name,
			"datetime"   => \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone)->setTimezone(static::$timezone),
			"extra"      => array(),
			// these are not used by SugiPHP, but are required by Monolog
			"level"      => static::MONOLOG_LEVEL,
			"channel"    => $this->name,
		);
		// check if any handler will handle this message
		$handlerKey = null;
		foreach ($this->handlers as $key => $handler) {
			if (static::isHandlingByFilter($level_name, $this->filters[$key])) {
				$handlerKey = $key;
				break;
			}
		}
		// none found
		if (null === $handlerKey) {
			return false;
		}

		// found at least one, process message and dispatch it
		foreach ($this->processors as $processor) {
			$record = call_user_func($processor, $record);
		}
		while (isset($this->handlers[$handlerKey])) {
			if (static::isHandlingByFilter($level_name, $this->filters[$handlerKey])) {
				$this->handlers[$handlerKey]->handle($record);
			}
			$handlerKey++;
		}

		return true;
	}

	/**
	 * Checks the level is within allowed levels
	 * 
	 * @param  string $level_name
	 * @param  string $filter 
	 * @return boolean
	 */
	protected static function isHandlingByFilter($level_name, $filter = null)
	{
		if (is_null($filter)) {
			return true;
		}

		$filter = strtolower($filter) . " ";
		$level_name = strtolower($level_name);
		if (strpos($filter, "none") === 0) {
			return (strpos($filter, "+$level_name ") > 0);
		}

		return (strpos($filter, "-$level_name ") === false);
	}

}
