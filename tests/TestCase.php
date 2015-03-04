<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 17:42
 * File: TestCase.php
 *
 */
namespace tests;
use jrf\JRF;
use jrf\json\Schema;
use \tests\lib\Request as RequestBuilder;

/**
 * Class        TestCase
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var JRF
	 */
	private $service;

	public function setUp()
	{
		$this->service = new JRF();

		// setup default methods

		$this->service->controller()->handleMethod('add', function($param1, $param2) {
			return $param1+$param2;
		});
	}

	public function testRequest()
	{
		$request_id = rand(100000, 999999);

		$data = $this->call('add', [10, 30], $request_id);

		$this->assertEquals($data['result'], 40);
		$this->assertEquals($data['id'], $request_id);
	}

	public function testSchema()
	{
		$request_id = rand(100000, 999999);
		$params = [
			0 => [
				'type' => Schema::TYPE_NUMERIC,
				'definition' => Schema::DEFINITION_REQUIRED,
				'expression' => function ($v) { return intval($v) > 0; }
			],
			1 => [
				'type' => Schema::TYPE_NUMERIC,
				'definition' => Schema::DEFINITION_REQUIRED,
				'expression' => function ($v) { return intval($v) > 0; }
			]
		];
		$schema = new Schema($params);
		$this->service->controller()->setSchemaForMethod('add', $schema);

		// first call (unsigned int)

		$data = $this->call('add', [-10, 30], $request_id);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], $request_id);
		$this->assertEquals($data['error']['code'], -32602);

		// second call (missing param)

		$data = $this->call('add', [10], $request_id);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], $request_id);
		$this->assertEquals($data['error']['code'], -32602);

		// third call (type mismatch)

		$data = $this->call('add', [10, 'abc'], $request_id);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], $request_id);
		$this->assertEquals($data['error']['code'], -32602);
	}

	public function testBadInput()
	{
		// test mismatch version

		$data = $this->call_raw(["jsonrpc"=>"10", "id"=>"12", "method"=>"method_not_exists"]);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], 12);
		$this->assertEquals($data['error']['code'], -32000);

		// test method not found

		$data = $this->call_raw(['jsonrpc'=>"2.0", "id"=>"12", "method"=>"method_not_exists"]);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], 12);
		$this->assertEquals($data['error']['code'], -32601);

		// check schema error

		$data = $this->call_raw([]);

		$this->assertEquals(isset($data['result']), false);
		$this->assertEquals($data['id'], null);
		$this->assertEquals($data['error']['code'], -32600);
	}

	public function testBatch()
	{
		$request = [
			['jsonrpc'=>"2.0", "id"=>"1", "method"=>"add", "params"=>[12,33]],
			['jsonrpc'=>"2.0", "id"=>"2", "method"=>"add_2", "params"=>[1,1]],
			['jsonrpc'=>"3.0", "id"=>"3", "method"=>"add", "params"=>[-94,-90]],
			[],
		];

		$data = $this->call_raw($request);

		$this->assertCount(4, $data);
		$this->assertArrayHasKey('error', $data[1]);
		$this->assertArrayHasKey('error', $data[2]);
		$this->assertArrayHasKey('error', $data[3]);

		$this->assertEquals($data[1]['error']['code'], -32601);
		$this->assertEquals($data[2]['error']['code'], -32000);
		$this->assertEquals($data[3]['error']['code'], -32600);

		$this->assertEquals($data[0]['result'], 45);
	}

	private function call($method, $args, $request_id)
	{
		$input  = RequestBuilder::create_input($method, $args, $request_id);
		$string = $this->service->handleInputProvider($input);
		return json_decode($string, true);
	}

	private function call_raw(array $array)
	{
		$input = RequestBuilder::input_from_array($array);
		$string = $this->service->handleInputProvider($input);
		return json_decode($string, true);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END TestCase < #
// --------------------------------------------------------------------------------------------------------------------- 