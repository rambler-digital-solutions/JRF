<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 11:52
 * File: SchemaChecker.php
 * Package: jrf\json
 *
 */
namespace jrf\json;

/**
 * Class        SchemaChecker
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf\json
 */
class SchemaChecker 
{
	/**
	 * @var Schema[]
	 */
	private $registered_methods = [];

	public function registerMethodSchema($method, Schema $schema)
	{
		$this->registered_methods[$method] = $schema;
	}

	public function checkMethod($method, array $params)
	{
		if (array_key_exists($method, $this->registered_methods)) {
			return $this->registered_methods[$method]->check($params);
		}

		return true;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END SchemaChecker < #
// --------------------------------------------------------------------------------------------------------------------- 