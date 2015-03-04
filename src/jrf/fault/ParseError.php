<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:51
 * File: ParseError.php
 * Package: jrf\fault
 *
 */
namespace jrf\fault;

/**
 * Class        ParseError
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\fault
 */
class ParseError extends Base
{
	static $fault_message     = 'Parse error';
	static $fault_description = 'An error occurred on the server while parsing the JSON text.';
	static $fault_code        = -32700;
}

// ---------------------------------------------------------------------------------------------------------------------
// > END ParseError < #
// --------------------------------------------------------------------------------------------------------------------- 