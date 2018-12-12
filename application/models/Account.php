<?php
namespace application\models;

use application\core\Model;

class Account extends Model {
    public $error;
    
    
	public function validate($input, $post) {
	    $rules = [
	        'email' => [
				'pattern' => '#^([a-z0-9_.-]{1,20}+)@([a-z0-9_.-]+)\.([a-z\.]{2,10})$#',
				'message' => 'E-mail адрес указан неверно',
			],
			'login' => [
				'pattern' => '#^[a-z0-9]{3,15}$#',
				'message' => 'Логин указан неверно (разрешены только латинские буквы и цифры от 3 до 15 символов',
			],
			'wallet' => [
				'pattern' => '#^[A-z0-9]{3,15}$#',
				'message' => 'Кошелек Perfect Money указан неверно',
			],
			'password' => [
				'pattern' => '#^[a-z0-9]{10,30}$#',
				'message' => 'Пароль указан неверно (разрешены только латинские буквы и цифры от 10 до 30 символов',
			],
	    ];
	    
	    foreach($input as $val) {
	        if(!isset($post[$val]) or !preg_match($rules[$val]['pattern'], $post[$val])) {
	            $this->error = $rules[$val]['message'];
				return false;
	        }
	    }
	    return true;
	}
	
}