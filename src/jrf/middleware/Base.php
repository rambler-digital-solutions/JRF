<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 11:57
 * File: Base.php
 * Package: jrf\middleware
 *
 */
namespace jrf\middleware;
use jrf\JRF;

/**
 * Class        Base
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\middleware
 */
abstract class Base implements Middleware
{
	/**
	 * @var Base
	 */
	protected $next;

	/**
	 * @var JRF
	 */
	protected $app;

	/**
	 * @param JRF $app
	 */
	public function setApplication(JRF $app)
	{
		$this->app = $app;
	}

	/**
	 * @param Middleware $middleware
	 */
	public function setNextMiddleware($middleware)
	{
		$this->next = $middleware;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Base < #
// --------------------------------------------------------------------------------------------------------------------- 