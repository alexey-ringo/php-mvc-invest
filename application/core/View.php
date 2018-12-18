<?php

namespace application\core;

class View {

	public $path;
	public $route;
	public $layout = 'default';

	public function __construct($route) {
		//записываем в переменную текущего объекта массив вида, напр: 'controller' => 'account', 'action' => 'register'
		$this->route = $route;
		//записываем в переменную текущего объекта путь к файлу, напр: account/action
		$this->path = $route['controller'].'/'.$route['action'];
	}

	public function render($title, $vars = []) {
		extract($vars);
		$path = 'application/views/'.$this->path.'.php';
		if (file_exists($path)) {
			ob_start();
			require $path;
			$content = ob_get_clean();
			require 'application/views/layouts/'.$this->layout.'.php';
		}
	}

	public function redirect($url) {
		header('location: /'.$url);
		exit;
	}

	public static function errorCode($code) {
		http_response_code($code);
		$path = 'application/views/errors/'.$code.'.php';
		if (file_exists($path)) {
			require $path;
		}
		exit;
	}
	
	
	public function message($status, $message) {
		exit(json_encode(['status' => $status, 'message' => $message]));
	}
	
	//Редирект для ajax
	public function location($url) {
		exit(json_encode(['url' => $url]));
	}

}	