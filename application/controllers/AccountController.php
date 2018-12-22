<?php

namespace application\controllers;

use application\core\Controller;

class AccountController extends Controller {

	public function registerAction() {
		if(!empty($_POST)) {
			
			//Основная валидация полей формы регистрации
			if(!$this->model->validate(['email', 'login', 'wallet', 'password', 'ref'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			
			//проверка email на уникальность в БД при рагистрации нового аккаунта
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
			
			//проверка наличия у аккаунта выполненной активации - поиск по login
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
		if (!empty($_POST)) {
			//Валидируем новые (измененные) email и кошелек
			if (!$this->model->validate(['email', 'wallet'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			//проверка наличия нового (измененного) email в БД, и если есть - получение id
			$id = $this->model->checkEmailExists($_POST['email']);
			//если новый (измененный) email уже есть в БД (нашли его id), и он не соответствует текущему - то ошибка (значит это чужой, уже зарег ранее email)
			if ($id and $id != $_SESSION['account']['id']) {
				$this->view->message('error', 'Этот E-mail уже используется');
			}
			//пароль должкн не быть пустым и удовлетворять правилам валидации
			if (!empty($_POST['password']) and !$this->model->validate(['password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			$this->model->save($_POST);
			$this->view->message('error', 'Сохранено');
		}
		$this->view->render('Профиль');
	}
	
	public function logoutAction() {
		unset($_SESSION['account']);
		$this->view->redirect('account/login');
	}

	public function recoveryAction() {
		if(!empty($_POST)) {
			
			//Основная валидация полей формы регистрации
			if(!$this->model->validate(['email'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			
			//проверка наличия email в БД у аккаунта, собирающегося сменить пароль
			elseif (!$this->model->checkEmailExists($_POST['email'])) {
				$this->view->message('error', 'Этот E-mail не зарегистрирован в базе');
			}
			
			//проверка наличия у аккаунта выполненной активации - поиск по email
			elseif (!$this->model->checkStatus('email', $_POST['email'])) {
				$this->view->message('error', $this->model->error);
			}
			
			$this->model->recovery($_POST);
			$this->view->message('success', 'Запрос на восстановление пароля отправлен на E-mail');
			
		}
		
		$this->view->render('Восстановление пароля');
	}
	
	public function resetAction() {
		if (!$this->model->checkTokenExists($this->route['token'])) {
			$this->view->redirect('account/login');
		}
		$password = $this->model->reset($this->route['token']);
		$vars = [
			'password' => $password,
			];
		
		$this->view->render('Пароль сброшен', $vars);
	}

}