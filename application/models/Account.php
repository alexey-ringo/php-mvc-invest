<?php
namespace application\models;

use application\core\Model;
use application\lib\phpmailer\PHPMailer;
use application\lib\phpmailer\SMTP;
use application\lib\phpmailer\Exception;

class Account extends Model {
    public $error;
    
    private $mailConfig;
    
	public function __construct() {
        //Загрузка массива параметров из конфига PhpMailer
        $this->mailConfig = require 'application/config/mail.php';
        parent::__construct();
       
    }
   
    
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
			'ref' => [
				'pattern' => '#^[a-z0-9]{3,15}$#',
				'message' => 'Логин пригласившего указан неверно',
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
	    
	    if(isset($post['ref'])) {
	    	if($post['login'] == $post['ref']) {
	    		$this->error = 'Регистрация невозможна!';
	    		return false;
	    	}
	    }
	    return true;
	}
	
	public function checkEmailExists($email) {
		$params = [
			'email' => $email,
		];
		return $this->db->column('SELECT id FROM accounts WHERE email = :email', $params);
	}
	
	public function checkLoginExists($login) {
		$params = [
			'login' => $login,
		];
		if ($this->db->column('SELECT id FROM accounts WHERE login = :login', $params)) {
			$this->error = 'Этот логин уже используется';
			return false;
		}
		return true;
	}
	
	public function checkTokenExists($token) {
		$params = [
			'token' => $token,
		];
		return $this->db->column('SELECT id FROM accounts WHERE token = :token', $params);
	}
	
	
	public function activate($token) {
		$params = [
			'token' => $token,
		];
		$this->db->query('UPDATE accounts SET status = 1, token = "" WHERE token = :token', $params);
	}
	
	public function checkRefExists() {
		$params = [
			'login' => $login,
		];
		return $this->db->column('SELECT id FROM accounts WHERE login = :login', $params);
	}
	
	public function createToken() {
		return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 60)), 0, 60);
	}
	
	public function createPassword() {
		return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 10)), 0, 10);
	}
	
	public function register($post) {
		$token = $this->createToken();
		//Проверка корректности реферала
		if($post['ref'] == 'none') {
			$ref = 0;
		}
		else {
			$ref = $this->checkRefExists($post['ref']);
			if(!$ref) {
				$ref = 0;
			}
		}
		
		$params = [
			'id' => NULL,
			'email' => $post['email'],
			'login' => $post['login'],
			'wallet' => $post['wallet'],
			'password' => password_hash($post['password'], PASSWORD_BCRYPT),
			'ref' => $ref,
			'refBalance' => 0,
			'token' => $token,
			'status' => 0,
		];
		
		$this->db->query('INSERT INTO accounts VALUES (:id, :email, :login, :wallet, :password, :ref, :refBalance, :token, :status)', $params);
			
		
		
		//$config = require 'application/config/mail.php';
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = $this->mailConfig['host']; 
		$mail->SMTPAuth = true;
		$mail->Username = $this->mailConfig['username'];
		$mail->Password = $this->mailConfig['password'];
		$mail->SMTPSecure = $this->mailConfig['smtpSecure']; 
		$mail->Port = $this->mailConfig['port'];
		$mail->setFrom($this->mailConfig['username']);
		$mail->addAddress($post['email']);
		
		$mail->isHTML(true); 
		$mail->Subject = 'Confirmation activate account'; // Заголовок письма
		$mail->Body = 'Confirm: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/account/confirm/'.$token;
		
		$mail->send();
		/*
		if(!$mail->send()) {
			echo ‘Message could not be sent.’;
			echo ‘Mailer Error: ‘ . $mail->ErrorInfo;
		} else {
		echo ‘ok’;
		}
		*/
		
	}
	
	public function checkData($login, $password) {
		$params = [
			'login' => $login,
			];
		$hash = $this->db->column('SELECT password FROM accounts WHERE login = :login', $params);
		
		if(!$hash or !password_verify($password, $hash)) {
			$this->error = 'Логин или пароль указан неверно';
			return false;
		}
		return true;
	}
	
	public function checkStatus($type, $data) {
		$params = [
			$type => $data,
		];
		$status = $this->db->column('SELECT status FROM accounts WHERE ' . $type . ' = :' . $type, $params);
		if($status != 1) {
			$this->error = 'Аккаунт ожидает подтверждения по E-mail';
			return false;
		}
		return true;
	}
	
	public function login($login) {
		$params = [
			'login' => $login,
		];
		$data = $this->db->row('SELECT * FROM accounts WHERE login = :login', $params);
		/*
		Данные о пользователе, возвращаемые из БД, хранятся в нолевом ключе массива $data:
		[0] => Array
        (
            [id] => 1
            [email] => alexey.ringo@gmail.com
            [login] => ringo
            [wallet] => U7034466
            [password] => $2y$10$u6DCRCobsNJeBEZEf6n3FeZHX2I73Xze4wQ17HsiOvK7AbC1BIIg2
            [ref] => 0
            [refBalance] => 0
            [token] => 
            [status] => 1
        )
		*/
		$_SESSION['account'] = $data[0];
	}
	
	public function recovery($post) {
		$token = $this->createToken();
		
		$params = [
			'email' => $post['email'],
			'token' => $token,
		];
		
		$this->db->query('UPDATE accounts SET token = :token WHERE email = :email', $params);
		
		$config = require 'application/config/mail.php';
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = $this->mailConfig['host']; 
		$mail->SMTPAuth = true;
		$mail->Username = $this->mailConfig['username'];
		$mail->Password = $this->mailConfig['password'];
		$mail->SMTPSecure = $this->mailConfig['smtpSecure']; 
		$mail->Port = $this->mailConfig['port'];
		$mail->setFrom($this->mailConfig['username']);
		$mail->addAddress($post['email']);
		
		$mail->isHTML(true); 
		$mail->Subject = 'Confirmation activate account'; // Заголовок письма
		$mail->Body = 'Recovery: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/account/reset/'.$token;
		
		$mail->send();
		/*
		if(!$mail->send()) {
			echo ‘Message could not be sent.’;
			echo ‘Mailer Error: ‘ . $mail->ErrorInfo;
		} else {
		echo ‘ok’;
		}
		*/
	}
	
	public function reset($token) {
		$new_password = $this->createPassword();
		$params = [
			'token' => $token,
			'password' => password_hash($new_password, PASSWORD_BCRYPT),
		];
		$this->db->query('UPDATE accounts SET status = 1, token = "", password = :password WHERE token = :token', $params);
		return $new_password;
	}
	
	public function save($post) {
		$params = [
			'id' => $_SESSION['account']['id'],
			'email' => $post['email'],
			'wallet' => $post['wallet'],
		];
		if (!empty($post['password'])) {
			$params['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
			$sql = ',password = :password';
		}
		//Если поле с паролем пришло пустое - то в запрос параметр пароля не добавляется
		else {
			$sql = '';
		}
		//Сначала обновляем измененные данные (собранные в $params) в сессии
		foreach ($params as $key => $val) {
			$_SESSION['account'][$key] = $val;
		}
		//А затем записываем изменения в БД
		$this->db->query('UPDATE accounts SET email = :email, wallet = :wallet'.$sql.' WHERE id = :id', $params);
	}
	
}