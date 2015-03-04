<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 17:46
 * File: StringInput.php
 *
 */
namespace tests\lib;

use jrf\http\request\Input;

/**
 * Class        StringInput
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 */
class StringInput extends Input
{
	private $input;
	public function __construct($input)
	{
		$this->input = $input;
	}

	public function read()
	{
		return $this->input;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END StringInput < #
// --------------------------------------------------------------------------------------------------------------------- 