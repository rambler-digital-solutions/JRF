<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 13:04
 * File: ControllerFactory.php
 * Package: jrf\lib
 *
 */
namespace jrf\controller;
use jrf\fault\MethodNotFound;
use jrf\JRF;
use jrf\json\Schema;
use jrf\json\SchemaChecker;

/**
 * Class        ControllerFactory
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\lib
 */
class Base
{
	/**
	 * @var \Closure[]
	 */
	private $methods = [];

	/**
	 * @var JRF
	 */
	private $app;

	/**
	 * @var SchemaChecker
	 */
	private $schemaChecker;

	/**
	 * @param JRF $app
	 */
	public function initApp(JRF $app)
	{
		$this->app = $app;
	}


	/**
	 * @return JRF
	 */
	public function app()
	{
		return $this->app;
	}

	/**
	 * Метод по хорошему можно переопределить
	 *
	 * @param $method
	 * @param array $args
	 * @param integer $req_id
	 * @return array
	 * @throws \Exception
	 */
	public function runMethodWithArgs($method, array $args=[], $req_id=0)
	{
		$args[] = $this->app();
		$args[] = $req_id;

		return $this->execHandledMethod($method, $args);
	}

	/**
	 * @param $method
	 * @param array $args
	 * @return array
	 */
	protected function execHandledMethod($method, array $args)
	{
		if (!$this->isHandleMethod($method)) {
			MethodNotFound::raise();
		}

		# function ( ..., JRF $app, $req_id ) {}
		$result = call_user_func_array($this->methods[$method], $args);

		return $result;
	}

	/**
	 * @param $method
	 * @return bool
	 */
	public function isHandleMethod($method)
	{
		return array_key_exists($method, $this->methods);
	}

	/**
	 * @param $method
	 * @param callable $callable
	 */
	public function handleMethod($method, \Closure $callable)
	{
		$this->methods[$method] = $callable;
	}

	/**
	 * @return SchemaChecker
	 */
	public function schemaChecker()
	{
		if (!$this->schemaChecker) {
			$this->schemaChecker = new SchemaChecker();
		}

		return $this->schemaChecker;
	}

	/**
	 * @param SchemaChecker $checker
	 */
	public function registerSchemaChecker(SchemaChecker $checker)
	{
		$this->schemaChecker = $checker;
	}

	/**
	 * @param $method
	 * @param Schema $schema
	 */
	public function setSchemaForMethod($method, Schema $schema)
	{
		$this->schemaChecker()->registerMethodSchema($method, $schema);
	}

	/**
	 * @param $method
	 * @param array $params
	 * @return bool
	 */
	public function checkIncomingParamsForMethod($method, array $params=[])
	{
		return $this->schemaChecker()->checkMethod($method, $params);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END ControllerFactory < #
// --------------------------------------------------------------------------------------------------------------------- 