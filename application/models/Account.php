<?php
namespace application\models;

use application\core\Model;
use application\lib\phpmailer\PHPMailer;
use application\lib\phpmailer\SMTP;
use application\lib\phpmailer\Exception;

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
	
	
	public function createToken() {
		return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 60)), 0, 60);
	}
	
	public function register($post) {
		$token = $this->createToken();
		
		$params = [
			'id' => NULL,
			'email' => $post['email'],
			'login' => $post['login'],
			'wallet' => $post['wallet'],
			'password' => password_hash($post['password'], PASSWORD_BCRYPT),
			'ref' => 0,
			'refBalance' => 0,
			'token' => $token,
			'status' => 0,
		];
		
		$this->db->query('INSERT INTO accounts VALUES (:id, :email, :login, :wallet, :password, :ref, :refBalance, :token, :status)', $params);
			
		
		
		$config = require 'application/config/mail.php';
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = $config['host']; 
		$mail->SMTPAuth = true; 
		$mail->Username = $config['username'];
		$mail->Password = $config['password'];
		$mail->SMTPSecure = $config['smtpSecure']; 
		$mail->Port = $config['port'];
		$mail->setFrom($config['username']);
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
	
	
	
}