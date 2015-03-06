JSON RPC Framework (JRF)
========================

[![Build Status](https://travis-ci.org/rambler-digital-solutions/JRF.svg)](https://travis-ci.org/rambler-digital-solutions/JRF)

**JRF** is a **JSON-RPC over HTTP** data providing framework.
Simple component-based architecture allow you to fast and easy web service developing.
From the box (as-is) **JRF** supports a controller/action factory, input interface (PhpInput by default),
and MiddleWare request checking.

SchemaChecker interface allow to hint-and-check incoming params for any method provided by yours application service.

Smart-fault interface is very easy to use. You don't need to care about anything low-level lifecycle catchers and response
result builders. Just raise and see =)

Outside of the box, you can create a powerful controller-based application service with controller factory.

Usage example
=============

```php

use \jrf\JRF;
$service = new JRF();

$service->controller()->handleMethod('method',
    function($param_1, $param_2, JRF $app, $request_id) {
        return $param_1 + $param_2
    });

$service->listen();
```

Just post JSON on it:

```json
{
    "jsonrpc": "2.0",
    "id": 100,
    "method": "method",
    "params": [1, 3]
}
```

Overriding input interface
==========================

If php://input is a bad way for you, you can create your own input:

```php

use jrf\http\Input;

class MyInput extends Input
{
    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function read()
    {
        return $this->input;
    }
}
```

And override default like this:

```php

// ..... service init
use MyInput;
$input = new MyInput($_POST['json']);
$service->request()->setInputProvider($input);
```

All done =) Your provider will work fine inside JRF ecosystem.


Create access middleware
========================

In "captain obvious" opinion, all available private service must have access provisioning module.
Let's code-in it:

```php

use \jrf\middleware\Base;
use \jrf\fault\ServerError;

class AccessMiddleware extends Base
{
	private $ip_list=[];
	public function __construct(array $ip_list) {
		$this->ip_list = $ip_list;
	}

	public function call()
	{
		$info = $this->app->request()->info();
		$ip   = $info['ip_address'];

		if (!in_array($ip, $this->ip_list)) {
		    ServerError::raise('access error');
		}

		$this->next->call();
	}
}
```

Let's add our middleware into service:

```php

// ... service init
use AccessMiddleware;

$list_allowed_ip = ['127.0.0.1'];

$service->add(new AccessMiddleware($list_allowed_ip));
```

Create custom controller factory
--------------------------------

Simple and easy way to create factory - use namespace-based action provider.

```php

use jrf\controller\Base;
use jrf\fault\MethodNotFound;

class MyControllerFactory extends Base
{
    public function runMethodWithArgs($method, array $args =[], $request_id=0)
    {
        // fall-back if want to use closure-based methods
        if ($this->isHandledMethod($method)) {
            return parent::runMethodWithArgs($method, $args, $request_id);
        }

        // now try to create action class
        if (strpos('.', $method)===false) {
            MethodNotFound::raise();
        }

        list($group, $action) = explode('.', $method);

        $class_name = sprint_f("\my\action\namespace\%s\%s", strtolower($group), ucfirst($action));

        if (!class_exists($class_name)) {
            MethodNotFound::raise();
        }

        $action = new $class_name($this->app());

        if (!is_callable([$action, 'run'])) {
            MethodNotFound::raise();
        }

        return $action->run();
    }
}
```

Implementation:

```php

// .... service init
use MyControllerFactory;
$factory = new MyControllerFactory;
$service->controller($factory);
```

Incoming params hinting/checking
--------------------------------

For example, json was posted is:

```json
{
    "jsonrpc":"2.0",
    "id":120,
    "method":"test",
    "params":{
        "a":11,
        "b":10
    }
}
```

Rules:

- a > 0
- b > 0
- a is INT
- b is INT
- a REQUIRED
- b REQUIRED

Implementation

```php

use jrf\json\Schema;

$params_definition = [
    'a' => [
        'type' => Schema::TYPE_INT,
        'definition' => Schema::DEFINITION_REQUIRED,
        'expression' => function ($v) { return intval($v) > 0; }
    ],
    'b' => [
        'type' => Schema::TYPE_INT,
        'definition' => Schema::DEFINITION_REQUIRED,
        'expression' => function ($v) { return intval($v) > 0; }
    ],
];

$schema = new Schema($params_definition);
$service->controller()->setSchemaForMethod('test', $schema);
```

Configuration container
-----------------------

Configuration Container instantiated Inside JRF constructor method with options passed inside it. To access container
JRF provide method named "config", config access examples:

```php

use jrf\JRF;

$options = [
    'app.debug' => true,
    'app.secret' => 'secret'
];

$service = new JRF($options);

// return: null
$val = $service->config('unknown-option');

// return default defined value: 123
$val = $service->config('unknown-option', 123);

// return true
$val = $service->config('app.debug');
$val = $service->config('app.debug', false);
$val = $service->config()->get('app.debug');
$val = $service->config()->{'app.debug'};
$val = $service->config()['app.debug'];
```

Client usage
---------------------

```php 
$client = new Client("http://json-rpc.server/");

$pipe = $client->createPipe();

$batch = $client->createBatch();
$m1_id = $batch->append($client->method('m1', [1,2,3]));
$m2_id = $batch->append($client->method('m2'));
$m3_id = $batch->append($client->method('m3'));

$pipe->add('batch', $batch);
$pipe->add('news', $client->method('news.search', ['limit'=>50]));
$pipe->add('users', $client->method('users.all', ['limit'=>10]));

$response = $client->executePipe($pipe);

$m1 = $response['batch'][$m2_id];

var_dump($m1->getResult());
var_dump($client->getTotalTime());
```

Unit Tests
----------

```sh
cd path/to/jrf-root
php composer.phar update --dev
vendor/bin/phpunit
```

No-HTTP Usage
-------------

If that really needed, JRF allow to handle custom input interface without any listeners.
By this way JRF is not provide response output.
However, if you need to use couple of providers both, you can run service by add **$service->listen()**.

```php

// input interface from example
use MyInput;

$payload = ["jsonrpc"=>"2.0", "id"=>1, "method"=>"my.method"];
$input   = new MyInput(json_encode($payload));

// ... defining service

$result_string = $service->handleInputProvider($input);
$result_array  = json_decode($result_string, true);

// if HTTP listener needed
$service->listen();
```

License
-------

Copyright (c) 2014 Rambler&Co

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
