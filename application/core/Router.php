<?php

namespace application\core;

use application\core\View;

class Router {

    protected $routes = [];
    protected $params = [];
    
    public function __construct() {
        //Загрузка массива маршрутов из конфига
        $arr = require 'application/config/routes.php';
        foreach ($arr as $key => $val) {
            $this->add($key, $val);
        }
    }
    
    //Добавление маршрутов, загруженных в конструкторе
    public function add($route, $params) {
        //Обработка доп параметров рута
        $route = preg_replace('/{([a-z]+):([^\}]+)}/', '(?P<\1>\2)', $route);
        $route = '#^'.$route.'$#';
        $this->routes[$route] = $params;
    }
    
    //Проверка соответствия маршрута - URI
    public function match() {
        //Опаределяем текущий URI
        //REQUEST_URI Возвращает со '/' спереди, поэтому его нужно срезать с trim
        $url = trim($_SERVER['REQUEST_URI'], '/'); 
        foreach ($this->routes as $route => $params) {
            //Если маршрут из route совпал со строкой URI,
            if (preg_match($route, $url, $matches)) {
                
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        if (is_numeric($match)) {
                            $match = (int) $match;
                        }
                        $params[$key] = $match;
                    }
                }
                
                
                //То записываем в переменную текущего объекта массив вида, напр: 'controller' => 'account', 'action' => 'register'
                $this->params = $params;
                return true;
            }
        }
        return false;
    }
    
    //Запуск маршрута
    public function run(){
        if ($this->match()) {
            //debug($this->params);
            //echo 'Маршрут найден'; exit;
            $path = 'application\controllers\\'.ucfirst($this->params['controller']).'Controller';
            if (class_exists($path)) {
                $action = $this->params['action'].'Action';
                //Если существует класс по пути $path и в нем существует метод $action,
                if (method_exists($path, $action)) {
                    //То создаем экземпляр этого класса контроллера,
                    $controller = new $path($this->params);
                    //и вызываем метод action
                    $controller->$action();
                } else {
                    View::errorCode(404);
                }
            } else {
                View::errorCode(404);
            }
        } else {
            View::errorCode(404);
        }
    }

}