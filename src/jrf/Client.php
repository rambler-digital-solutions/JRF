<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 15:46
 * File: Client.php
 * Package: jrf
 *
 *
 * USAGE:
 *
 * $client = new Client("http://json-rpc.server/");
 *
 * $pipe = $client->createPipe();
 *
 * $batch = $client->createBatch();
 * $m1_id = $batch->append($client->method('m1', [1,2,3]));
 * $m2_id = $batch->append($client->method('m2'));
 * $m3_id = $batch->append($client->method('m3'));
 *
 * $pipe->add('batch', $batch);
 * $pipe->add('news', $client->method('news.search', ['limit'=>50]));
 * $pipe->add('users', $client->method('users.all', ['limit'=>10]));
 *
 * $response = $client->executePipe($pipe);
 *
 * $m1 = $response['batch'][$m2_id];
 *
 * var_dump($m1->getResult());
 * var_dump($client->getTotalTime());
 *
 */
namespace jrf;
use jrf\fault\Base;
use jrf\fault\InvalidRequest;
use jrf\fault\ServerError;

/**
 * Class        Client
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 06.03.15
 * @since       06.03.15
 * @version     0.01
 * @package     jrf
 */
class Client
{
	private $connect_timeout = 0;
	private $timeout = 0;
	private $user_agent = '';
	private $api_url = '';
	private $debug = false;
	private $time_total = 0.0;

	/**
	 * @param $api_url
	 * @param int $connect_timeout
	 * @param int $timeout
	 * @param bool $debug
	 * @param string $ua
	 */
	public function __construct(
		$api_url,
		$connect_timeout=1,
		$timeout=1,
		$debug=false,
		$ua='JSON-RPC PHP Client'
	)
	{
		$this->api_url = $api_url;
		$this->connect_timeout = $connect_timeout;
		$this->timeout = $timeout;
		$this->debug = $debug;
		$this->user_agent = $ua;
	}

	/**
	 * @return float
	 */
	public function getTotalTime()
	{
		return $this->time_total;
	}

	/**
	 * @param array $headers
	 * @return __batch__
	 */
	public function createBatch(array $headers = [])
	{
		return new __batch__($headers);
	}

	/**
	 * @return __pipe_master__
	 */
	public function createPipe()
	{
		return new __pipe_master__();
	}

	/**
	 * @param $name
	 * @param array $args
	 * @param array $headers
	 * @return __request__
	 */
	public function method($name, array $args = [], array $headers = [])
	{
		return new __request__($name, $args, $headers);
	}

	/**
	 * @param __executable__ $request
	 * @return array
	 */
	public function execute(__executable__ $request)
	{
		$response = __exec__::get()->executeSingle($this->api_url, $request, $this->_getOptions());
		return $this->_processResult($response);
	}

	public function executePipe(__pipe__ $pipe)
	{
		$list   = [];
		$result = __exec__::get()->executeMultiple($this->api_url, $pipe, $this->_getOptions());

		foreach ($result as $name=>$response) {
			$list[$name] = $this->_processResult($response);
		}

		return $list;
	}

	/**
	 * @return array
	 */
	private function _getOptions()
	{
		$options =  [
			'connect_timeout' => $this->connect_timeout,
			'timeout' => $this->timeout
		];

		if ($this->debug) {
			$options['debug'] = true;
		}

		return $options;
	}

	/**
	 * @param array $result
	 * @return array|__result__
	 */
	private function _processResult(array $result)
	{
		$response = json_decode($result[0], true);
		$this->time_total += $result[1]['total_time'];

//		if batch
		if (!isset($response['id']) && is_array($response))
		{
			$return = [];
			foreach ($response as $batch_response)
			{
				if (is_array($batch_response) && isset($batch_response['id'])) {
					$return[$batch_response['id']] = new __result__($batch_response, $result[1]);
				} else {
					$return[] = new __result__(['raw'=>$batch_response], $result[1]);
				}
			}
			return $return;
		}
		else
		{
			if (is_array($response)) {
				return new __result__($response, $result[1]);
			} else {
				return new __result__(['raw'=>$response], $result[1]);
			}
		}
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Client < #
// ---------------------------------------------------------------------------------------------------------------------


/**
 * Тут лежит набор классов для работы.
 * По идее, они никак не должны светиться извне.
 */

interface __executable__ {
	/** @return array */
	public function getPayload();
	/** @return array */
	public function getHeaders();
	/** @return int */
	public function getId();
}

interface __pipe__ {
	/** @return __executable__[] */
	public function listExecutable();
}

class __result__
{
	private $response;
	private $info;

	public function __construct(array $response, array $info)
	{
		$this->response = $response;
		$this->info     = $info;
	}

	public function raiseError()
	{
		if ($this->getErrorCode()) {
			Base::raiseByCode($this->getErrorCode(), $this->getErrorMessage());
		} elseif (isset($this->response['raw'])) {
			ServerError::raise($this->response['raw']);
		} else {
			InvalidRequest::raise(json_encode($this->info));
		}
	}

	public function getId()
	{
		return $this->_readProperty('id');
	}

	public function getVersion()
	{
		return $this->_readProperty('jsonrpc');
	}

	public function getResult()
	{
//		на случай когда дергаем данные
//		а по факту у нас тут ошибка
		if (!$this->success()) {
			$this->raiseError();
		}

		return $this->_readProperty('result');
	}

	public function success()
	{
		return !isset($this->response['error'])
		&& isset($this->response['result'])
		&& (200 == $this->getHttpCode());
	}

	public function getErrorCode()
	{
		return $this->_readProperty('error', ['message'=>'', 'code'=>''])['code'];
	}

	public function getErrorMessage()
	{
		return $this->_readProperty('error', ['message'=>'', 'code'=>''])['message'];
	}

	public function getTimer()
	{
		return [
			'total_time' => $this->info['total_time'],
			'namelookup_time' => $this->info['namelookup_time'],
			'connect_time' => $this->info['connect_time'],
			'pretransfer_time' => $this->info['pretransfer_time'],
		];
	}

	public function getHttpCode()
	{
		return $this->info['http_code'];
	}

	private function _readProperty($name, $default=null)
	{
		return isset($this->response[$name]) ? $this->response[$name] : $default;
	}
}

class __pipe_master__ implements __pipe__
{
	/**
	 * @var __executable__[]
	 */
	private $list_exe = [];

	/**
	 * @param $name
	 * @param __executable__ $request
	 */
	public function add($name, __executable__ $request)
	{
		$this->list_exe[$name] = $request;
	}

	/** @return __executable__[] */
	public function listExecutable()
	{
		return $this->list_exe;
	}
}

class __exec__
{
	private $handles = [];

	/**
	 * @param $api_url
	 * @param __executable__ $request
	 * @param array $options
	 * @return array
	 */
	public function executeSingle($api_url, __executable__ $request, array $options = [])
	{
		$ch = $this->create_curl_handle(
			$api_url,
			json_encode($request->getPayload()),
			$request->getHeaders(),
			$options
		);

		$data = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);

		return [$data, $info];
	}

	public function executeMultiple($api_url, __pipe__ $pipe, array $options = [])
	{
		$mh = curl_multi_init();
		$result = [];

		foreach ($pipe->listExecutable() as $name=>$request)
		{
			$this->handles[$name] = $this->create_curl_handle(
				$api_url,
				json_encode($request->getPayload()),
				$request->getHeaders(),
				$options
			);

			curl_multi_add_handle($mh, $this->handles[$name]);
		}

		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);

		// get content and remove handles
		foreach($this->handles as $name => $handle) {
			$result[$name] = [
				curl_multi_getcontent($handle),
				curl_getinfo($handle)
			];
			curl_multi_remove_handle($mh, $handle);
		}

		curl_multi_close($mh);

		return $result;
	}

	/**
	 * @return __exec__
	 */
	static public function get()
	{
		static $me;
		if (!$me) {
			$me = new __exec__();
		}

		return $me;
	}

	private function create_curl_handle($url, $payload, array $headers = [], array $options=[])
	{
		$ch = curl_init();

		$ua = isset($options['user_agent'])?$options['user_agent']:'NaN';
		$connect_timeout = isset($options['connect_timeout'])?$options['connect_timeout']:10;
		$timeout = isset($options['timeout'])?$options['timeout']:10;
		$verbose = isset($options['debug']);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_buildHeaders($headers));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		if ($verbose) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
		}

		if (is_float($connect_timeout)) {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connect_timeout*1000);
		} else {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		}

		if (is_float($timeout)) {
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout*1000);
		} else {
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}

		return $ch;
	}

	private function _buildHeaders(array $headers)
	{
		$list = [];
		foreach ($headers as $h=>$v) {
			$list[] = implode(': ', [$h, $v]);
		}
		return $list;
	}

	private function __construct() {}
}

class __request__ implements  __executable__
{
	private $payload = [];
	private $headers = [];

	/**
	 * @param $method
	 * @param array $args
	 * @param array $headers
	 */
	public function __construct($method, array $args = [], array $headers = [])
	{
		$this->payload = [
			'jsonrpc' => '2.0',
			'method' => $method,
			'id' => mt_rand()
		];

		if (! empty($args)) {
			$this->payload['params'] = $args;
		}

		$this->headers = $headers;
	}

	/**
	 * @return array
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	public function getId()
	{
		return $this->payload['id'];
	}
}

class __batch__ implements __executable__
{
	private $batch = [];
	private $headers = [];
	private $id = 0;

	public function __construct(array $headers = [])
	{
		$this->headers = $headers;
		$this->id = mt_rand();
	}

	/**
	 * @return array
	 */
	public function getPayload()
	{
		return $this->batch;
	}

	/**
	 * @param __request__ $request
	 * @return int request id
	 */
	public function append(__request__ $request)
	{
		$this->batch[] = $request->getPayload();
		return $request->getId();
	}

	public function getHeaders()
	{
		return $this->headers;
	}

	public function getId()
	{
		return $this->id;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Client < #
// ---------------------------------------------------------------------------------------------------------------------