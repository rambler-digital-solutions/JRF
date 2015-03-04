<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 11:50
 * File: Base.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        Base
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class Base extends \Exception
{
	static $fault_message = '';
	static $fault_description = '';
	static $fault_code = 0;

	/**
	 * @param null $message
	 * @param null $code
	 * @throws $this
	 */
	public static function raise($message=null, $code=null)
	{
		$message = $message?:static::$fault_message;
		$code    = $code?:static::$fault_code;
		$class   = get_called_class();

		throw new $class($message, $code);
	}

	/**
	 * Raise error by code
	 *
	 * @param $code
	 * @param $message
	 */
	public static function raiseByCode($code, $message)
	{
		$class = 'ServerError';

		if (-32603 == $code) {
			$class = 'InternalError';
		}

		if (-32602 == $code) {
			$class = 'InvalidParams';
		}

		if (-32600 == $code) {
			$class = 'InvalidRequest';
		}

		if (-32601 == $code) {
			$class = 'MethodNotFound';
		}

		if (-32700 == $code) {
			$class = 'ParseError';
		}

		$class_name = '\jrf\fault\\'.$class;
		call_user_func_array([$class_name, 'raise'], [$message, $code]);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Base < #
// --------------------------------------------------------------------------------------------------------------------- 