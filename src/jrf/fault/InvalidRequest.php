<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:52
 * File: InvalidRequest.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        InvalidRequest
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class InvalidRequest extends Base
{
	static $fault_message     = 'Invalid Request';
	static $fault_description = 'The JSON sent is not a valid Request object.';
	static $fault_code        = -32600;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END InvalidRequest < #
// --------------------------------------------------------------------------------------------------------------------- 