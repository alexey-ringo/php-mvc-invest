<?php

namespace application\controllers;

use application\core\Controller;

class AccountController extends Controller {

	public function loginAction() {
		$this->view->render('Вход');
	}

	public function registerAction() {
		if(!empty($_POST)) {
			if(!$this->model->validate(['email', 'login', 'wallet', 'password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			$this->view->message('success', 'validation OK');
		}
		
		$this->view->render('Регистрация');
	}
	
	public function recoveryAction() {
		$this->view->render('Восстановление пароля');
	}

}