<?php

namespace application\components\menu_categories;

use application\models\Category;

class MenuCategories {
    
    //Переменная определяет вариант шаблона данного виджета при инициализации компонента
    public $tpl;
    
    //Здесь хранятся записи о всех категориях из БД (массив категорий)
    public $data;
    
    //Здесь хранится дерево - результат работы функции, которая построит из обычного массива массив дерева (визуально видно вложенность категорий)
    public $tree;
    
    //Хранится сгенеренный html-код в зависимости от типа шаблона, определенного в $tpl
    public $menuHtml;
    
    public function __construct($typeMenu) {
        $this->tpl = $typeMenu;
        //Автоподставление в $tpl по умолчанию, если параметр не передан в виджет
        if($this->tpl === null) {
            $this->tpl = 'menu';
        }
        //Добавили расширение файла php
        $this->tpl .= '.php';
    }
    
        public function run() {
        /*    
        //Проверяем - есть ли в соответствующем разделе кэша (menu) - инф. о запросе списка категорий
        $menu = Yii::$app->cache->get('menu');
        //Если из этой секции кэша (menu) - что либо получено - то run() сразу возвращает это значение
        if($menu) {
            return $menu;
        }
        //Если в кэше пусто - то начинаем стандартным образом запрашибать у БД о категориях
        //И формировать меню
        */
        
        //indexBy() перенумеровывает ключи массива для соответвия (совпадения) с идентификаторами 'id'
        //передаем в indexBy() параметр, указывающий - по какому полю индексировать массив 
        $categories = new Category();
        $this->data = $categories->getMenuCategories();
        $this->tree = $this->getTree();
        $this->menuHtml = $this->getMenuHtml($this->tree);
        
        /*
        //По окончании формирования меню записываем его в кэш
        //(название конкретного кэша, /что записываем, /время действия кэша в сек)
        Yii::$app->cache->set('menu', $this->menuHtml, 60);
        */
        
        return $this->menuHtml;
    }
    
    
    //проходит массив в цикле и из обычного одномерного массива строит дерево
    protected function getTree() {
        $tree = [];
        foreach($this->data as $id => &$node) {
            if(!$node['parent_id']) {
                $tree[$id] = &$node;
            }
            else {
                $this->data[$node['parent_id']]['childs'][$node['id']] = &$node;
            
            }
        }
        return $tree;
    }
    

    //здесь свойство $tree передаем в функ. как параметр (а не используем в ней как свойство), 
    //потому что hetMenuHtml($tree) не проходит весь массив целиком
    //Функция работает с одним узлом(категорией)
    protected function getMenuHtml($tree) {
        //Сюда помещается готовый hthl-код
        $str = '';
        foreach($tree as $category) {
            //Передаем в catToTemplate каждый конкретный элемент дерева
            $str .= $this->catToTemplate($category);
        }
        return $str;
    }
    
    //принимает параметром переданный элемент "категория" и возвращает буферизированный вывод
    protected function catToTemplate($category) {
        ob_start();
        //и помещает его в шаблон
        include __DIR__ . '/menu_tpl/' . $this->tpl;
        //буферизация - для запрета вывода в браузер. Буферизируем вывод (ob_start), а затем возврящаем, не выводя на экран
        return ob_get_clean();
    }
    
    
}