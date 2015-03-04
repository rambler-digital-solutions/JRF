<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 18:01
 * File: Request.php
 * Package: tests\lib
 *
 */
namespace tests\lib;

/**
 * Class        Request
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     tests\lib
 */
class Request 
{
	public static function create_input($method, array $args=[], $id=null)
	{
		$input = [
			'jsonrpc' => "2.0",
			"id"      => $id?:rand(1000000, 9999999),
			"method"  => $method,
			"params"  => $args
		];

		return self::input_from_array($input);
	}

	public static function input_from_array(array $array)
	{
		return new StringInput(json_encode($array));
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Request < #
// --------------------------------------------------------------------------------------------------------------------- 