<?php

//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

function debug($str) {
	echo '<pre>';
	print_r($str);
	echo '</pre>';
	exit;
}

function dd($str) {
	echo '<pre>';
	var_dump($str);
	echo '</pre>';
	exit;
}