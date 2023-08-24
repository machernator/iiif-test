<?php
namespace Controllers;
use \Tracy\Debugger as Debugger;

class ErrorController {
	private $f3;
	// template folders
    protected $views;

	public function __construct(\Base $f3, array $params) {
		// Pfade
		$this->views = $f3->get('tpl_views') . '/error/';
		$this->f3 = $f3;
	}

	public function error($f3) {
		//print_r($this->f3->get('ERROR')); exit;
		$error = $this->f3->get('ERROR') ?? null;
		$exception = $this->f3->get('EXCEPTION') ?? null;

		switch($error['code']) {
			case 404:
				echo \Template::instance()->render($this->views . 'error-404.html');
				Debugger::barDump($exception);
				break;
			case 500:
				echo \Template::instance()->render($this->views . 'error-500.html');
				Debugger::barDump($exception);
		}
		// There isn't an exception when calling `Base->error()`.
		// if (!$exception instanceof \Throwable) {
		// 	return;
		// 	//$exception = new \Exception('HTTP ' . $this->f3->get('ERROR.code'));
		// }

		// Debugger::exceptionHandler($exception);
	}

	public function error404(\Base $f3, array $params) {
		echo 'Error 404';
	}
}
