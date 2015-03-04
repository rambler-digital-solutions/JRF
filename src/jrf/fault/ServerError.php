<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:59
 * File: ServerError.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        ServerError
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class ServerError extends Base
{
	static $fault_message     = 'Server error';
	static $fault_description = 'Reserved server error.';
	static $fault_code        = -32000;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END ServerError < #
// --------------------------------------------------------------------------------------------------------------------- 