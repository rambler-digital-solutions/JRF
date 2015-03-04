<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:56
 * File: InvalidParams.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        InvalidParams
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class InvalidParams extends Base
{
	static $fault_message     = 'Invalid params';
	static $fault_description = 'Invalid method parameter(s).';
	static $fault_code        = -32602;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END InvalidParams < #
// --------------------------------------------------------------------------------------------------------------------- 