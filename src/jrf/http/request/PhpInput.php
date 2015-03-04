<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 11:40
 * File: PhpInput.php
 * Package: jrf\http\request
 *
 */
namespace jrf\http\request;

/**
 * Class        PhpInput
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf\http\request
 */
class PhpInput extends Input
{
	private $input;

	public function __construct()
	{
		$this->input = file_get_contents("php://input");
	}

	public function read()
	{
		return $this->input;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END PhpInput < #
// --------------------------------------------------------------------------------------------------------------------- 