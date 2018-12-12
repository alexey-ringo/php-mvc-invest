<?php
//подключили вывод ошибок
require 'application/lib/Dev.php';

use application\core\Router;

//Встроенная в php функция автозагрузки
spl_autoload_register(function($class) {
    /* заменили все '\\' на '/', вставили изменение в $class и добавили .php */ 
    $path = str_replace('\\', '/', $class.'.php');
    //Если файл с расширением .php в пути $path существует, то подключаем его
    if (file_exists($path)) {
        require $path;
    }
});

session_start();

$router = new Router;
$router->run();