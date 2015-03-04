<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 16:29
 * File: Protocol.php
 * Package: jrf\lib\json
 *
 */
namespace jrf\json;
use jrf\fault\InvalidParams;
use jrf\fault\InvalidRequest;
use jrf\fault\ServerError;
use jrf\controller\Base as BaseController;
use jrf\http\Request;

/**
 * Class        Protocol
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 24.09.14
 * @since       24.09.14
 * @version     0.01
 * @package     jrf\lib\json
 */
class Protocol 
{
	const JSON_RPC_VERSION = '2.0';

	/**
	 * @var BaseController
	 */
	private $controller;

	/**
	 * @param BaseController $controller
	 */
	public function __construct(BaseController $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	public function handleRequest(Request $request)
	{
		$body = $request->body();
		$json = $this->load_schema_from_string($body);

		if ($this->is_batch_request($json))
		{
			$result = [];
			foreach ($json as $batch) {
				$result[] = $this->execute($batch);
			}
		}
		else {
			$result = $this->execute($json);
		}

		return $this->stringify($result);
	}

	/**
	 * @param array $command
	 * @return string
	 */
	private function execute(array $command)
	{
		$json = [
			'jsonrpc' => self::JSON_RPC_VERSION,
			'id'      => isset($command['id'])?$command['id']:null,
		];

		try
		{
			if (   !array_key_exists('id', $command)
				|| !array_key_exists('method', $command)
				|| !array_key_exists('jsonrpc', $command))
			{
				InvalidRequest::raise();
			}

			if (0 !== version_compare(self::JSON_RPC_VERSION, $command['jsonrpc'])) {
				ServerError::raise('Protocol version mismatch');
			}

			$params = isset($command['params']) ? $command['params'] : [];

			if (!$this->controller->checkIncomingParamsForMethod($command['method'], $params)) {
				InvalidParams::raise();
			}

			$json['result'] = $this->controller
				->runMethodWithArgs($command['method'], $params, $command['id']);
		}
		catch (\Exception $e) {
			$json['error'] = [
				'message' => $e->getMessage(),
				'code'    => $e->getCode()
			];
		}

		return $json;
	}

	private function is_batch_request(array $json)
	{
		return !isset($json['id']) && isset($json[0]) && isset($json[0]['id']);
	}

	private function load_schema_from_string($body)
	{
		return json_decode($body, true)?:[];
	}

	public function stringify(array $json)
	{
		return json_encode($json);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Protocol < #
// --------------------------------------------------------------------------------------------------------------------- 