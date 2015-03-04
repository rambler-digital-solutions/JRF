<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 11:51
 * File: Schema.php
 * Package: jrf\json
 *
 */
namespace jrf\json;

/**
 * Class        Schema
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf\json
 */
class Schema 
{
	const DEFINITION_REQUIRED = 'required';
	const DEFINITION_OPTIONAL = 'optional';

	const TYPE_BOOLEAN = 'boolean';
	const TYPE_NUMERIC = 'int';
	const TYPE_STRING  = 'string';
	const TYPE_ARRAY   = 'array';

	/**
	 * @var array
	 */
	private $params_definitions = [];

	/**
	 * @param array $params
	 *
	 *      $params = [
	 *          'param_name' => [
	 *              'type' => 'int',
	 *              'definition' => 'required',
	 *              'expression' => function ($val) { return $val>0; }
	 *          ],
	 *      ]
	 */
	public function __construct(array $params=[])
	{
		if ($params) {
			foreach ($params as $param=>$definitions) {
				$this->addParam(
					$param,
					isset($definitions['type']) ? $definitions['type'] : self::TYPE_STRING,
					isset($definitions['definition']) ? $definitions['definition'] : self::DEFINITION_OPTIONAL,
					isset($definitions['expression']) ? $definitions['expression'] : null
				);
			}
		}
	}

	/**
	 * @param $name
	 * @param string $type
	 * @param string $definition
	 * @param \Closure $expression
	 */
	public function addParam(
		$name,
		$type=Schema::TYPE_STRING,
		$definition=Schema::DEFINITION_OPTIONAL,
		\Closure $expression=null
	) {
		$this->params_definitions[$name] = [
			$type, $definition, $expression
		];
	}

	/**
	 * @param array $params
	 * @return bool
	 */
	public function check(array $params)
	{
		$scheme_params = array_keys($this->params_definitions);

		foreach ($scheme_params as $key)
		{
			if ($this->paramRequired($key) && !array_key_exists($key, $params)) {
				return false;
			}

			if (array_key_exists($key, $params) && !$this->paramTypeValid($key, $params[$key])) {
				return false;
			}

			if (array_key_exists($key, $params) && !$this->paramExpressionValid($key, $params[$key])) {
				return false;
			}

			unset($params[$key]);
		}

		# unrecognized params
		if ($params) {
			return false;
		}

		return true;
	}

	/**
	 * expression closure check
	 *
	 * @param $param
	 * @param $value
	 * @return bool
	 */
	private function paramExpressionValid($param, $value)
	{
		if (array_key_exists($param, $this->params_definitions))
		{
			$closure = $this->params_definitions[$param][2];
			if (is_callable($closure)) {
				return $closure($value);
			}
		}

		return true;
	}

	/**
	 * check param required definition
	 *
	 * @param $param
	 * @return bool
	 */
	private function paramRequired($param)
	{
		return array_key_exists($param, $this->params_definitions)
			&& $this->params_definitions[$param][1] == self::DEFINITION_REQUIRED;
	}

	/**
	 * type-hint check
	 *
	 * @param $param
	 * @param $value
	 * @return bool
	 */
	private function paramTypeValid($param, $value)
	{
		$type = $this->params_definitions[$param][0];

		switch ($type)
		{
			case self::TYPE_BOOLEAN:
				return is_bool($value);
				break;
			case self::TYPE_NUMERIC:
				return is_numeric($value);
				break;
			case self::TYPE_STRING:
				return is_string($value);
				break;
			case self::TYPE_ARRAY:
				return is_array($value);
				break;
		}

		return false;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Schema < #
// --------------------------------------------------------------------------------------------------------------------- 