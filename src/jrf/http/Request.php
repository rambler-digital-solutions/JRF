<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 12:23
 * File: Request.php
 * Package: jrf\lib
 *
 */
namespace jrf\http;
use jrf\http\request\Input;
use jrf\http\request\PhpInput;
use jrf\JRF;

/**
 * Class        Request
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\lib
 */
class Request 
{
	private $info = [];
	private $headers = [];

	/** @var  request\Input */
	private $inputProvider;

	public function __construct(JRF $app)
	{
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$this->info['ip_address'] = $_SERVER['REMOTE_ADDR'];
		}

		if (isset($_SERVER['X_FORWARDED_FOR'])) {
			$this->info['ip_address'] = $_SERVER['X_FORWARDED_FOR'];
		}

		foreach ($_SERVER as $key => $val)
		{
			if (0 === strpos($key, 'HTTP_'))
			{
				$header = str_replace('HTTP_', '', $key);
				$header = explode('_', $header);

				$header = join('-', array_map(function($v) {
					return ucfirst(strtolower($v));
				}, $header));

				$this->headers[$header] = $val;
			}
		}
	}

	/**
	 * POST > STD-IN
	 *
	 * @return string
	 */
	public function body()
	{
		if (!$this->inputProvider) {
			$this->inputProvider = new PhpInput();
		}

		return $this->inputProvider->read();
	}

	/**
	 * @param Input $provider
	 */
	public function setInputProvider(Input $provider)
	{
		$this->inputProvider = $provider;
	}

	/**
	 * HTTP HEADERS NORMAL LIST
	 *
	 * @return array
	 */
	public function headers()
	{
		return $this->headers;
	}

	/**
	 * @return array
	 */
	public function info()
	{
		return $this->info;
	}

	/**
	 * @return bool
	 */
	public function isPostRequest()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Request < #
// --------------------------------------------------------------------------------------------------------------------- 