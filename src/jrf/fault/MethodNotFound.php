<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 11:55
 * File: MethodNotFound.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        MethodNotFound
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class MethodNotFound extends Base
{
	static $fault_message     = 'Method not found';
	static $fault_description = 'The method does not exist / is not available.';
	static $fault_code        = -32601;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END MethodNotFound < #
// --------------------------------------------------------------------------------------------------------------------- 