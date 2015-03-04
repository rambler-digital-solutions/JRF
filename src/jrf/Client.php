<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 15:46
 * File: Client.php
 * Package: jrf
 *
 */
namespace jrf;
use jrf\fault\InvalidRequest;
use jrf\fault\Base;

/**
 * Class        Client
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf
 */
class Client
{
	/**
	 * URL of the server
	 *
	 * @access private
	 * @var string
	 */
	private $url;

	/**
	 * HTTP client timeout
	 *
	 * @access private
	 * @var integer
	 */
	private $timeout;

	/**
	 * Enable debug output to the php error log
	 *
	 * @access public
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Default HTTP headers to send to the server
	 *
	 * @access private
	 * @var array
	 */
	private $headers = array(
		'Connection'   => 'close',
		'Content-Type' => 'application/json',
		'Accept'       =>'application/json'
	);

	/**
	 * @var bool
	 */
	private $is_batch = false;

	/**
	 * @var array
	 */
	private $batch = [];

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  string    $url         Server URL
	 * @param  integer   $timeout     Server URL
	 * @param  array     $headers     Custom HTTP headers
	 */
	public function __construct($url, $timeout = 5, $headers = array())
	{
		$this->url = $url;
		$this->timeout = $timeout;
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * @param $header
	 * @param $value
	 */
	public function setHeader($header, $value)
	{
		$header = ucfirst($header);
		$this->headers[$header] = $value;
	}

	/**
	 * Automatic mapping of procedures
	 *
	 * @access public
	 * @param  string   $method   Procedure name
	 * @param  array    $params   Procedure arguments
	 * @return mixed
	 */
	public function __call($method, $params)
	{
		return $this->execute($method, $params);
	}

	/**
	 * Execute
	 *
	 * @access public
	 * @throws InvalidRequest
	 * @param  string   $method
	 * @param  array    $params
	 * @return mixed|void
	 */
	public function execute($method, array $params = array())
	{
		$id = mt_rand();

		$payload = array(
			'jsonrpc' => '2.0',
			'method' => $method,
			'id' => $id
		);

		if (! empty($params)) {
			$payload['params'] = $params;
		}

//		if batch call init
//		just return $id of batch element
//		and append batch with new element
		if ($this->is_batch)
		{
			$this->batch[] = $payload;
			return $payload['id'];
		}

		$result   = $this->doRequest($payload);
		$response = json_decode($result, true);

		if (isset($response['error'])) {
			Base::raiseByCode($response['error']['code'], $response['error']['message']);
		}

		if (isset($response['id']) && $response['id'] == $id && array_key_exists('result', $response)) {
			return $response['result'];
		}

		InvalidRequest::raise($result);

		# phpstorm fix =(
		return null;
	}

	/**
	 * @return $this
	 */
	public function beginBatchCall()
	{
		$this->is_batch = true;
		$this->batch = [];
		return $this;
	}

	/**
	 * Batch-request
	 *
	 * @return array
	 */
	public function commit()
	{
		$response = $this->doRequest($this->batch);
		$result = json_decode($response, true);

		if (count($result) != count($this->batch)) {
			InvalidRequest::raise($response);
		}

		$this->is_batch = false;
		$this->batch = [];

		return $result;
	}

	/**
	 * @return array
	 */
	private function buildHeaders()
	{
		$list = [];
		foreach ($this->headers as $h=>$v) {
			$list[] = implode(': ', [$h, $v]);
		}
		return $list;
	}

	/**
	 * @param $payload
	 * @return array
	 */
	public function doRequest($payload)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, 'JSON-RPC PHP Client');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeaders());
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Client < #
// --------------------------------------------------------------------------------------------------------------------- 