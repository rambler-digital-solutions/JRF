<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 24.09.14
 * Time: 12:02
 * File: Middleware.php
 * Package: jrf\middleware
 *
 */

namespace jrf\middleware;


use jrf\JRF;

interface Middleware {
	public function call();
	public function setApplication(JRF $app);
	public function setNextMiddleware($middleware);
} 