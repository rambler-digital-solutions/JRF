<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 13:47
 * File: service.php
 *
 */

include realpath(__DIR__.'/../vendor/autoload.php');

class AccessMiddleware extends \jrf\middleware\Base
{
	private $iplist=[];
	public function __construct(array $iplist) {
		$this->iplist = $iplist;
	}

	public function call()
	{
		$accept  = false;

//		$info    = $this->app->request()->info();
		$headers = $this->app->request()->headers();
//		$ip      = $info['ip_address'];

//		if (isset($headers['X-My-Header']) && isset($headers['X-My-Header-V'])) {
//			if (md5($headers['X-My-Header']) == $headers['X-My-Header-V']) {
//				$accept = true;
//			}
//		}

//		# IP mismatch
//		if (in_array($ip, $this->iplist)) {
//			$accept = true;
//		}

		if (isset($headers['X-Accept-Connection']) && (200==$headers['X-Accept-Connection'])) {
			$accept = true;
		}

		if (!$accept) {
//			\jrf\fault\ServerError::raise('access error');
			# will now work fine (convert into ServerError automatically)
			throw new \RuntimeException('access error');
		}

		$this->next->call();
	}
}


$config = [
	'app.debug' => true,
	'access.ip.list' => ['192.168.1.1', '127.0.0.1']
];

$service = new \jrf\JRF($config);
//$service->add(new AccessMiddleware($service->config('access.ip.list')));


$a_schema_test = [
	'a' => [
		'type' => \jrf\json\Schema::TYPE_NUMERIC,
		'definition' => \jrf\json\Schema::DEFINITION_REQUIRED,
		'expression' => function ($v) { return intval($v) > 0; }
	],
	'b' => [
		'type' => \jrf\json\Schema::TYPE_NUMERIC,
		'definition' => \jrf\json\Schema::DEFINITION_REQUIRED,
		'expression' => function ($v) { return intval($v) > 0; }
	]
];

$schema_test = new \jrf\json\Schema($a_schema_test);
$service->controller()->setSchemaForMethod('test', $schema_test);
$service->controller()->handleMethod('test', function($a, $b, \jrf\JRF $app) {

	if ($a > $b) {
		\jrf\fault\InvalidParams::raise('a>b');
	}

	return $a+$b;
});

$service->listen();