<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 11:40
 * File: Input.php
 * Package: jrf\http\request
 *
 */
namespace jrf\http\request;

/**
 * Class        Input
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf\http\request
 */
abstract class Input
{
	abstract public function read();
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Input < #
// --------------------------------------------------------------------------------------------------------------------- 