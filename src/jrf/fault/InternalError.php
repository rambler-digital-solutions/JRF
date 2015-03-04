<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:57
 * File: InternalError.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        InternalError
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class InternalError extends Base
{
	static $fault_message     = 'Internal error';
	static $fault_description = 'Internal JSON-RPC error.';
	static $fault_code        = -32603;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END InternalError < #
// --------------------------------------------------------------------------------------------------------------------- 