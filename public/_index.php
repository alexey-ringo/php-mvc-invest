<?php

//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);


//define('ROOT', dirname(__FILE__, 2));
//dirname(dirname(__FILE__))
//dirname(__FILE__, 2)



//require_once(ROOT . '/application/core/Autoload.php');
require __DIR__ . '/../application/core/Autoload.php';



session_start();

$router = new Router;
$router->run();