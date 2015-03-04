<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 11:55
 * File: JRF.php
 * Package: jrf
 *
 */
namespace jrf;
use jrf\container\Container;
use jrf\fault\ServerError;
use jrf\controller\Base as BaseController;
use jrf\json\Protocol;
use jrf\http\Request;
use jrf\http\Response;
use jrf\http\request\Input;
use jrf\middleware\Middleware;
use \jrf\fault\Base as BaseException;

/**
 * Class        JRF
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf
 */
class JRF
{
	/**
	 * @var Container
	 */
	private $config;

	/**
	 * @var Middleware[]
	 */
	private $middleware;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @var BaseController
	 */
	protected $controller;

	/**
	 * @param array $config
	 */
	public function __construct(array $config=[])
	{
		// do the base merge
		$this->config   = new Container($config);
		$this->request  = new Request($this);
		$this->response = new Response($this);

		$this->middleware = [$this];
	}

	/**
	 * Get/Set Controller
	 *
	 * @param BaseController $controller
	 * @return BaseController
	 */
	public function controller(BaseController $controller = null)
	{
		if (!$this->controller)
		{
			if (!$controller) {
				$controller = new BaseController();
			}
			$controller->initApp($this);
			$this->controller = $controller;
		}

		return $this->controller;
	}

	/**
	 * @return Request
	 */
	public function request()
	{
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->response;
	}

	/**
	 * @param null $option
	 * @param null $default
	 * @return Container|mixed|null
	 */
	public function config($option=null, $default=null)
	{
		if ($option) {
			return $this->config->get($option, $default);
		}
		return $this->config;
	}

	/**
	 * @param Middleware $newMiddleware
	 * @throws \RuntimeException
	 */
	public function add(Middleware $newMiddleware)
	{
		if (in_array($newMiddleware, $this->middleware)) {
			$middleware_class = get_class($newMiddleware);
			throw new \RuntimeException("Tried to queue the same Middleware instance ({$middleware_class}) twice.");
		}

		$newMiddleware->setApplication($this);
		$newMiddleware->setNextMiddleware($this->middleware[0]);
		array_unshift($this->middleware, $newMiddleware);
	}

	/**
	 * Application stack call
	 */
	public function call()
	{
		if (!$this->request()->isPostRequest()) {
			ServerError::raise('Only POST request supported.');
		}
		$body = $this->handleRequest($this->request());
		$this->response()->body($body);
	}

	/**
	 * Run
	 */
	public function listen()
	{
		# start buffer
		ob_start();

		/* magic */
		$this->response()->header('Content-Type', 'application/json; charset=UTF-8');

		try
		{
			try {
				$this->middleware[0]->call();
			}
			# convert default exceptions to ServerError
			# for default error handling response output
			catch (\Exception $e) {
				if (! $e instanceof BaseException) {
					ServerError::raise($e->getMessage());
				} else {
					throw $e;
				}
			}
		}
		catch (BaseException $e) {
			$result = [
				'error' => [
					'message' => $e->getMessage(),
					'code'  => $e->getCode(),
				]
			];

			$protocol = new Protocol($this->controller());
			$body     = $protocol->stringify($result);

			$this->response()->body($body);
		}

		list ($status, $headers, $body) = $this->response()->finalize();

		//Send headers
		if (headers_sent() === false) {
			//Send status
			if (strpos(PHP_SAPI, 'cgi') === 0) {
				header(sprintf('Status: %s', $this->response()->getMessageForCode($status)));
			} else {
				header(sprintf('HTTP/%s %s', $this->config('http.version', '1.1'),
					$this->response()->getMessageForCode($status)));
			}

			//Send headers
			foreach ($headers as $name => $value) {
				$hValues = explode("\n", $value);
				foreach ($hValues as $hVal) {
					header("$name: $hVal", false);
				}
			}
		}

		/** @var string $raw_data (maybe used) */
		$raw_data = ob_get_clean();

		echo $body;

	}

	/**
	 * @param null $message
	 * @param null $code
	 * @param int $http_status
	 */
	public function halt($message=null, $code=null, $http_status=200)
	{
		$this->response()->status($http_status);
		ServerError::raise($message, $code);
	}

	/**
	 * @return Protocol
	 */
	public function createProtocol()
	{
		return new Protocol($this->controller());
	}

	/**
	 * @param Input $input
	 * @return string
	 */
	public function handleInputProvider(Input $input)
	{
		$request  = $this->request();
		$request->setInputProvider($input);
		return $this->handleRequest($request);
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	public function handleRequest(Request $request)
	{
		$protocol = $this->createProtocol();
		return $protocol->handleRequest($request);
	}

}

// ---------------------------------------------------------------------------------------------------------------------
// > END JRF < #
// --------------------------------------------------------------------------------------------------------------------- 