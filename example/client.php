<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 14:32
 * File: client.php
 *
 */

include realpath(__DIR__.'/../vendor/autoload.php');

//$client = new \jrf\Client('http://jrf.lo/service.php');
//
//$result = $client->execute('test', ['a'=>'10', 'b'=>20]);
//
//var_dump($result);


trait Test
{
	public function test($a, $b)
	{
		return $this->execute('test', ['a'=>$a, 'b'=>$b]);
	}
}

class MyClient extends \jrf\Client
{
	use Test;
}

$client = new MyClient('http://jrf.lo/service.php');
$result = $client->test(10, 25);

var_dump($result);