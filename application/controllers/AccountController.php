<?php

namespace application\controllers;

use application\core\Controller;

class AccountController extends Controller {

	public function loginAction() {
		if(!empty($_POST)) {
			
			//Основная валидация полей формы входа
			if(!$this->model->validate(['login', 'password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			
			//проверка соответствия правильности пароля у вводимого логина
			elseif (!$this->model->checkData($_POST['login'], $_POST['password'])) {
				$this->view->message('error', $this->model->error);
			}
			
			//проверка наличия у аккацнта выполненной активации
			elseif (!$this->model->checkStatus('login', $_POST['login'])) {
				$this->view->message('error', $this->model->error);
			}
			//Зппись проверенных данных из формы входа (из $_POST) в сессию
			$this->model->login($_POST['login']);
			$this->view->location('account/profile');
			
		}
		
		$this->view->render('Регистрация');
	}
	
	public function profileAction() {
		$this->view->render('Профиль');
	}
	
	public function logoutAction() {
		unset($_SESSION['account']);
		$this->view->redirect('account/login');
	}

	public function registerAction() {
		if(!empty($_POST)) {
			
			//Основная валидация полей формы регистрации
			if(!$this->model->validate(['email', 'login', 'wallet', 'password', 'ref'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			
			//проверка email на уникальность в БД
			elseif ($this->model->checkEmailExists($_POST['email'])) {
				$this->view->message('error', 'Этот E-mail уже используется');
			}
			//проверка login на уникальность в БД
			elseif (!$this->model->checkLoginExists($_POST['login'])) {
				$this->view->message('error', $this->model->error);
			}
			
			$this->model->register($_POST);
			$this->view->message('success', 'Регистрация завершена, подтвердите свой E-mail');
			
		}
		
		$this->view->render('Регистрация');
	}
	
	
	public function confirmAction() {
		if (!$this->model->checkTokenExists($this->route['token'])) {
			$this->view->redirect('account/login');
		}
		
		$this->model->activate($this->route['token']);
		$this->view->render('Аккаунт активирован');
	}
	
	
	public function recoveryAction() {
		$this->view->render('Восстановление пароля');
	}

}